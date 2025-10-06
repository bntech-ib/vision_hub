<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Request as FacadesRequest;

class UserProfileController extends Controller
{
    /**
     * Get user profile information including bank account status and withdrawal availability
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Load relationships
        $user->load(['currentPackage', 'referrals', 'referredBy']);
        
        // Format package benefits
        $packageBenefits = $user->currentPackage ? $user->currentPackage->features : [];
        if (is_string($packageBenefits)) {
            $packageBenefits = json_decode($packageBenefits, true) ?: [];
        }
        
        // Get referral statistics
        $referralStats = $user->getReferralStats();
        $referralEarnings = $user->getReferralEarningsByLevel();
        
        // Get bank account details directly from the user model
        $bankAccountDetails = $user->getAdminBankAccountDetails();
        
        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
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
                    'has_bound_bank_account' => $user->hasBoundBankAccount(),
                    'bank_account_bound_at' => $user->bank_account_bound_at,
                    // Include bank account details
                    'bank_account_holder_name' => $bankAccountDetails['bank_account_holder_name'],
                    'bank_name' => $bankAccountDetails['bank_name'],
                    'bank_branch' => $bankAccountDetails['bank_branch'],
                    'bank_account_number' => $bankAccountDetails['bank_account_number'],
                    'bank_routing_number' => $bankAccountDetails['bank_routing_number'],
                    // Withdrawal status
                    'withdrawals_enabled' => \App\Models\GlobalSetting::isWithdrawalEnabled(),
                    'can_request_withdrawal' => $user->hasWithdrawalAccess(),
                ]
            ]
        ]);
    }

    /**
     * Bind bank account details to the user
     * This can only be done once
     */
    public function bindBankAccount(Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Check if user has already bound their bank account
        if ($user->hasBoundBankAccount()) {
            return response()->json([
                'success' => false,
                'message' => 'Bank account details have already been bound and cannot be updated.'
            ], 422);
        }
        
        // Validate bank account details
        $validated = $request->validate([
            'bank_account_holder_name' => 'required|string|max:255',
            'bank_account_number' => 'required|string|max:50',
            'bank_name' => 'required|string|max:255',
            'bank_branch' => 'nullable|string|max:255',
            'bank_routing_number' => 'nullable|string|max:50',
        ]);
        
        // Attempt to bind bank account
        if ($user->bindBankAccount($validated)) {
            // Load relationships
            $user->load(['currentPackage', 'referrals', 'referredBy']);
            
            // Format package benefits
            $packageBenefits = $user->currentPackage ? $user->currentPackage->features : [];
            if (is_string($packageBenefits)) {
                $packageBenefits = json_decode($packageBenefits, true) ?: [];
            }
            
            // Get referral statistics
            $referralStats = $user->getReferralStats();
            $referralEarnings = $user->getReferralEarningsByLevel();
            
            // Get updated bank account details
            $bankAccountDetails = $user->getAdminBankAccountDetails();
            
            return response()->json([
                'success' => true,
                'message' => 'Bank account details bound successfully.',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
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
                        'has_bound_bank_account' => $user->hasBoundBankAccount(),
                        'bank_account_bound_at' => $user->bank_account_bound_at,
                        'bank_account_holder_name' => $bankAccountDetails['bank_account_holder_name'],
                        'bank_name' => $bankAccountDetails['bank_name'],
                        'bank_branch' => $bankAccountDetails['bank_branch'],
                        'bank_account_number' => $bankAccountDetails['bank_account_number'],
                        'bank_routing_number' => $bankAccountDetails['bank_routing_number'],
                    ]
                ]
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'Failed to bind bank account details. Please ensure all required fields are filled.'
        ], 422);
    }

    /**
     * Update user profile information (excluding bank account details)
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
        ]);
        
        // Update user profile (bank account fields will be ignored if already bound)
        $user->update($validated);
        
        // Load relationships
        $user->load(['currentPackage', 'referrals', 'referredBy']);
        
        // Format package benefits
        $packageBenefits = $user->currentPackage ? $user->currentPackage->features : [];
        if (is_string($packageBenefits)) {
            $packageBenefits = json_decode($packageBenefits, true) ?: [];
        }
        
        // Get referral statistics
        $referralStats = $user->getReferralStats();
        $referralEarnings = $user->getReferralEarningsByLevel();
        
        // Get updated bank account details
        $bankAccountDetails = $user->getAdminBankAccountDetails();
        
        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully.',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
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
                    'has_bound_bank_account' => $user->hasBoundBankAccount(),
                    'bank_account_bound_at' => $user->bank_account_bound_at,
                    'bank_account_holder_name' => $bankAccountDetails['bank_account_holder_name'],
                    'bank_name' => $bankAccountDetails['bank_name'],
                    'bank_branch' => $bankAccountDetails['bank_branch'],
                    'bank_account_number' => $bankAccountDetails['bank_account_number'],
                    'bank_routing_number' => $bankAccountDetails['bank_routing_number'],
                    'withdrawals_enabled' => \App\Models\GlobalSetting::isWithdrawalEnabled(),
                    'can_request_withdrawal' => $user->hasWithdrawalAccess(),
                ]
            ]
        ]);
    }

    /**
     * Get user username by referral code
     */
    public function getUsernameByReferralCode(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'referral_code' => 'required|string|exists:users,referral_code',
        ]);

        $user = User::where('referral_code', $validated['referral_code'])->first();

        return response()->json([
            'success' => true,
            'data' => [
                'username' => $user->username,
                'referral_code' => $user->referral_code,
            ],
            'message' => 'Username retrieved successfully',
        ]);
    }

    /**
     * Get withdrawal status
     */
    public function getWithdrawalStatus(Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Load relationships
        $user->load(['currentPackage', 'referrals', 'referredBy']);
        
        // Get referral statistics
        $referralStats = $user->getReferralStats();
        $referralEarnings = $user->getReferralEarningsByLevel();
        
        // Get bank account details
        $bankAccountDetails = $user->getAdminBankAccountDetails();
        
        return response()->json([
            'success' => true,
            'data' => [
                'withdrawals_enabled' => \App\Models\GlobalSetting::isWithdrawalEnabled(),
                'can_request_withdrawal' => $user->hasWithdrawalAccess(),
                'user_withdrawal_enabled' => $user->hasWithdrawalAccess(),
                'has_bound_bank_account' => $user->hasBoundBankAccount(),
                'bank_account_holder_name' => $bankAccountDetails['bank_account_holder_name'],
                'bank_name' => $bankAccountDetails['bank_name'],
                'bank_branch' => $bankAccountDetails['bank_branch'],
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
            ]
        ]);
    }

    /**
     * Upgrade user package using a new access key
     */
    public function upgradePackage(Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Validate request
        $validated = $request->validate([
            'access_key' => 'required|string|exists:access_keys,key',
        ]);
        
        // Find the access key
        $accessKey = \App\Models\AccessKey::where('key', $validated['access_key'])->first();
        
        // Check if access key is valid and can be used
        if (!$accessKey || !$accessKey->canBeUsed()) {
            return response()->json([
                'success' => false,
                'message' => 'The access key is invalid, expired, or has already been used.'
            ], 422);
        }
        
        // Check if the access key package matches the requested package
        // (In this case, we're allowing upgrade to any package the access key provides)
        $newPackage = $accessKey->package;
        
        if (!$newPackage || !$newPackage->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'The package associated with this access key is not available.'
            ], 422);
        }
        
        // Store previous package ID for audit trail
        $previousPackageId = $user->current_package_id;
        
        // Update user's package
        $user->update([
            'previous_package_id' => $previousPackageId,
            'current_package_id' => $newPackage->id,
            'package_expires_at' => $newPackage->duration_days ? 
                now()->addDays((int) $newPackage->duration_days) : null,
            'last_package_upgrade_at' => now(),
        ]);
        
        // Mark access key as used and track upgrade request details
        $accessKey->update([
            'is_used' => true,
            'used_by' => $user->id,
            'used_at' => now(),
            'upgrade_requested_at' => now(),
            'upgrade_request_ip' => FacadesRequest::ip(),
            'upgrade_request_user_agent' => FacadesRequest::userAgent(),
        ]);
        
        // Load updated relationships
        $user->load(['currentPackage', 'referrals', 'referredBy']);
        
        // Format package benefits
        $packageBenefits = $user->currentPackage ? $user->currentPackage->features : [];
        if (is_string($packageBenefits)) {
            $packageBenefits = json_decode($packageBenefits, true) ?: [];
        }
        
        // Get referral statistics
        $referralStats = $user->getReferralStats();
        $referralEarnings = $user->getReferralEarningsByLevel();
        
        // Get bank account details
        $bankAccountDetails = $user->getAdminBankAccountDetails();
        
        return response()->json([
            'success' => true,
            'message' => 'Package upgraded successfully.',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
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
                    'has_bound_bank_account' => $user->hasBoundBankAccount(),
                    'bank_account_bound_at' => $user->bank_account_bound_at,
                    'bank_account_holder_name' => $bankAccountDetails['bank_account_holder_name'],
                    'bank_name' => $bankAccountDetails['bank_name'],
                    'bank_branch' => $bankAccountDetails['bank_branch'],
                    'bank_account_number' => $bankAccountDetails['bank_account_number'],
                    'bank_routing_number' => $bankAccountDetails['bank_routing_number'],
                    'withdrawals_enabled' => \App\Models\GlobalSetting::isWithdrawalEnabled(),
                    'can_request_withdrawal' => $user->hasWithdrawalAccess(),
                ]
            ]
        ]);
    }
}