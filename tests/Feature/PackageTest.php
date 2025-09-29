<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Package;

class PackageTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_fetch_available_packages()
    {
        // Create some packages
        Package::factory()->create([
            'name' => 'Basic Plan',
            'price' => 9.99,
            'is_active' => true
        ]);
        
        Package::factory()->create([
            'name' => 'Premium Plan',
            'price' => 19.99,
            'is_active' => true
        ]);
        
        Package::factory()->create([
            'name' => 'Inactive Plan',
            'price' => 29.99,
            'is_active' => false
        ]);

        $response = $this->getJson('/api/v1/packages/available');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Available packages retrieved successfully'
            ]);
            
        $responseData = $response->json();
        
        // Should only return active packages
        $this->assertCount(2, $responseData['data']);
        
        // Should be ordered by price
        $this->assertEquals('Basic Plan', $responseData['data'][0]['name']);
        $this->assertEquals('Premium Plan', $responseData['data'][1]['name']);
        
        // Should not include inactive packages
        $names = collect($responseData['data'])->pluck('name')->toArray();
        $this->assertNotContains('Inactive Plan', $names);
    }

    /** @test */
    public function it_returns_empty_array_when_no_packages_are_available()
    {
        // Create an inactive package
        Package::factory()->create([
            'name' => 'Inactive Plan',
            'is_active' => false
        ]);

        $response = $this->getJson('/api/v1/packages/available');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => []
            ]);
    }
    
    /** @test */
    public function it_only_returns_active_packages()
    {
        // Create active and inactive packages
        $activePackage = Package::factory()->create(['is_active' => true]);
        $inactivePackage = Package::factory()->create(['is_active' => false]);

        $response = $this->getJson('/api/v1/packages/available');

        $response->assertStatus(200);
        $data = $response->json('data');
        
        // Should only contain the active package
        $this->assertCount(1, $data);
        $this->assertEquals($activePackage->name, $data[0]['name']);
        $this->assertNotEquals($inactivePackage->name, $data[0]['name']);
    }
}