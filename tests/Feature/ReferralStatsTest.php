<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\UserPackage;

class ReferralStatsTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /** @test */
    public function user_can_get_their_referral_statistics()
    {
        // Create a package
        $package = UserPackage::factory()->create([
            'name' => 'Premium Package',
            'is_active' => true
        ]);

        // Create a user with referrals
        $user = User::factory()->create([
            'current_package_id' => $package->id
        ]);

        // Create referral users
        $referral1 = User::factory()->create([
            'referred_by' => $user->id,
            'current_package_id' => $package->id,
            'username' => 'referral1'
        ]);

        $referral2 = User::factory()->create([
            'referred_by' => $user->id,
            'current_package_id' => $package->id,
            'username' => 'referral2'
        ]);

        $response = $this->actingAs($user)->getJson('/api/v1/dashboard/referral-stats');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'total_referrals' => 2
            ],
            'message' => 'Referral statistics retrieved successfully'
        ]);

        // Check that the referrals data includes the expected fields
        $responseData = $response->json('data');
        $this->assertCount(2, $responseData['referrals']);

        // Check first referral data
        $this->assertEquals($referral1->id, $responseData['referrals'][0]['id']);
        $this->assertEquals('referral1', $responseData['referrals'][0]['username']);
        $this->assertEquals('Premium Package', $responseData['referrals'][0]['package_name']);
        $this->assertArrayHasKey('registered_at', $responseData['referrals'][0]);

        // Check second referral data
        $this->assertEquals($referral2->id, $responseData['referrals'][1]['id']);
        $this->assertEquals('referral2', $responseData['referrals'][1]['username']);
        $this->assertEquals('Premium Package', $responseData['referrals'][1]['package_name']);
        $this->assertArrayHasKey('registered_at', $responseData['referrals'][1]);
    }

    /** @test */
    public function user_with_no_referrals_gets_empty_list()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/v1/dashboard/referral-stats');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'total_referrals' => 0,
                'referrals' => []
            ],
            'message' => 'Referral statistics retrieved successfully'
        ]);
    }

    /** @test */
    public function unauthenticated_user_cannot_access_referral_stats()
    {
        $response = $this->getJson('/api/v1/dashboard/referral-stats');

        $response->assertStatus(401);
    }

    /** @test */
    public function referral_with_no_package_shows_no_package()
    {
        // Create a user
        $user = User::factory()->create();

        // Create referral user without package
        $referral = User::factory()->create([
            'referred_by' => $user->id,
            'username' => 'referral_no_package'
        ]);

        $response = $this->actingAs($user)->getJson('/api/v1/dashboard/referral-stats');

        $response->assertStatus(200);
        $responseData = $response->json('data');

        $this->assertEquals(1, $responseData['total_referrals']);
        $this->assertEquals('referral_no_package', $responseData['referrals'][0]['username']);
        $this->assertEquals('No Package', $responseData['referrals'][0]['package_name']);
    }
}