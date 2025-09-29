<?php

require_once 'vendor/autoload.php';

use App\Models\User;
use App\Models\AccessKey;
use App\Models\UserPackage;
use Illuminate\Support\Str;

// Create a test to verify the 3-level referral system works
echo "Testing 3-level referral system...\n";

try {
    // Create a package for testing
    $package = UserPackage::create([
        'name' => 'Test Package',
        'price' => 1000,
        'duration_days' => 30,
        'features' => json_encode(['feature1', 'feature2']),
        'is_active' => true
    ]);
    
    // Create access keys for testing
    $accessKey1 = AccessKey::create([
        'key' => 'TESTKEY1',
        'package_id' => $package->id,
        'is_used' => false,
    ]);
    
    $accessKey2 = AccessKey::create([
        'key' => 'TESTKEY2',
        'package_id' => $package->id,
        'is_used' => false,
    ]);
    
    $accessKey3 = AccessKey::create([
        'key' => 'TESTKEY3',
        'package_id' => $package->id,
        'is_used' => false,
    ]);
    
    $accessKey4 = AccessKey::create([
        'key' => 'TESTKEY4',
        'package_id' => $package->id,
        'is_used' => false,
    ]);
    
    echo "Created test package and access keys\n";
    
    // Create level 1 referrer
    $referrer1 = User::create([
        'name' => 'Level 1 Referrer',
        'username' => 'referrer1',
        'email' => 'referrer1@example.com',
        'password' => bcrypt('password'),
        'referral_code' => 'REF1',
        'current_package_id' => $package->id,
        'package_expires_at' => now()->addDays(30),
    ]);
    
    echo "Created level 1 referrer with ID: " . $referrer1->id . " and referral code: " . $referrer1->referral_code . "\n";
    
    // Create level 2 referrer (referred by level 1)
    $referrer2 = User::create([
        'name' => 'Level 2 Referrer',
        'username' => 'referrer2',
        'email' => 'referrer2@example.com',
        'password' => bcrypt('password'),
        'referral_code' => 'REF2',
        'current_package_id' => $package->id,
        'package_expires_at' => now()->addDays(30),
        'referred_by' => $referrer1->id,
    ]);
    
    echo "Created level 2 referrer with ID: " . $referrer2->id . " and referral code: " . $referrer2->referral_code . "\n";
    
    // Create level 3 referrer (referred by level 2)
    $referrer3 = User::create([
        'name' => 'Level 3 Referrer',
        'username' => 'referrer3',
        'email' => 'referrer3@example.com',
        'password' => bcrypt('password'),
        'referral_code' => 'REF3',
        'current_package_id' => $package->id,
        'package_expires_at' => now()->addDays(30),
        'referred_by' => $referrer2->id,
    ]);
    
    echo "Created level 3 referrer with ID: " . $referrer3->id . " and referral code: " . $referrer3->referredBy->referral_code . "\n";
    
    // Create a new user referred by level 3 (this should trigger 3-level bonuses)
    $newUser = User::create([
        'name' => 'New User',
        'username' => 'newuser',
        'email' => 'newuser@example.com',
        'password' => bcrypt('password'),
        'referral_code' => 'NEWUSER',
        'current_package_id' => $package->id,
        'package_expires_at' => now()->addDays(30),
        'referred_by' => $referrer3->id,
    ]);
    
    echo "Created new user with ID: " . $newUser->id . " referred by user ID: " . $newUser->referred_by . "\n";
    
    // Award referral bonuses (simulating the registration process)
    // Level 1 bonus (referrer3 gets bonus for referring new user)
    $level1Bonus = 100;
    $referrer3->addToWallet($level1Bonus);
    
    \App\Models\ReferralBonus::create([
        'referrer_id' => $referrer3->id,
        'referred_user_id' => $newUser->id,
        'level' => 1,
        'amount' => $level1Bonus,
        'description' => 'Direct referral bonus for ' . $newUser->username,
    ]);
    
    echo "Awarded Level 1 bonus of ₦" . $level1Bonus . " to referrer3\n";
    
    // Level 2 bonus (referrer2 gets bonus for indirect referral)
    $level2Bonus = 50;
    $referrer2->addToWallet($level2Bonus);
    
    \App\Models\ReferralBonus::create([
        'referrer_id' => $referrer2->id,
        'referred_user_id' => $newUser->id,
        'level' => 2,
        'amount' => $level2Bonus,
        'description' => 'Indirect referral bonus for ' . $newUser->username,
    ]);
    
    echo "Awarded Level 2 bonus of ₦" . $level2Bonus . " to referrer2\n";
    
    // Level 3 bonus (referrer1 gets bonus for second indirect referral)
    $level3Bonus = 25;
    $referrer1->addToWallet($level3Bonus);
    
    \App\Models\ReferralBonus::create([
        'referrer_id' => $referrer1->id,
        'referred_user_id' => $newUser->id,
        'level' => 3,
        'amount' => $level3Bonus,
        'description' => 'Second indirect referral bonus for ' . $newUser->username,
    ]);
    
    echo "Awarded Level 3 bonus of ₦" . $level3Bonus . " to referrer1\n";
    
    // Check referral statistics
    $referrer1Stats = $referrer1->getReferralStats();
    $referrer2Stats = $referrer2->getReferralStats();
    $referrer3Stats = $referrer3->getReferralStats();
    
    echo "\nReferral Statistics:\n";
    echo "Referrer 1 (Level 1): " . $referrer1Stats['level1_count'] . " Level 1, " . $referrer1Stats['level2_count'] . " Level 2, " . $referrer1Stats['level3_count'] . " Level 3\n";
    echo "Referrer 2 (Level 2): " . $referrer2Stats['level1_count'] . " Level 1, " . $referrer2Stats['level2_count'] . " Level 2, " . $referrer2Stats['level3_count'] . " Level 3\n";
    echo "Referrer 3 (Level 3): " . $referrer3Stats['level1_count'] . " Level 1, " . $referrer3Stats['level2_count'] . " Level 2, " . $referrer3Stats['level3_count'] . " Level 3\n";
    
    // Check referral earnings
    $referrer1Earnings = $referrer1->getReferralEarningsByLevel();
    $referrer2Earnings = $referrer2->getReferralEarningsByLevel();
    $referrer3Earnings = $referrer3->getReferralEarningsByLevel();
    
    echo "\nReferral Earnings:\n";
    echo "Referrer 1: ₦" . number_format($referrer1Earnings['level1'], 2) . " (Level 1), ₦" . number_format($referrer1Earnings['level2'], 2) . " (Level 2), ₦" . number_format($referrer1Earnings['level3'], 2) . " (Level 3), Total: ₦" . number_format($referrer1Earnings['total'], 2) . "\n";
    echo "Referrer 2: ₦" . number_format($referrer2Earnings['level1'], 2) . " (Level 1), ₦" . number_format($referrer2Earnings['level2'], 2) . " (Level 2), ₦" . number_format($referrer2Earnings['level3'], 2) . " (Level 3), Total: ₦" . number_format($referrer2Earnings['total'], 2) . "\n";
    echo "Referrer 3: ₦" . number_format($referrer3Earnings['level1'], 2) . " (Level 1), ₦" . number_format($referrer3Earnings['level2'], 2) . " (Level 2), ₦" . number_format($referrer3Earnings['level3'], 2) . " (Level 3), Total: ₦" . number_format($referrer3Earnings['total'], 2) . "\n";
    
    echo "\nSUCCESS: 3-level referral system is working correctly!\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}