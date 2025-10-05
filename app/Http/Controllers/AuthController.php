<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\AccessKey;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Register a new user
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'fullName' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'confirmPassword' => 'required|string|min:8|same:password',
            'accessKey' => 'required|string|exists:access_keys,key',
            'country' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'referrerCode' => 'nullable|string|exists:users,referral_code',
        ]);

        // Verify access key
        $accessKey = AccessKey::where('key', $validated['accessKey'])->first();
        
        if (!$accessKey || !$accessKey->canBeUsed()) {
            throw ValidationException::withMessages([
                'accessKey' => ['The access key is invalid or has already been used.'],
            ]);
        }

        // Find referrer if provided
        $referrer = null;
        if (!empty($validated['referrerCode'])) {
            $referrer = User::where('referral_code', $validated['referrerCode'])->first();
        }

        // Create user with package from access key
        /** @var User $user */
        $user = User::create([
            'name' => $validated['fullName'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'country' => $validated['country'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'referral_code' => strtoupper(Str::random(6)), // Generate unique referral code
            'current_package_id' => $accessKey->package_id,
            'package_expires_at' => $accessKey->package->duration_days ? 
                now()->addDays($accessKey->package->duration_days) : null,
            'referred_by' => $referrer ? $referrer->id : null,
        ]);

        // Mark access key as used
        $accessKey->update([
            'is_used' => true,
            'used_by' => $user->id,
            'used_at' => now(),
        ]);

        // Award referral bonuses if applicable
        if ($referrer) {
            // Base referral amounts
            $baseLevel1Bonus = 100; // Amount in your currency
            $baseLevel2Bonus = 50;  // Amount in your currency
            $baseLevel3Bonus = 25;  // Amount in your currency
            
            // Calculate actual bonus amounts based on the package that was applied to the new user
            $level1Bonus = $baseLevel1Bonus;
            if ($accessKey->package && $accessKey->package->referral_earning_percentage > 0) {
                $level1Bonus = (float) $accessKey->package->referral_earning_percentage;
            }
            
            // Award direct referral bonus (Level 1) to referral earnings
            $referrer->addToReferralEarnings($level1Bonus);
            
            // Log the referral bonus
            \App\Models\ReferralBonus::create([
                'referrer_id' => $referrer->id,
                'referred_user_id' => $user->id,
                'level' => 1,
                'amount' => $level1Bonus,
                'description' => 'Direct referral bonus for ' . $user->username,
            ]);
            
            // Log the transaction for the referrer
            $referrer->transactions()->create([
                'amount' => $level1Bonus,
                'type' => 'referral_earning',
                'description' => 'Direct referral bonus for ' . $user->username,
                'status' => 'completed',
            ]);
            
            // Award indirect referral bonus (Level 2)
            if ($referrer->referredBy) {
                $level2Bonus = $baseLevel2Bonus;
                if ($accessKey->package && $accessKey->package->referral_earning_percentage > 0) {
                    $level2Bonus = (float) $accessKey->package->referral_earning_percentage;
                }
                
                $referrer->referredBy->addToReferralEarnings($level2Bonus);
                
                // Log the referral bonus
                \App\Models\ReferralBonus::create([
                    'referrer_id' => $referrer->referredBy->id,
                    'referred_user_id' => $user->id,
                    'level' => 2,
                    'amount' => $level2Bonus,
                    'description' => 'Indirect referral bonus for ' . $user->username,
                ]);
                
                // Log the transaction for the indirect referrer
                $referrer->referredBy->transactions()->create([
                    'amount' => $level2Bonus,
                    'type' => 'referral_earning',
                    'description' => 'Indirect referral bonus for ' . $user->username,
                    'status' => 'completed',
                ]);
                
                // Award indirect referral bonus (Level 3)
                if ($referrer->referredBy->referredBy) {
                    $level3Bonus = $baseLevel3Bonus;
                    if ($accessKey->package && $accessKey->package->referral_earning_percentage > 0) {
                        $level3Bonus = (float) $accessKey->package->referral_earning_percentage;
                    }
                    
                    $referrer->referredBy->referredBy->addToReferralEarnings($level3Bonus);
                    
                    // Log the referral bonus
                    \App\Models\ReferralBonus::create([
                        'referrer_id' => $referrer->referredBy->referredBy->id,
                        'referred_user_id' => $user->id,
                        'level' => 3,
                        'amount' => $level3Bonus,
                        'description' => 'Second indirect referral bonus for ' . $user->username,
                    ]);
                    
                    // Log the transaction for the second indirect referrer
                    $referrer->referredBy->referredBy->transactions()->create([
                        'amount' => $level3Bonus,
                        'type' => 'referral_earning',
                        'description' => 'Second indirect referral bonus for ' . $user->username,
                        'status' => 'completed',
                    ]);
                }
            }
        }

        // Create API token
        $token = $user->createToken('auth-token')->plainTextToken;

        // Load package information and other relationships
        $user->load(['currentPackage', 'referrals', 'referredBy', 'projects', 'images']);

        // Format package benefits
        $packageBenefits = $user->currentPackage ? $user->currentPackage->features : [];
        if (is_string($packageBenefits)) {
            $packageBenefits = json_decode($packageBenefits, true) ?: [];
        }

        // Get referral statistics
        $referralStats = $user->getReferralStats();
        $referralEarnings = $user->getReferralEarningsByLevel();

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => (string)$user->id,
                    'username' => $user->username,
                    'email' => $user->email,
                    'fullName' => $user->name,
                    'phone' => $user->phone,
                    'country' => $user->country,
                    'package' => $user->currentPackage ? [
                        'id' => (string)$user->currentPackage->id,
                        'name' => $user->currentPackage->name,
                        'price' => (int)$user->currentPackage->price,
                        'benefits' => $packageBenefits,
                        'duration' => (int)$user->currentPackage->duration_days,
                    ] : null,
                    'referralCode' => $user->referral_code,
                    'referralStats' => [
                        'level1Count' => $referralStats['level1_count'],
                        'level2Count' => $referralStats['level2_count'],
                        'level3Count' => $referralStats['level3_count'],
                        'totalCount' => $referralStats['total_count'],
                    ],
                    'referralEarnings' => [
                        'level1Earnings' => (float)$referralEarnings['level1'],
                        'level2Earnings' => (float)$referralEarnings['level2'],
                        'level3Earnings' => (float)$referralEarnings['level3'],
                        'totalEarnings' => (float)$referralEarnings['total'],
                    ],
                    'earnings' => [
                        'normalEarnings' => (float)$user->getNormalEarnings(),
                        'referralEarnings' => (float)$user->getReferralEarnings(),
                        'totalEarnings' => (float)$user->getTotalEarnings(),
                    ],
                    'referrals' => $user->referrals->map(function ($referral) {
                        return [
                            'id' => (string)$referral->id,
                            'username' => $referral->username,
                            'email' => $referral->email,
                            'fullName' => $referral->name,
                            'createdAt' => $referral->created_at->toISOString(),
                        ];
                    }),
                    'referredBy' => $user->referredBy ? [
                        'id' => (string)$user->referredBy->id,
                        'username' => $user->referredBy->username,
                        'email' => $user->referredBy->email,
                        'fullName' => $user->referredBy->name,
                    ] : null,
                    'projectsCount' => $user->projects->count(),
                    'imagesCount' => $user->images->count(),
                    'createdAt' => $user->created_at->toISOString(),
                    'updatedAt' => $user->updated_at->toISOString(),
                ],
                'token' => $token
            ],
            'message' => 'Registration successful! Welcome to VisionHub!',
        ], 201);
    }

    /**
     * Login user with username and password
     */
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => 'required',
            'password' => 'required',
        ]);

        if (!Auth::attempt(['email' => $credentials['email'], 'password' => $credentials['password']])) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        /** @var User $user */
        $user = Auth::user();
        
        // Revoke existing tokens (optional - for single session)
        // $user->tokens()->delete();
        
        // Create new API token
        $token = $user->createToken('auth-token')->plainTextToken;

        // Load package information and other relationships
        $user->load(['currentPackage', 'referrals', 'referredBy', 'projects', 'images']);

        // Format package benefits
        $packageBenefits = $user->currentPackage ? $user->currentPackage->features : [];
        if (is_string($packageBenefits)) {
            $packageBenefits = json_decode($packageBenefits, true) ?: [];
        }

        // Get referral statistics
        $referralStats = $user->getReferralStats();
        $referralEarnings = $user->getReferralEarningsByLevel();

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => (string)$user->id,
                    'username' => $user->username,
                    'email' => $user->email,
                    'fullName' => $user->name,
                    'phone' => $user->phone,
                    'country' => $user->country,
                    'package' => $user->currentPackage ? [
                        'id' => (string)$user->currentPackage->id,
                        'name' => $user->currentPackage->name,
                        'price' => $user->currentPackage->price,
                        'benefits' => $packageBenefits,
                        'duration' => $user->currentPackage->duration_days,
                    ] : null,
                    'referralCode' => $user->referral_code,
                    'referralStats' => [
                        'level1Count' => $referralStats['level1_count'],
                        'level2Count' => $referralStats['level2_count'],
                        'level3Count' => $referralStats['level3_count'],
                        'totalCount' => $referralStats['total_count'],
                    ],
                    'referralEarnings' => [
                        'level1Earnings' => (float)$referralEarnings['level1'],
                        'level2Earnings' => (float)$referralEarnings['level2'],
                        'level3Earnings' => (float)$referralEarnings['level3'],
                        'totalEarnings' => (float)$referralEarnings['total'],
                    ],
                    'earnings' => [
                        'normalEarnings' => (float)$user->getNormalEarnings(),
                        'referralEarnings' => (float)$user->getReferralEarnings(),
                        'totalEarnings' => (float)$user->getTotalEarnings(),
                    ],
                    'referrals' => $user->referrals->map(function ($referral) {
                        return [
                            'id' => (string)$referral->id,
                            'username' => $referral->username,
                            'email' => $referral->email,
                            'fullName' => $referral->name,
                            'createdAt' => $referral->created_at->toISOString(),
                        ];
                    }),
                    'referredBy' => $user->referredBy ? [
                        'id' => (string)$user->referredBy->id,
                        'username' => $user->referredBy->username,
                        'email' => $user->referredBy->email,
                        'fullName' => $user->referredBy->name,
                    ] : null,
                    'projectsCount' => $user->projects->count(),
                    'imagesCount' => $user->images->count(),
                    'createdAt' => $user->created_at->toISOString(),
                    'updatedAt' => $user->updated_at->toISOString(),
                ],
                'token' => $token,
            ],
            'message' => 'Login successful',
        ]);
    }

    /**
     * Login user with email and password
     */
    public function loginWithEmail(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt(['email' => $credentials['email'], 'password' => $credentials['password']])) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        /** @var User $user */
        $user = Auth::user();
        
        // Create new API token
        $token = $user->createToken('auth-token')->plainTextToken;

        // Load package information and other relationships
        $user->load(['currentPackage', 'referrals', 'referredBy', 'projects', 'images']);

        // Format package benefits
        $packageBenefits = $user->currentPackage ? $user->currentPackage->features : [];
        if (is_string($packageBenefits)) {
            $packageBenefits = json_decode($packageBenefits, true) ?: [];
        }

        // Get referral statistics
        $referralStats = $user->getReferralStats();
        $referralEarnings = $user->getReferralEarningsByLevel();

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => (string)$user->id,
                    'username' => $user->username,
                    'email' => $user->email,
                    'fullName' => $user->name,
                    'phone' => $user->phone,
                    'country' => $user->country,
                    'package' => $user->currentPackage ? [
                        'id' => (string)$user->currentPackage->id,
                        'name' => $user->currentPackage->name,
                        'price' => $user->currentPackage->price,
                        'benefits' => $packageBenefits,
                        'duration' => $user->currentPackage->duration_days,
                    ] : null,
                    'referralCode' => $user->referral_code,
                    'referralStats' => [
                        'level1Count' => $referralStats['level1_count'],
                        'level2Count' => $referralStats['level2_count'],
                        'level3Count' => $referralStats['level3_count'],
                        'totalCount' => $referralStats['total_count'],
                    ],
                    'referralEarnings' => [
                        'level1Earnings' => (float)$referralEarnings['level1'],
                        'level2Earnings' => (float)$referralEarnings['level2'],
                        'level3Earnings' => (float)$referralEarnings['level3'],
                        'totalEarnings' => (float)$referralEarnings['total'],
                    ],
                    'earnings' => [
                        'normalEarnings' => (float)$user->getNormalEarnings(),
                        'referralEarnings' => (float)$user->getReferralEarnings(),
                        'totalEarnings' => (float)$user->getTotalEarnings(),
                    ],
                    'referrals' => $user->referrals->map(function ($referral) {
                        return [
                            'id' => (string)$referral->id,
                            'username' => $referral->username,
                            'email' => $referral->email,
                            'fullName' => $referral->name,
                            'createdAt' => $referral->created_at->toISOString(),
                        ];
                    }),
                    'referredBy' => $user->referredBy ? [
                        'id' => (string)$user->referredBy->id,
                        'username' => $user->referredBy->username,
                        'email' => $user->referredBy->email,
                        'fullName' => $user->referredBy->name,
                    ] : null,
                    'projectsCount' => $user->projects->count(),
                    'imagesCount' => $user->images->count(),
                    'createdAt' => $user->created_at->toISOString(),
                    'updatedAt' => $user->updated_at->toISOString(),
                ],
                'token' => $token,
            ],
            'message' => 'Login successful',
        ]);
    }

    /**
     * Logout user (revoke current token)
     */
    public function logout(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        
        // Get the current access token and delete it
        $currentToken = $user->currentAccessToken();
        if ($currentToken) {
            /** @var \Laravel\Sanctum\PersonalAccessToken $currentToken */
            $currentToken->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Successfully logged out',
        ]);
    }

    /**
     * Logout from all devices (revoke all tokens)
     */
    public function logoutAll(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $user->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Successfully logged out',
        ]);
    }

    /**
     * Get current authenticated user
     */
    public function me(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        
        // Load relationships
        $user->load(['currentPackage', 'referrals', 'referredBy', 'projects', 'images']);

        // Format package benefits
        $packageBenefits = $user->currentPackage ? $user->currentPackage->features : [];
        if (is_string($packageBenefits)) {
            $packageBenefits = json_decode($packageBenefits, true) ?: [];
        }

        // Get referral statistics
        $referralStats = $user->getReferralStats();
        $referralEarnings = $user->getReferralEarningsByLevel();

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => (string)$user->id,
                    'username' => $user->username,
                    'email' => $user->email,
                    'fullName' => $user->name,
                    'phone' => $user->phone,
                    'country' => $user->country,
                    'package' => $user->currentPackage ? [
                        'id' => (string)$user->currentPackage->id,
                        'name' => $user->currentPackage->name,
                        'price' => (int)$user->currentPackage->price,
                        'benefits' => $packageBenefits,
                        'duration' => (int)$user->currentPackage->duration_days,
                    ] : null,
                    'referralCode' => $user->referral_code,
                    'referralStats' => [
                        'level1Count' => $referralStats['level1_count'],
                        'level2Count' => $referralStats['level2_count'],
                        'level3Count' => $referralStats['level3_count'],
                        'totalCount' => $referralStats['total_count'],
                    ],
                    'referralEarnings' => [
                        'level1Earnings' => (float)$referralEarnings['level1'],
                        'level2Earnings' => (float)$referralEarnings['level2'],
                        'level3Earnings' => (float)$referralEarnings['level3'],
                        'totalEarnings' => (float)$referralEarnings['total'],
                    ],
                    'earnings' => [
                        'normalEarnings' => (float)$user->getNormalEarnings(),
                        'referralEarnings' => (float)$user->getReferralEarnings(),
                        'totalEarnings' => (float)$user->getTotalEarnings(),
                    ],
                    'referrals' => $user->referrals->map(function ($referral) {
                        return [
                            'id' => (string)$referral->id,
                            'username' => $referral->username,
                            'email' => $referral->email,
                            'fullName' => $referral->name,
                            'createdAt' => $referral->created_at->toISOString(),
                        ];
                    }),
                    'referredBy' => $user->referredBy ? [
                        'id' => (string)$user->referredBy->id,
                        'username' => $user->referredBy->username,
                        'email' => $user->referredBy->email,
                        'fullName' => $user->referredBy->name,
                    ] : null,
                    'projectsCount' => $user->projects->count(),
                    'imagesCount' => $user->images->count(),
                    'createdAt' => $user->created_at->toISOString(),
                    'updatedAt' => $user->updated_at->toISOString(),
                ]
            ],
            'message' => 'User data retrieved successfully',
        ]);
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        
        $validated = $request->validate([
            'fullName' => 'sometimes|required|string|max:255',
            'phone' => 'sometimes|required|string|max:255',
            'country' => 'sometimes|required|string|max:255',
        ]);

        $user->update([
            'name' => $validated['fullName'] ?? $user->name,
            'phone' => $validated['phone'] ?? $user->phone,
            'country' => $validated['country'] ?? $user->country,
        ]);

        // Load package information and other relationships
        $user->load(['currentPackage', 'referrals', 'referredBy', 'projects', 'images']);

        // Format package benefits
        $packageBenefits = $user->currentPackage ? $user->currentPackage->features : [];
        if (is_string($packageBenefits)) {
            $packageBenefits = json_decode($packageBenefits, true) ?: [];
        }

        // Get referral statistics
        $referralStats = $user->getReferralStats();
        $referralEarnings = $user->getReferralEarningsByLevel();

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => (string)$user->id,
                    'username' => $user->username,
                    'email' => $user->email,
                    'fullName' => $user->name,
                    'phone' => $user->phone,
                    'country' => $user->country,
                    'package' => $user->currentPackage ? [
                        'id' => (string)$user->currentPackage->id,
                        'name' => $user->currentPackage->name,
                        'price' => (int)$user->currentPackage->price,
                        'benefits' => $packageBenefits,
                        'duration' => (int)$user->currentPackage->duration_days,
                    ] : null,
                    'referralCode' => $user->referral_code,
                    'referralStats' => [
                        'level1Count' => $referralStats['level1_count'],
                        'level2Count' => $referralStats['level2_count'],
                        'level3Count' => $referralStats['level3_count'],
                        'totalCount' => $referralStats['total_count'],
                    ],
                    'referralEarnings' => [
                        'level1Earnings' => (float)$referralEarnings['level1'],
                        'level2Earnings' => (float)$referralEarnings['level2'],
                        'level3Earnings' => (float)$referralEarnings['level3'],
                        'totalEarnings' => (float)$referralEarnings['total'],
                    ],
                    'earnings' => [
                        'normalEarnings' => (float)$user->getNormalEarnings(),
                        'referralEarnings' => (float)$user->getReferralEarnings(),
                        'totalEarnings' => (float)$user->getTotalEarnings(),
                    ],
                    'referrals' => $user->referrals->map(function ($referral) {
                        return [
                            'id' => (string)$referral->id,
                            'username' => $referral->username,
                            'email' => $referral->email,
                            'fullName' => $referral->name,
                            'createdAt' => $referral->created_at->toISOString(),
                        ];
                    }),
                    'referredBy' => $user->referredBy ? [
                        'id' => (string)$user->referredBy->id,
                        'username' => $user->referredBy->username,
                        'email' => $user->referredBy->email,
                        'fullName' => $user->referredBy->name,
                    ] : null,
                    'projectsCount' => $user->projects->count(),
                    'imagesCount' => $user->images->count(),
                    'createdAt' => $user->created_at->toISOString(),
                    'updatedAt' => $user->updated_at->toISOString(),
                ]
            ],
            'message' => 'Profile updated successfully',
        ]);
    }

    /**
     * Change password
     */
    public function changePassword(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        
        $validated = $request->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if (!Hash::check($validated['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['The current password is incorrect.'],
            ]);
        }

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        // Optionally revoke all tokens to force re-login
        // $user->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully.',
        ]);
    }

    /**
     * Get user's API tokens
     */
    public function tokens(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $tokens = $user->tokens()->select(['id', 'name', 'created_at', 'last_used_at'])->get();

        return response()->json([
            'success' => true,
            'data' => $tokens,
        ]);
    }

    /**
     * Create a new API token
     */
    public function createToken(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $token = $user->createToken($validated['name'])->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Token created successfully',
            'data' => [
                'token' => $token,
                'name' => $validated['name'],
            ],
        ]);
    }

    /**
     * Revoke a specific API token
     */
    public function revokeToken(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $validated = $request->validate([
            'token_id' => 'required|exists:personal_access_tokens,id',
        ]);

        $user->tokens()->where('id', $validated['token_id'])->delete();

        return response()->json([
            'success' => true,
            'message' => 'Token revoked successfully',
        ]);
    }
}