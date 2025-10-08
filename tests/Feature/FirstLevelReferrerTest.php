<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\AccessKey;
use App\Models\UserPackage;
use App\Models\ReferralBonus;

class FirstLevelReferrerTest extends TestCase
{
    use RefreshDatabase;

    protected $referrer;
    protected $package;
    protected $accessKey;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a package for testing
        $this->package = UserPackage::factory()->create([
            'name' => 'Test Package',
            'referral_earning_percentage' => 150, // Fixed amount for referral earnings
            'is_active' => true,
            'welcome_bonus' => 50
        ]);

        // Create access key
        $this->accessKey = AccessKey::factory()->create([
            'package_id' => $this->package->id,
            'is_used' => false
        ]);

        // Create referrer user with some initial referral earnings
        $this->referrer = User::factory()->create([
            'referral_code' => 'FIRSTLEVEL',
            'referral_earnings' => 200, // Starting with 200 referral earnings
            'wallet_balance' => 500, // Starting with 500 wallet balance
            'welcome_bonus' => 50 // Starting with 50 welcome bonus
        ]);
    }

    /** @test */
    public function first_level_referrer_gets_correct_earnings_on_new_user_registration()
    {
        // Register a new user with the referrer
        $response = $this->postJson('/api/v1/auth/register', [
            'fullName' => 'New User',
            'username' => 'newuser',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'accessKey' => $this->accessKey->key,
            'referralCode' => 'FIRSTLEVEL'
        ]);

        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
            'message' => 'Registration successful! Welcome to VisionHub!'
        ]);

        // Refresh referrer data
        $this->referrer->refresh();

        // Check that referral earnings were added correctly
        // Initial: 200, Added: 150, Total: 350
        $this->assertEquals(350, $this->referrer->referral_earnings);

        // Check that wallet balance is unchanged (referral earnings don't go to wallet)
        $this->assertEquals(500, $this->referrer->wallet_balance);

        // Check that welcome bonus is unchanged
        $this->assertEquals(50, $this->referrer->welcome_bonus);

        // Check that a referral bonus record was created for level 1
        $this->assertDatabaseHas('referral_bonuses', [
            'referrer_id' => $this->referrer->id,
            'level' => 1,
            'amount' => 150,
            'description' => 'Direct referral bonus for newuser'
        ]);

        // Check that a transaction record was created for the referral earning
        $this->assertDatabaseHas('transactions', [
            'user_id' => $this->referrer->id,
            'type' => 'referral_earning',
            'amount' => 150,
            'description' => 'Direct referral bonus for newuser',
            'status' => 'completed'
        ]);
    }

    /** @test */
    public function first_level_referrer_gets_default_earnings_when_package_has_no_referral_percentage()
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

        // Register a new user with the referrer
        $response = $this->postJson('/api/v1/auth/register', [
            'fullName' => 'Basic User',
            'username' => 'basicuser',
            'email' => 'basicuser@example.com',
            'password' => 'password123',
            'accessKey' => $accessKey->key,
            'referralCode' => 'FIRSTLEVEL'
        ]);

        $response->assertStatus(201);

        // Refresh referrer data
        $this->referrer->refresh();

        // Check that referral earnings were added with default amount (100)
        // Initial: 200, Added: 100, Total: 300
        $this->assertEquals(300, $this->referrer->referral_earnings);

        // Check that a referral bonus record was created with default amount
        $this->assertDatabaseHas('referral_bonuses', [
            'referrer_id' => $this->referrer->id,
            'level' => 1,
            'amount' => 100, // Default amount
            'description' => 'Direct referral bonus for basicuser'
        ]);
    }

    /** @test */
    public function referrer_statistics_are_updated_correctly()
    {
        // Initially the referrer should have 0 referrals
        $initialStats = $this->referrer->getReferralStats();
        $this->assertEquals(0, $initialStats['level1_count']);
        $this->assertEquals(0, $initialStats['total_count']);

        // Register a new user with the referrer
        $response = $this->postJson('/api/v1/auth/register', [
            'fullName' => 'First Referred User',
            'username' => 'firstuser',
            'email' => 'firstuser@example.com',
            'password' => 'password123',
            'accessKey' => $this->accessKey->key,
            'referralCode' => 'FIRSTLEVEL'
        ]);

        $response->assertStatus(201);

        // Check referrer statistics after first referral
        $statsAfterFirst = $this->referrer->getReferralStats();
        $this->assertEquals(1, $statsAfterFirst['level1_count']);
        $this->assertEquals(1, $statsAfterFirst['total_count']);

        // Register another user with the same referrer
        $accessKey2 = AccessKey::factory()->create([
            'package_id' => $this->package->id,
            'is_used' => false
        ]);

        $response2 = $this->postJson('/api/v1/auth/register', [
            'fullName' => 'Second Referred User',
            'username' => 'seconduser',
            'email' => 'seconduser@example.com',
            'password' => 'password123',
            'accessKey' => $accessKey2->key,
            'referralCode' => 'FIRSTLEVEL'
        ]);

        $response2->assertStatus(201);

        // Check referrer statistics after second referral
        $statsAfterSecond = $this->referrer->getReferralStats();
        $this->assertEquals(2, $statsAfterSecond['level1_count']);
        $this->assertEquals(2, $statsAfterSecond['total_count']);
    }

    /** @test */
    public function referral_earnings_are_segregated_from_wallet_balance()
    {
        // Get initial balances
        $initialReferralEarnings = $this->referrer->referral_earnings;
        $initialWalletBalance = $this->referrer->wallet_balance;
        $initialWelcomeBonus = $this->referrer->welcome_bonus;
        $initialTotalEarnings = $this->referrer->getTotalEarnings();

        // Register a new user with the referrer
        $response = $this->postJson('/api/v1/auth/register', [
            'fullName' => 'Segregation Test User',
            'username' => 'segtestuser',
            'email' => 'segtestuser@example.com',
            'password' => 'password123',
            'accessKey' => $this->accessKey->key,
            'referralCode' => 'FIRSTLEVEL'
        ]);

        $response->assertStatus(201);

        // Refresh referrer data
        $this->referrer->refresh();

        // Check that only referral earnings were updated
        $this->assertEquals($initialReferralEarnings + 150, $this->referrer->referral_earnings);
        $this->assertEquals($initialWalletBalance, $this->referrer->wallet_balance); // Unchanged
        $this->assertEquals($initialWelcomeBonus, $this->referrer->welcome_bonus); // Unchanged
        $this->assertEquals($initialTotalEarnings + 150, $this->referrer->getTotalEarnings());
    }

    /** @test */
    public function user_registered_without_referrer_does_not_award_earnings()
    {
        // Get initial balances
        $initialReferralEarnings = $this->referrer->referral_earnings;

        // Register a new user WITHOUT a referrer
        $response = $this->postJson('/api/v1/auth/register', [
            'fullName' => 'No Referrer User',
            'username' => 'noreferuser',
            'email' => 'noreferuser@example.com',
            'password' => 'password123',
            'accessKey' => $this->accessKey->key
            // No referralCode provided
        ]);

        $response->assertStatus(201);

        // Refresh referrer data
        $this->referrer->refresh();

        // Check that referrer's earnings are unchanged
        $this->assertEquals($initialReferralEarnings, $this->referrer->referral_earnings);

        // Check that no referral bonus record was created
        $this->assertDatabaseMissing('referral_bonuses', [
            'referred_user_id' => User::where('username', 'noreferuser')->first()->id
        ]);
    }

    /** @test */
    public function referral_bonus_records_contain_correct_information()
    {
        // Register a new user with the referrer
        $response = $this->postJson('/api/v1/auth/register', [
            'fullName' => 'Record Test User',
            'username' => 'recordtestuser',
            'email' => 'recordtestuser@example.com',
            'password' => 'password123',
            'confirmPassword' => 'password123',
            'accessKey' => $this->accessKey->key,
            'referralCode' => 'FIRSTLEVEL'
        ]);

        $response->assertStatus(201);

        // Get the newly created user
        $newUser = User::where('username', 'recordtestuser')->first();

        // Check that referral bonus record has correct information
        $referralBonus = ReferralBonus::where('referrer_id', $this->referrer->id)
            ->where('referred_user_id', $newUser->id)
            ->first();

        $this->assertNotNull($referralBonus);
        $this->assertEquals($this->referrer->id, $referralBonus->referrer_id);
        $this->assertEquals($newUser->id, $referralBonus->referred_user_id);
        $this->assertEquals(1, $referralBonus->level);
        $this->assertEquals(150, $referralBonus->amount);
        $this->assertEquals('Direct referral bonus for recordtestuser', $referralBonus->description);

        // Check relationships
        $this->assertEquals($this->referrer->id, $referralBonus->referrer->id);
        $this->assertEquals($newUser->id, $referralBonus->referredUser->id);
    }
}