<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class PackageCreationTest extends TestCase
{
    use RefreshDatabase;

    private $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Create an admin user
        $this->admin = User::factory()->create([
            'is_admin' => true,
        ]);
    }

    /**
     * Test package creation to identify the error
     */
    public function test_package_creation_error()
    {
        $packageData = [
            'name' => 'Test Package',
            'description' => 'A test package',
            'price' => 10.00,
            'duration_days' => 30,
            'features' => '["Feature 1", "Feature 2"]',
            'ad_views_limit' => 100,
            'daily_earning_limit' => 5.00,
            'ad_limits' => 50,
            'course_access_limit' => 5,
            'marketplace_access' => true,
            'brain_teaser_access' => true,
            'is_active' => true,
            'referral_earning_percentage' => 2.50,
            'welcome_bonus' => 100.00
        ];

        // First, let's try to create a package and see what error we get
        $response = $this->actingAs($this->admin)->postJson('/admin/packages', $packageData);

        // Check if the response is successful
        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
            'message' => 'Package created successfully'
        ]);
        
        // Check if the package was actually created in the database
        $this->assertDatabaseHas('user_packages', [
            'name' => 'Test Package',
            'price' => 10.00,
            'duration_days' => 30
        ]);
    }
    
    /**
     * Test package creation with minimal data
     */
    public function test_package_creation_with_minimal_data()
    {
        $packageData = [
            'name' => 'Basic Package',
            'price' => 5.00,
            'duration_days' => 30
        ];

        $response = $this->actingAs($this->admin)->postJson('/admin/packages', $packageData);

        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
            'message' => 'Package created successfully'
        ]);
        
        $this->assertDatabaseHas('user_packages', [
            'name' => 'Basic Package',
            'price' => 5.00
        ]);
    }
}