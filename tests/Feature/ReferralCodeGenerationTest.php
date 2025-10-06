<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\AccessKey;
use App\Models\UserPackage;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ReferralCodeGenerationTest extends TestCase
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
}