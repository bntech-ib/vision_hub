<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\AccessKey;
use App\Models\UserPackage;

class SimpleReferralTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_referral_earning_is_added()
    {
        // Create a package for testing
        $package = UserPackage::factory()->create([
            'name' => 'Test Package',
            'referral_earning_percentage' => 150, // Fixed amount for referral earnings
            'is_active' => true,
            'welcome_bonus' => 50
        ]);

        // Create access keys
        $referrerAccessKey = AccessKey::factory()->create([
            'package_id' => $package->id,
            'is_used' => false
        ]);
        
        $referredAccessKey = AccessKey::factory()->create([
            'package_id' => $package->id,
            'is_used' => false
        ]);

        // Create referrer user
        $referrer = User::factory()->create([
            'referral_code' => 'REFERRER',
            'referral_earnings' => 200 // Starting with 200 referral earnings
        ]);

        // Register a new user with the referrer
        $response = $this->postJson('/api/v1/auth/register', [
            'fullName' => 'New User',
            'username' => 'newuser',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'accessKey' => $referredAccessKey->key,
            'referralCode' => 'REFERRER'
        ]);

        $response->assertStatus(201);
        
        // Check if referral bonus was created
        $this->assertDatabaseHas('referral_bonuses', [
            'referrer_id' => $referrer->id,
            'level' => 1,
            'amount' => 150
        ]);
        
        // Refresh referrer data
        $referrer->refresh();

        // Check that referral earnings were added
        $this->assertEquals(350, $referrer->referral_earnings); // 200 + 150
    }
    
    /** @test */
    public function test_referral_code_validation()
    {
        // Create a package for testing
        $package = UserPackage::factory()->create([
            'name' => 'Test Package',
            'referral_earning_percentage' => 150,
            'is_active' => true
        ]);

        // Create access key
        $accessKey = AccessKey::factory()->create([
            'package_id' => $package->id,
            'is_used' => false
        ]);

        // Try to register with non-existent referral code
        $response = $this->postJson('/api/v1/auth/register', [
            'fullName' => 'New User',
            'username' => 'newuser',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'accessKey' => $accessKey->key,
            'referralCode' => 'NONEXISTENT'
        ]);

        // This should fail due to validation
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('referralCode');
    }
    
    /** @test */
    public function test_any_user_can_receive_referral_earnings()
    {
        // Create a package for testing
        $package = UserPackage::factory()->create([
            'name' => 'Test Package',
            'referral_earning_percentage' => 150,
            'is_active' => true
        ]);

        // Create access key
        $accessKey = AccessKey::factory()->create([
            'package_id' => $package->id,
            'is_used' => false
        ]);

        // Create referrer user (any user can now receive referral earnings)
        $referrer = User::factory()->create([
            'referral_code' => 'ANY_REFERRER',
            'referral_earnings' => 200 // Starting with 200 referral earnings
        ]);

        // Register a new user with the referrer
        $response = $this->postJson('/api/v1/auth/register', [
            'fullName' => 'New User',
            'username' => 'newuser2',
            'email' => 'newuser2@example.com',
            'password' => 'password123',
            'accessKey' => $accessKey->key,
            'referralCode' => 'ANY_REFERRER'
        ]);

        $response->assertStatus(201);
        
        // Check that referral bonus was created
        $this->assertDatabaseHas('referral_bonuses', [
            'referrer_id' => $referrer->id,
            'level' => 1,
            'amount' => 150
        ]);
        
        // Refresh referrer data
        $referrer->refresh();

        // Check that referral earnings were added
        $this->assertEquals(350, $referrer->referral_earnings); // 200 + 150
    }
    
    /** @test */
    public function test_referral_with_referrer_code_parameter()
    {
        // Create a package for testing
        $package = UserPackage::factory()->create([
            'name' => 'Test Package',
            'referral_earning_percentage' => 150,
            'is_active' => true
        ]);

        // Create access key
        $accessKey = AccessKey::factory()->create([
            'package_id' => $package->id,
            'is_used' => false
        ]);

        // Create referrer user
        $referrer = User::factory()->create([
            'referral_code' => 'REFERRER_CODE',
            'referral_earnings' => 100 // Starting with 100 referral earnings
        ]);

        // Register a new user with the referrer using referrerCode parameter
        $response = $this->postJson('/api/v1/auth/register', [
            'fullName' => 'New User',
            'username' => 'newuser3',
            'email' => 'newuser3@example.com',
            'password' => 'password123',
            'accessKey' => $accessKey->key,
            'referrerCode' => 'REFERRER_CODE' // Using referrerCode instead of referralCode
        ]);

        $response->assertStatus(201);
        
        // Check that referral bonus was created
        $this->assertDatabaseHas('referral_bonuses', [
            'referrer_id' => $referrer->id,
            'level' => 1,
            'amount' => 150
        ]);
        
        // Refresh referrer data
        $referrer->refresh();

        // Check that referral earnings were added
        $this->assertEquals(250, $referrer->referral_earnings); // 100 + 150
    }
    
    /** @test */
    public function test_real_world_scenario_with_actual_request_data()
    {
        // Create a package with ID 3 (matching the request data)
        $package = UserPackage::factory()->create([
            'id' => 3,
            'name' => 'Basic Package',
            'referral_earning_percentage' => 100, // Fixed amount for referral earnings
            'is_active' => true,
            'welcome_bonus' => 50
        ]);

        // Create access key (matching the request data)
        $accessKey = AccessKey::factory()->create([
            'key' => '9LEAQUNRRZMC1HXK',
            'package_id' => 3,
            'is_used' => false
        ]);

        // Create referrer user with the exact referral code from the request
        $referrer = User::factory()->create([
            'referral_code' => 'gargar22',
            'referral_earnings' => 0 // Starting with 0 referral earnings
        ]);

        // Register a new user with the exact data structure from the request
        $response = $this->postJson('/api/v1/auth/register', [
            'accessKey' => '9LEAQUNRRZMC1HXK',
            'confirmPassword' => '11223344',
            'country' => 'Nigeria',
            'email' => 'kjnvkf@gmail.com',
            'fullName' => 'hjkfvff',
            'packageId' => 3,
            'password' => '11223344',
            'phone' => '11223344557',
            'referrerCode' => 'gargar22', // Using referrerCode as in the request
            'username' => 'fvoikefv'
        ]);

        // The request should be successful
        $response->assertStatus(201);
        
        // Check that referral bonus was created
        $this->assertDatabaseHas('referral_bonuses', [
            'referrer_id' => $referrer->id,
            'level' => 1,
            'amount' => 100
        ]);
        
        // Refresh referrer data
        $referrer->refresh();

        // Check that referral earnings were added
        $this->assertEquals(100, $referrer->referral_earnings); // 0 + 100
    }
}