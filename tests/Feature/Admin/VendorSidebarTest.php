<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class VendorSidebarTest extends TestCase
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
     * Test that the vendor link appears in the admin sidebar
     */
    public function test_vendor_link_appears_in_admin_sidebar()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Vendors');
        $response->assertSee(route('admin.vendors.index'));
        $response->assertSee('bi-shop'); // Bootstrap icon class
    }
}