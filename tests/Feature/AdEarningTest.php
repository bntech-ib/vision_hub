<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Advertisement;
use App\Models\UserPackage;
use App\Models\AdInteraction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class AdEarningTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $user;
    protected UserPackage $package;
    protected Advertisement $ad;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a test package with earning limits
        $this->package = UserPackage::factory()->create([
            'name' => 'Test Package',
            'daily_earning_limit' => 10.00,
            'ad_limits' => 20,
            'price' => 0,
        ]);

        // Create a test user with the package
        $this->user = User::factory()->create([
            'current_package_id' => $this->package->id,
            'wallet_balance' => 0,
        ]);

        // Create a test advertisement
        $this->ad = Advertisement::factory()->create([
            'title' => 'Test Ad',
            'status' => 'active',
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);
    }

    /**
     * Test that user earns reward for viewing an ad
     *
     * @return void
     */
    public function test_user_earns_reward_for_viewing_ad()
    {
        // Calculate expected reward: daily_earning_limit / ad_limits
        $expectedReward = $this->package->daily_earning_limit / $this->package->ad_limits;

        // Get initial wallet balance
        $initialBalance = $this->user->wallet_balance;

        // Act: User views an ad
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/ads/{$this->ad->id}/interact", [
                'type' => 'view'
            ]);

        // Assert: Response is successful
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'interaction' => [
                        'type' => 'view',
                        'reward_earned' => $expectedReward,
                    ]
                ]
            ]);

        // Assert: User's wallet balance increased by the reward amount
        $this->user->refresh();
        $this->assertEquals($initialBalance + $expectedReward, $this->user->wallet_balance);

        // Assert: Ad interaction record was created
        $this->assertDatabaseHas('ad_interactions', [
            'user_id' => $this->user->id,
            'advertisement_id' => $this->ad->id,
            'type' => 'view',
            'reward_earned' => $expectedReward,
        ]);

        // Assert: Transaction record was created
        $this->assertDatabaseHas('transactions', [
            'user_id' => $this->user->id,
            'amount' => $expectedReward,
            'type' => 'earning',
            'description' => "Reward for viewing advertisement #{$this->ad->id}",
            'status' => 'completed',
        ]);
    }

    /**
     * Test that user earns 2x reward for clicking an ad
     *
     * @return void
     */
    public function test_user_earns_double_reward_for_clicking_ad()
    {
        // Calculate expected reward: daily_earning_limit / ad_limits
        $viewReward = $this->package->daily_earning_limit / $this->package->ad_limits;
        $clickReward = $viewReward * 2;

        // Get initial wallet balance
        $initialBalance = $this->user->wallet_balance;

        // Act: User clicks an ad
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/ads/{$this->ad->id}/interact", [
                'type' => 'click'
            ]);

        // Assert: Response is successful
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'interaction' => [
                        'type' => 'click',
                        'reward_earned' => $clickReward,
                    ]
                ]
            ]);

        // Assert: User's wallet balance increased by the click reward amount
        $this->user->refresh();
        $this->assertEquals($initialBalance + $clickReward, $this->user->wallet_balance);

        // Assert: Ad interaction record was created
        $this->assertDatabaseHas('ad_interactions', [
            'user_id' => $this->user->id,
            'advertisement_id' => $this->ad->id,
            'type' => 'click',
            'reward_earned' => $clickReward,
        ]);

        // Assert: Transaction record was created
        $this->assertDatabaseHas('transactions', [
            'user_id' => $this->user->id,
            'amount' => $clickReward,
            'type' => 'earning',
            'description' => "Reward for clicking advertisement #{$this->ad->id}",
            'status' => 'completed',
        ]);
    }

    /**
     * Test that user cannot exceed daily ad interaction limit
     *
     * @return void
     */
    public function test_user_cannot_exceed_daily_ad_interaction_limit()
    {
        // Create maximum number of interactions for the day
        for ($i = 0; $i < $this->package->ad_limits; $i++) {
            AdInteraction::factory()->create([
                'user_id' => $this->user->id,
                'advertisement_id' => $this->ad->id,
                'type' => 'view',
                'interacted_at' => now(),
            ]);
        }

        // Act: Try to view another ad when limit is reached
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/ads/{$this->ad->id}/interact", [
                'type' => 'view'
            ]);

        // Assert: Response shows limit reached
        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'You have reached your daily ad interaction limit based on your package.'
            ]);
    }

    /**
     * Test ad statistics endpoint
     *
     * @return void
     */
    public function test_get_ad_statistics()
    {
        // Create some interactions
        AdInteraction::factory()->create([
            'user_id' => $this->user->id,
            'advertisement_id' => $this->ad->id,
            'type' => 'view',
            'interacted_at' => now(),
        ]);

        AdInteraction::factory()->create([
            'user_id' => $this->user->id,
            'advertisement_id' => $this->ad->id,
            'type' => 'click',
            'interacted_at' => now(),
        ]);

        // Act: Get ad statistics
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/ads/stats");

        // Assert: Response is successful
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'today_views' => 1,
                    'today_clicks' => 1,
                    'daily_limit' => $this->package->ad_limits,
                ]
            ]);
    }
}