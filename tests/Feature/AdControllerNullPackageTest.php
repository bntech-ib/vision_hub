<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Advertisement;

class AdControllerNullPackageTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a test user without a package
        $this->user = User::factory()->create([
            'current_package_id' => null,
            'package_expires_at' => null
        ]);
    }

    /** @test */
    public function user_without_package_can_retrieve_advertisements()
    {
        $this->actingAs($this->user);

        // Create some test advertisements
        Advertisement::factory()->count(3)->create([
            'status' => 'active',
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay()
        ]);

        // Try to retrieve advertisements
        $response = $this->getJson('/api/v1/ads');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Advertisements retrieved successfully'
        ]);
        
        // Should return the advertisements
        $response->assertJsonStructure([
            'data' => [
                'ads' => [
                    '*' => ['id', 'title', 'description', 'imageUrl', 'targetUrl', 'category', 'rewardAmount', 'startDate', 'endDate', 'status', 'createdAt']
                ],
                'meta' => [
                    'pagination' => ['total', 'count', 'per_page', 'current_page', 'total_pages']
                ]
            ]
        ]);
    }

    /** @test */
    public function user_without_package_can_get_ad_statistics()
    {
        $this->actingAs($this->user);

        // Try to get ad statistics
        $response = $this->getJson('/api/v1/ads/stats');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Ad statistics retrieved successfully'
        ]);
        
        // Should return statistics with 0 values and no errors
        $response->assertJsonStructure([
            'data' => ['today_views', 'today_clicks', 'daily_limit', 'remaining_interactions', 'has_reached_limit']
        ]);
    }
}