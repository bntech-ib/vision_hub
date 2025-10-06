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
     * Generate a unique referral code
     * Uses the username if it's unique, otherwise generates a random code
     */
    private function generateUniqueReferralCode(string $username): string
    {
        // Check if username is already used as a referral code
        if (!User::where('referral_code', $username)->exists()) {
            return $username;
        }
        
        // If username is already used, generate a random code
        do {
            $referralCode = strtoupper(Str::random(6));
        } while (User::where('referral_code', $referralCode)->exists());
        
        return $referralCode;
    }

    /**
     * Register a new user
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'fullName' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'country' => 'nullable|string|max:255',
            'referralCode' => 'nullable|string|exists:users,referral_code',
            'accessKey' => 'required|string|exists:access_keys,key,is_used,0,is_active,1',
        ]);

        // Validate access key
        $accessKey = AccessKey::where('key', $validated['accessKey'])
            ->where('is_used', false)
            ->where('is_active', true)
            ->first();

        if (!$accessKey) {
            throw ValidationException::withMessages([
                'accessKey' => ['The provided access key is invalid or has already been used.'],
            ]);
        }

        // Check if referral code exists and get the referring user
        $referredBy = null;
        if (!empty($validated['referralCode'])) {
            $referredBy = User::where('referral_code', $validated['referralCode'])->first();
        }

        // Create user
        $user = User::create([
            'name' => $validated['fullName'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'country' => $validated['country'] ?? null,
            'referral_code' => $this->generateUniqueReferralCode($validated['username']),
            'referred_by' => $referredBy ? $referredBy->id : null,
            'current_package_id' => $accessKey->package_id,
            'package_expires_at' => $accessKey->package->duration_days ? 
                now()->addDays((int) $accessKey->package->duration_days) : null,
        ]);

        // Award referral bonus to referring user (only for level 1 referrals)
        if ($referredBy) {
            // Only award referral bonus for level 1 referrals
            // Level 2 and 3 referral bonuses have been removed
        }

        // Award welcome bonus from the package to new user
        $packageWelcomeBonus = (float) $accessKey->package->welcome_bonus ?? 0;
        if ($packageWelcomeBonus > 0) {
            $user->addToWelcomeBonus($packageWelcomeBonus);
            
            // Log the welcome bonus transaction
            $user->transactions()->create([
                'amount' => $packageWelcomeBonus,
                'type' => 'welcome_bonus',
                'description' => 'Welcome bonus from ' . $accessKey->package->name . ' package',
                'status' => 'completed',
            ]);
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
                    'package' => $user->currentPackage ? $this->formatPackageDetails($user->currentPackage) : null,
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
                    'package' => $user->currentPackage ? $this->formatPackageDetails($user->currentPackage) : null,
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
                    'package' => $user->currentPackage ? $this->formatPackageDetails($user->currentPackage) : null,
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
                    'package' => $user->currentPackage ? $this->formatPackageDetails($user->currentPackage) : null,
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
                    'package' => $user->currentPackage ? $this->formatPackageDetails($user->currentPackage) : null,
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
        
        $tokens = $user->tokens->map(function ($token) {
            return [
                'id' => (string)$token->id,
                'name' => $token->name,
                'abilities' => $token->abilities,
                'lastUsedAt' => $token->last_used_at ? $token->last_used_at->toISOString() : null,
                'createdAt' => $token->created_at->toISOString(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'tokens' => $tokens
            ]
        ]);
    }

    /**
     * Create a new API token
     */
    public function createToken(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'abilities' => 'array',
        ]);

        /** @var User $user */
        $user = $request->user();
        
        $token = $user->createToken(
            $validated['name'], 
            $validated['abilities'] ?? ['*']
        )->plainTextToken;

        return response()->json([
            'success' => true,
            'data' => [
                'token' => $token
            ],
            'message' => 'API token created successfully'
        ]);
    }

    /**
     * Revoke an API token
     */
    public function revokeToken(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token_id' => 'required|integer',
        ]);

        /** @var User $user */
        $user = $request->user();
        
        $token = $user->tokens()->where('id', $validated['token_id'])->first();
        
        if ($token) {
            $token->delete();
            return response()->json([
                'success' => true,
                'message' => 'API token revoked successfully'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Token not found'
        ], 404);
    }

    /**
     * Format package details with all fields
     */
    private function formatPackageDetails($package)
    {
        return [
            'id' => (string)$package->id,
            'name' => $package->name,
            'description' => $package->description,
            'price' => (float)$package->price,
            'benefits' => $package->features ?? [],
            'duration' => (int)$package->duration_days,
            'adViewsLimit' => (int)$package->ad_views_limit,
            'courseAccessLimit' => (int)$package->course_access_limit,
            'marketplaceAccess' => (bool)$package->marketplace_access,
            'brainTeaserAccess' => (bool)$package->brain_teaser_access,
            'isActive' => (bool)$package->is_active,
            'referralEarningPercentage' => (float)$package->referral_earning_percentage,
            'welcomeBonus' => (float)$package->welcome_bonus,
            'dailyEarningLimit' => (float)$package->daily_earning_limit,
            'adLimits' => (int)$package->ad_limits,
            'createdAt' => $package->created_at->toISOString(),
            'updatedAt' => $package->updated_at->toISOString(),
        ];
    }
}