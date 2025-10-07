<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\UserPackage; // Changed from Package to UserPackage

class AvailablePackagesTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_retrieve_available_packages()
    {
        // Create some test packages
        UserPackage::factory()->count(3)->create([
            'is_active' => true
        ]);

        // Create an inactive package to ensure it's not included
        UserPackage::factory()->create([
            'is_active' => false
        ]);

        // Make request to available packages endpoint
        $response = $this->getJson('/api/v1/packages/available');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Available packages retrieved successfully'
        ]);

        // Should return 3 active packages
        $response->assertJsonCount(3, 'data');

        // All returned packages should be active
        $responseData = $response->json();
        foreach ($responseData['data'] as $package) {
            $this->assertTrue($package['is_active']);
        }
    }

    /** @test */
    public function it_returns_empty_array_when_no_active_packages_exist()
    {
        // Create an inactive package
        UserPackage::factory()->create([
            'is_active' => false
        ]);

        // Make request to available packages endpoint
        $response = $this->getJson('/api/v1/packages/available');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Available packages retrieved successfully',
            'data' => []
        ]);
    }

    /** @test */
    public function it_orders_packages_by_price()
    {
        // Create packages with different prices
        UserPackage::factory()->create([
            'name' => 'Premium Package',
            'price' => 99.99,
            'is_active' => true
        ]);

        UserPackage::factory()->create([
            'name' => 'Basic Package',
            'price' => 9.99,
            'is_active' => true
        ]);

        UserPackage::factory()->create([
            'name' => 'Standard Package',
            'price' => 29.99,
            'is_active' => true
        ]);

        // Make request to available packages endpoint
        $response = $this->getJson('/api/v1/packages/available');

        $response->assertStatus(200);
        
        // Check that packages are ordered by price (ascending)
        $responseData = $response->json();
        $prices = array_column($responseData['data'], 'price');
        $sortedPrices = $prices;
        sort($sortedPrices);
        
        $this->assertEquals($sortedPrices, $prices);
    }
}