<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\AccessKey;
use App\Models\UserPackage;
use App\Models\ReferralBonus;

class ComprehensiveFirstLevelReferralTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function comprehensive_first_level_referral_implementation_test()
    {
        // Create a package for testing with specific referral earning percentage
        $package = UserPackage::factory()->create([
            'name' => 'Premium Package',
            'referral_earning_percentage' => 200.50, // Specific amount for referral earnings
            'is_active' => true,
            'welcome_bonus' => 75.25
        ]);

        // Create access key
        $accessKey = AccessKey::factory()->create([
            'package_id' => $package->id,
            'is_used' => false
        ]);

        // Create referrer user with initial values
        $referrer = User::factory()->create([
            'referral_code' => 'TOPREFERRER',
            'referral_earnings' => 100.00,
            'wallet_balance' => 300.00,
            'welcome_bonus' => 25.00
        ]);

        // Register a new user with the referrer
        $response = $this->postJson('/api/v1/auth/register', [
            'fullName' => 'Referred User',
            'username' => 'referreduser',
            'email' => 'referred@example.com',
            'password' => 'password123',
            'confirmPassword' => 'password123',
            'accessKey' => $accessKey->key,
            'referralCode' => 'TOPREFERRER'
        ]);

        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
            'message' => 'Registration successful! Welcome to VisionHub!'
        ]);

        // Verify the response contains referral information
        $response->assertJsonStructure([
            'data' => [
                'user' => [
                    'referredBy' => [
                        'id',
                        'username',
                        'email',
                        'fullName'
                    ]
                ]
            ]
        ]);

        // Refresh referrer data from database
        $referrer->refresh();

        // Check that referral earnings were added correctly
        // Initial: 100.00, Added: 200.50, Total: 300.50
        $this->assertEquals(300.50, $referrer->referral_earnings);

        // Check that wallet balance is unchanged (referral earnings don't go to wallet)
        $this->assertEquals(300.00, $referrer->wallet_balance);

        // Check that welcome bonus is unchanged
        $this->assertEquals(25.00, $referrer->welcome_bonus);

        // Check that a referral bonus record was created for level 1
        $this->assertDatabaseHas('referral_bonuses', [
            'referrer_id' => $referrer->id,
            'level' => 1,
            'amount' => 200.50,
            'description' => 'Direct referral bonus for referreduser'
        ]);

        // Check that a transaction record was created for the referral earning
        $this->assertDatabaseHas('transactions', [
            'user_id' => $referrer->id,
            'type' => 'referral_earning',
            'amount' => 200.50,
            'description' => 'Direct referral bonus for referreduser',
            'status' => 'completed'
        ]);

        // Verify the new user was created with correct package
        $newUser = User::where('username', 'referreduser')->first();
        $this->assertNotNull($newUser);
        $this->assertEquals($package->id, $newUser->current_package_id);
        $this->assertEquals($referrer->id, $newUser->referred_by);

        // Verify the new user received welcome bonus
        $this->assertEquals(75.25, $newUser->welcome_bonus);
        $this->assertEquals(75.25, $newUser->wallet_balance); // Welcome bonus goes to wallet too

        // Check that a welcome bonus transaction was created for the new user
        $this->assertDatabaseHas('transactions', [
            'user_id' => $newUser->id,
            'type' => 'welcome_bonus',
            'amount' => 75.25,
            'description' => 'Welcome bonus from ' . $package->name . ' package',
            'status' => 'completed'
        ]);

        // Verify access key was marked as used
        $accessKey->refresh();
        $this->assertTrue($accessKey->is_used);
        $this->assertEquals($newUser->id, $accessKey->used_by);
    }

    /** @test */
    public function first_level_referrer_with_default_bonus_when_package_has_no_referral_percentage()
    {
        // Create a package without referral earning percentage
        $packageWithoutReferral = UserPackage::factory()->create([
            'name' => 'Basic Package',
            'referral_earning_percentage' => 0, // No specific percentage
            'is_active' => true
        ]);

        // Create access key for this package
        $accessKey = AccessKey::factory()->create([
            'package_id' => $packageWithoutReferral->id,
            'is_used' => false
        ]);

        // Create referrer user
        $referrer = User::factory()->create([
            'referral_code' => 'BASICREFERRER',
            'referral_earnings' => 50.00
        ]);

        // Register a new user with the referrer
        $response = $this->postJson('/api/v1/auth/register', [
            'fullName' => 'Basic User',
            'username' => 'basicuser',
            'email' => 'basic@example.com',
            'password' => 'password123',
            'confirmPassword' => 'password123',
            'accessKey' => $accessKey->key,
            'referralCode' => 'BASICREFERRER'
        ]);

        $response->assertStatus(201);

        // Refresh referrer data
        $referrer->refresh();

        // Check that referral earnings were added with default amount (100)
        // Initial: 50.00, Added: 100.00, Total: 150.00
        $this->assertEquals(150.00, $referrer->referral_earnings);

        // Check that a referral bonus record was created with default amount
        $this->assertDatabaseHas('referral_bonuses', [
            'referrer_id' => $referrer->id,
            'level' => 1,
            'amount' => 100.00, // Default amount
            'description' => 'Direct referral bonus for basicuser'
        ]);
    }

    /** @test */
    public function user_registered_without_referrer_gets_no_referral_bonus()
    {
        // Create a package
        $package = UserPackage::factory()->create([
            'name' => 'Standalone Package',
            'referral_earning_percentage' => 150.00,
            'is_active' => true
        ]);

        // Create access key
        $accessKey = AccessKey::factory()->create([
            'package_id' => $package->id,
            'is_used' => false
        ]);

        // Create a referrer user (but won't be used)
        $referrer = User::factory()->create([
            'referral_code' => 'UNUSEDREFERRER',
            'referral_earnings' => 100.00
        ]);

        // Register a new user WITHOUT a referrer
        $response = $this->postJson('/api/v1/auth/register', [
            'fullName' => 'Standalone User',
            'username' => 'standalone',
            'email' => 'standalone@example.com',
            'password' => 'password123',
            'confirmPassword' => 'password123',
            'accessKey' => $accessKey->key
            // No referralCode provided
        ]);

        $response->assertStatus(201);

        // Refresh referrer data
        $referrer->refresh();

        // Check that referrer's earnings are unchanged
        $this->assertEquals(100.00, $referrer->referral_earnings);

        // Check that no referral bonus record was created
        $this->assertDatabaseMissing('referral_bonuses', [
            'referred_user_id' => User::where('username', 'standalone')->first()->id
        ]);

        // Verify the new user was created without a referrer
        $newUser = User::where('username', 'standalone')->first();
        $this->assertNull($newUser->referred_by);
    }
}