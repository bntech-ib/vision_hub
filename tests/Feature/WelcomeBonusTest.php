<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\UserPackage;
use App\Models\AccessKey;
use Illuminate\Support\Str;

class WelcomeBonusTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that new users receive a welcome bonus from their package upon registration.
     *
     * @return void
     */
    public function test_new_user_receives_package_welcome_bonus()
    {
        // Create a package with a specific welcome bonus
        $packageWelcomeBonus = 750.00;
        $package = UserPackage::factory()->create([
            'name' => 'Test Package',
            'price' => 10.00,
            'welcome_bonus' => $packageWelcomeBonus,
        ]);

        // Create an access key
        $accessKey = AccessKey::factory()->create([
            'package_id' => $package->id,
            'key' => strtoupper(Str::random(16)),
        ]);

        // Prepare registration data
        $userData = [
            'fullName' => 'Test User',
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'password123',
            'confirmPassword' => 'password123',
            'accessKey' => $accessKey->key,
            'country' => 'USA',
        ];

        // Register the user
        $response = $this->postJson('/api/v1/auth/register', $userData);

        // Assert successful registration
        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
            'message' => 'Registration successful! Welcome to VisionHub!',
        ]);

        // Check that the user was created
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'username' => 'testuser',
        ]);

        // Get the created user
        $user = User::where('email', 'test@example.com')->first();

        // Check that the user received the package welcome bonus
        $this->assertEquals($packageWelcomeBonus, $user->welcome_bonus);
        $this->assertEquals($packageWelcomeBonus, $user->getWelcomeBonus());
        $this->assertTrue($user->hasClaimedWelcomeBonus());

        // Check that a transaction was created for the welcome bonus
        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'amount' => $packageWelcomeBonus,
            'type' => 'welcome_bonus',
            'description' => 'Welcome bonus from ' . $package->name . ' package',
        ]);
    }

    /**
     * Test that new users with a package that has no welcome bonus receive zero bonus.
     *
     * @return void
     */
    public function test_new_user_with_no_package_welcome_bonus_receives_zero()
    {
        // Create a package with no welcome bonus
        $package = UserPackage::factory()->create([
            'name' => 'Free Package',
            'price' => 0.00,
            'welcome_bonus' => 0.00,
        ]);

        // Create an access key
        $accessKey = AccessKey::factory()->create([
            'package_id' => $package->id,
            'key' => strtoupper(Str::random(16)),
        ]);

        // Prepare registration data
        $userData = [
            'fullName' => 'Test User 2',
            'username' => 'testuser2',
            'email' => 'test2@example.com',
            'password' => 'password123',
            'confirmPassword' => 'password123',
            'accessKey' => $accessKey->key,
            'country' => 'USA',
        ];

        // Register the user
        $response = $this->postJson('/api/v1/auth/register', $userData);

        // Assert successful registration
        $response->assertStatus(201);

        // Get the created user
        $user = User::where('email', 'test2@example.com')->first();

        // Check that the user received zero welcome bonus
        $this->assertEquals(0.00, $user->welcome_bonus);
        $this->assertEquals(0.00, $user->getWelcomeBonus());
        $this->assertFalse($user->hasClaimedWelcomeBonus());
    }

    /**
     * Test that the welcome bonus setting exists (kept for backward compatibility).
     *
     * @return void
     */
    public function test_welcome_bonus_setting_exists()
    {
        // Check that the welcome bonus setting exists
        $this->assertDatabaseHas('global_settings', [
            'key' => 'welcome_bonus_amount',
            'value' => '500.00',
        ]);
    }
}