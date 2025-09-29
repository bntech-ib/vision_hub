<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\AccessKey;
use App\Models\UserPackage;
use App\Models\ReferralBonus;
use Illuminate\Support\Str;

class ReferralSystemTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test the 3-level referral system
     */
    public function test_3_level_referral_system(): void
    {
        // Create an admin user for the created_by field
        $adminUser = User::factory()->admin()->create();
        
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
            'created_by' => $adminUser->id,
        ]);
        
        $accessKey2 = AccessKey::create([
            'key' => 'TESTKEY2',
            'package_id' => $package->id,
            'is_used' => false,
            'created_by' => $adminUser->id,
        ]);
        
        $accessKey3 = AccessKey::create([
            'key' => 'TESTKEY3',
            'package_id' => $package->id,
            'is_used' => false,
            'created_by' => $adminUser->id,
        ]);
        
        $accessKey4 = AccessKey::create([
            'key' => 'TESTKEY4',
            'package_id' => $package->id,
            'is_used' => false,
            'created_by' => $adminUser->id,
        ]);
        
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
        
        // Award referral bonuses (simulating the registration process)
        // Level 1 bonus (referrer3 gets bonus for referring new user)
        $level1Bonus = 100;
        $referrer3->addToWallet($level1Bonus);
        
        ReferralBonus::create([
            'referrer_id' => $referrer3->id,
            'referred_user_id' => $newUser->id,
            'level' => 1,
            'amount' => $level1Bonus,
            'description' => 'Direct referral bonus for ' . $newUser->username,
        ]);
        
        // Level 2 bonus (referrer2 gets bonus for indirect referral)
        $level2Bonus = 50;
        $referrer2->addToWallet($level2Bonus);
        
        ReferralBonus::create([
            'referrer_id' => $referrer2->id,
            'referred_user_id' => $newUser->id,
            'level' => 2,
            'amount' => $level2Bonus,
            'description' => 'Indirect referral bonus for ' . $newUser->username,
        ]);
        
        // Level 3 bonus (referrer1 gets bonus for second indirect referral)
        $level3Bonus = 25;
        $referrer1->addToWallet($level3Bonus);
        
        ReferralBonus::create([
            'referrer_id' => $referrer1->id,
            'referred_user_id' => $newUser->id,
            'level' => 3,
            'amount' => $level3Bonus,
            'description' => 'Second indirect referral bonus for ' . $newUser->username,
        ]);
        
        // Check referral statistics
        $referrer1Stats = $referrer1->getReferralStats();
        $referrer2Stats = $referrer2->getReferralStats();
        $referrer3Stats = $referrer3->getReferralStats();
        
        // Check referral earnings
        $referrer1Earnings = $referrer1->getReferralEarningsByLevel();
        $referrer2Earnings = $referrer2->getReferralEarningsByLevel();
        $referrer3Earnings = $referrer3->getReferralEarningsByLevel();
        
        // Assertions for referrer1
        // referrer1 has 1 level 1 referral (referrer2)
        $this->assertEquals(1, $referrer1Stats['level1_count']);
        // referrer1 has 1 level 2 referral (referrer3, referred by referrer2)
        $this->assertEquals(1, $referrer1Stats['level2_count']);
        // referrer1 has 1 level 3 referral (newUser, referred by referrer3)
        $this->assertEquals(1, $referrer1Stats['level3_count']);
        
        // Assertions for referrer2
        // referrer2 has 1 level 1 referral (referrer3)
        $this->assertEquals(1, $referrer2Stats['level1_count']);
        // referrer2 has 1 level 2 referral (newUser, referred by referrer3)
        $this->assertEquals(1, $referrer2Stats['level2_count']);
        // referrer2 has 0 level 3 referrals
        $this->assertEquals(0, $referrer2Stats['level3_count']);
        
        // Assertions for referrer3
        // referrer3 has 1 level 1 referral (newUser)
        $this->assertEquals(1, $referrer3Stats['level1_count']);
        // referrer3 has 0 level 2 referrals
        $this->assertEquals(0, $referrer3Stats['level2_count']);
        // referrer3 has 0 level 3 referrals
        $this->assertEquals(0, $referrer3Stats['level3_count']);
        
        // Check referral earnings
        $this->assertEquals($level3Bonus, $referrer1Earnings['level3']); // referrer1 earned level 3 bonus
        $this->assertEquals($level2Bonus, $referrer2Earnings['level2']); // referrer2 earned level 2 bonus
        $this->assertEquals($level1Bonus, $referrer3Earnings['level1']); // referrer3 earned level 1 bonus
        
        $this->assertEquals($level3Bonus, $referrer1Earnings['total']); // referrer1 total earnings
        $this->assertEquals($level2Bonus, $referrer2Earnings['total']); // referrer2 total earnings
        $this->assertEquals($level1Bonus, $referrer3Earnings['total']); // referrer3 total earnings
    }
}