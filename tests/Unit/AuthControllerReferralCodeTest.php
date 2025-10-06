<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\AccessKey;
use App\Models\UserPackage;
use Illuminate\Support\Str;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthControllerReferralCodeTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_uses_username_as_referral_code_when_unique()
    {
        // Create a package for the access key
        $package = UserPackage::factory()->create();
        
        // Create an access key
        $accessKey = AccessKey::factory()->create([
            'package_id' => $package->id,
            'is_used' => false,
        ]);

        // Register a new user with a unique username
        $response = $this->postJson('/auth/register', [
            'fullName' => 'John Doe',
            'username' => 'johndoe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'confirmPassword' => 'password123',
            'accessKey' => $accessKey->key,
        ]);

        $response->assertStatus(201);
        
        // Check that the user was created with the username as referral code
        $this->assertDatabaseHas('users', [
            'username' => 'johndoe',
            'referral_code' => 'johndoe',
        ]);
    }

    /** @test */
    public function it_generates_random_referral_code_when_username_is_not_unique()
    {
        // Create a package for the access key
        $package = UserPackage::factory()->create();
        
        // Create an access key
        $accessKey = AccessKey::factory()->create([
            'package_id' => $package->id,
            'is_used' => false,
        ]);

        // Create a user with the same username as referral code
        User::factory()->create([
            'referral_code' => 'johndoe',
        ]);

        // Register a new user with the same username
        $response = $this->postJson('/auth/register', [
            'fullName' => 'John Doe',
            'username' => 'johndoe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'confirmPassword' => 'password123',
            'accessKey' => $accessKey->key,
        ]);

        $response->assertStatus(201);
        
        // Get the created user
        $user = User::where('username', 'johndoe')->where('email', 'john@example.com')->first();
        
        // Check that the user was created with a random referral code (not the username)
        $this->assertNotEquals('johndoe', $user->referral_code);
        $this->assertEquals(6, strlen($user->referral_code));
        $this->assertTrue(ctype_upper($user->referral_code));
    }

    /** @test */
    public function it_generates_different_random_codes_for_same_username()
    {
        // Create a package for the access key
        $package = UserPackage::factory()->create();
        
        // Create access keys
        $accessKey1 = AccessKey::factory()->create([
            'package_id' => $package->id,
            'is_used' => false,
        ]);
        
        $accessKey2 = AccessKey::factory()->create([
            'package_id' => $package->id,
            'is_used' => false,
        ]);

        // Create a user with the same username as referral code
        User::factory()->create([
            'referral_code' => 'johndoe',
        ]);

        // Register two new users with the same username
        $response1 = $this->postJson('/auth/register', [
            'fullName' => 'John Doe 1',
            'username' => 'johndoe',
            'email' => 'john1@example.com',
            'password' => 'password123',
            'confirmPassword' => 'password123',
            'accessKey' => $accessKey1->key,
        ]);

        $response2 = $this->postJson('/auth/register', [
            'fullName' => 'John Doe 2',
            'username' => 'johndoe',
            'email' => 'john2@example.com',
            'password' => 'password123',
            'confirmPassword' => 'password123',
            'accessKey' => $accessKey2->key,
        ]);

        $response1->assertStatus(201);
        $response2->assertStatus(201);
        
        // Get the created users
        $user1 = User::where('email', 'john1@example.com')->first();
        $user2 = User::where('email', 'john2@example.com')->first();
        
        // Check that both users have different random referral codes
        $this->assertNotEquals('johndoe', $user1->referral_code);
        $this->assertNotEquals('johndoe', $user2->referral_code);
        $this->assertNotEquals($user1->referral_code, $user2->referral_code);
    }
}