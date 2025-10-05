<?php

namespace Tests\Feature\API;

use Tests\TestCase;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GetUsernameByReferralCodeTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_get_username_by_referral_code()
    {
        // Create a user with a referral code
        $user = User::factory()->create([
            'referral_code' => 'TEST123',
            'username' => 'testuser',
        ]);

        // Create another user and authenticate them
        $authenticatedUser = User::factory()->create();
        Sanctum::actingAs($authenticatedUser);

        // Make request to get username by referral code
        $response = $this->postJson('/api/v1/user/username-by-referral', [
            'referral_code' => 'TEST123',
        ]);

        // Assert successful response
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'username' => 'testuser',
                    'referral_code' => 'TEST123',
                ],
                'message' => 'Username retrieved successfully',
            ]);
    }

    /** @test */
    public function it_returns_error_for_invalid_referral_code()
    {
        // Create a user and authenticate them
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Make request with invalid referral code
        $response = $this->postJson('/api/v1/user/username-by-referral', [
            'referral_code' => 'INVALID',
        ]);

        // Assert validation error
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['referral_code']);
    }

    /** @test */
    public function it_requires_authentication()
    {
        // Make request without authentication
        $response = $this->postJson('/api/v1/user/username-by-referral', [
            'referral_code' => 'TEST123',
        ]);

        // Assert unauthorized response
        $response->assertStatus(401);
    }

    /** @test */
    public function it_requires_referral_code_parameter()
    {
        // Create a user and authenticate them
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Make request without referral_code parameter
        $response = $this->postJson('/api/v1/user/username-by-referral', []);

        // Assert validation error
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['referral_code']);
    }
}