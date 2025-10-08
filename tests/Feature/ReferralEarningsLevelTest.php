<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\AccessKey;
use App\Models\UserPackage;
use Illuminate\Support\Facades\Hash;

class ReferralEarningsLevelTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /** @test */
    public function level_1_referrer_still_gets_earnings()
    {
        // Create packages
        $package = UserPackage::factory()->create([
            'name' => 'Test Package',
            'referral_earning_percentage' => 100, // Fixed amount for referral earnings
            'is_active' => true
        ]);

        // Create access key
        $accessKey = AccessKey::factory()->create([
            'package_id' => $package->id,
            'is_used' => false
        ]);

        // Create level 1 referrer
        $referrer = User::factory()->create([
            'referral_code' => 'REFERRER1',
            'referral_earnings' => 0
        ]);

        // Register new user with referrer
        $response = $this->postJson('/api/v1/auth/register', [
            'fullName' => 'New User',
            'username' => 'newuser',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'accessKey' => $accessKey->key,
            'referralCode' => 'REFERRER1'
        ]);

        $response->assertStatus(201);

        // Refresh referrer data
        $referrer->refresh();

        // Level 1 referrer should still get earnings
        $this->assertEquals(100, $referrer->referral_earnings);
        
        // Check that a referral bonus record was created for level 1
        $this->assertDatabaseHas('referral_bonuses', [
            'referrer_id' => $referrer->id,
            'level' => 1,
            'amount' => 100
        ]);
    }

    /** @test */
    public function level_2_referrer_does_not_get_earnings()
    {
        // Create packages
        $package = UserPackage::factory()->create([
            'name' => 'Test Package',
            'referral_earning_percentage' => 100, // Fixed amount for referral earnings
            'is_active' => true
        ]);

        // Create access keys
        $accessKey1 = AccessKey::factory()->create([
            'package_id' => $package->id,
            'is_used' => false
        ]);

        $accessKey2 = AccessKey::factory()->create([
            'package_id' => $package->id,
            'is_used' => false
        ]);

        // Create level 2 referrer (referrer of referrer)
        $level2Referrer = User::factory()->create([
            'referral_code' => 'LEVEL2REFERRER',
            'referral_earnings' => 0
        ]);

        // Create level 1 referrer
        $level1Referrer = User::factory()->create([
            'referral_code' => 'LEVEL1REFERRER',
            'referral_earnings' => 0,
            'referred_by' => $level2Referrer->id
        ]);

        // Register new user with level 1 referrer
        $response = $this->postJson('/api/v1/auth/register', [
            'fullName' => 'New User',
            'username' => 'newuser',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            
            'accessKey' => $accessKey1->key,
            'referralCode' => 'LEVEL1REFERRER'
        ]);

        $response->assertStatus(201);

        // Refresh level 2 referrer data
        $level2Referrer->refresh();

        // Level 2 referrer should NOT get earnings (as requested)
        $this->assertEquals(0, $level2Referrer->referral_earnings);
        
        // Check that no referral bonus record was created for level 2
        $this->assertDatabaseMissing('referral_bonuses', [
            'referrer_id' => $level2Referrer->id,
            'level' => 2
        ]);
    }

    /** @test */
    public function level_3_referrer_does_not_get_earnings()
    {
        // Create packages
        $package = UserPackage::factory()->create([
            'name' => 'Test Package',
            'referral_earning_percentage' => 100, // Fixed amount for referral earnings
            'is_active' => true
        ]);

        // Create access keys
        $accessKey1 = AccessKey::factory()->create([
            'package_id' => $package->id,
            'is_used' => false
        ]);

        $accessKey2 = AccessKey::factory()->create([
            'package_id' => $package->id,
            'is_used' => false
        ]);

        $accessKey3 = AccessKey::factory()->create([
            'package_id' => $package->id,
            'is_used' => false
        ]);

        // Create level 3 referrer (referrer of referrer of referrer)
        $level3Referrer = User::factory()->create([
            'referral_code' => 'LEVEL3REFERRER',
            'referral_earnings' => 0
        ]);

        // Create level 2 referrer
        $level2Referrer = User::factory()->create([
            'referral_code' => 'LEVEL2REFERRER',
            'referral_earnings' => 0,
            'referred_by' => $level3Referrer->id
        ]);

        // Create level 1 referrer
        $level1Referrer = User::factory()->create([
            'referral_code' => 'LEVEL1REFERRER',
            'referral_earnings' => 0,
            'referred_by' => $level2Referrer->id
        ]);

        // Register new user with level 1 referrer
        $response = $this->postJson('/api/v1/auth/register', [
            'fullName' => 'New User',
            'username' => 'newuser',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            
            'accessKey' => $accessKey1->key,
            'referralCode' => 'LEVEL1REFERRER'
        ]);

        $response->assertStatus(201);

        // Refresh level 3 referrer data
        $level3Referrer->refresh();

        // Level 3 referrer should NOT get earnings (as requested)
        $this->assertEquals(0, $level3Referrer->referral_earnings);
        
        // Check that no referral bonus record was created for level 3
        $this->assertDatabaseMissing('referral_bonuses', [
            'referrer_id' => $level3Referrer->id,
            'level' => 3
        ]);
    }
}