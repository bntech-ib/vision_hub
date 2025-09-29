<?php

require_once 'vendor/autoload.php';

use App\Models\User;

// Create a test to verify the referrals relationship works
echo "Testing User referrals relationship...\n";

try {
    // Create a referrer user
    $referrer = User::factory()->create([
        'referral_code' => 'ABC123',
    ]);
    
    echo "Created referrer user with ID: " . $referrer->id . "\n";
    
    // Create referred users
    $referred1 = User::factory()->create([
        'referred_by' => $referrer->id,
    ]);
    
    $referred2 = User::factory()->create([
        'referred_by' => $referrer->id,
    ]);
    
    echo "Created referred users with IDs: " . $referred1->id . ", " . $referred2->id . "\n";
    
    // Check that the referrer has 2 referrals
    $referralCount = $referrer->referrals()->count();
    echo "Referrer has " . $referralCount . " referrals\n";
    
    if ($referralCount === 2) {
        echo "SUCCESS: Referrals relationship is working correctly!\n";
    } else {
        echo "ERROR: Expected 2 referrals, got " . $referralCount . "\n";
    }
    
    // Check that the referred users have the correct referrer
    if ($referred1->referredBy && $referred1->referredBy->id === $referrer->id) {
        echo "SUCCESS: Referred user 1 has correct referrer\n";
    } else {
        echo "ERROR: Referred user 1 does not have correct referrer\n";
    }
    
    if ($referred2->referredBy && $referred2->referredBy->id === $referrer->id) {
        echo "SUCCESS: Referred user 2 has correct referrer\n";
    } else {
        echo "ERROR: Referred user 2 does not have correct referrer\n";
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}