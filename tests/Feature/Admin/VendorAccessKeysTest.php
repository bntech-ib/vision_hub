<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\UserPackage;
use App\Models\AccessKey;
use App\Models\VendorAccessKey;
use Illuminate\Support\Str;

class VendorAccessKeysTest extends TestCase
{
    use RefreshDatabase;

    private $admin;
    private $vendor;
    private $package;

    protected function setUp(): void
    {
        parent::setUp();

        // Create an admin user
        $this->admin = User::factory()->create([
            'is_admin' => true,
        ]);

        // Create a vendor user
        $this->vendor = User::factory()->create([
            'is_vendor' => true,
            'vendor_company_name' => 'Test Company',
            'vendor_description' => 'Test Description',
            'vendor_website' => 'https://testcompany.com',
            'vendor_commission_rate' => 15.5,
        ]);

        // Create a package
        $this->package = UserPackage::factory()->create([
            'name' => 'Test Package',
            'price' => 10.00,
        ]);
    }

    /**
     * Test that admin can view vendor access keys
     */
    public function test_admin_can_view_vendor_access_keys()
    {
        // Create some vendor access keys
        for ($i = 0; $i < 3; $i++) {
            $accessKey = AccessKey::factory()->create([
                'package_id' => $this->package->id,
                'key' => strtoupper(Str::random(16)),
            ]);

            VendorAccessKey::create([
                'vendor_id' => $this->vendor->id,
                'access_key_id' => $accessKey->id,
                'commission_rate' => 15.5,
            ]);
        }

        $response = $this->actingAs($this->admin)->get(route('admin.vendors.access-keys', $this->vendor));

        $response->assertStatus(200);
        $response->assertViewIs('admin.vendors.access-keys');
        $response->assertSee('Vendor Access Keys');
        $response->assertSee($this->vendor->vendor_company_name);
    }

    /**
     * Test that admin can filter vendor access keys by status
     */
    public function test_admin_can_filter_vendor_access_keys_by_status()
    {
        // Create an unsold vendor access key
        $unsoldAccessKey = AccessKey::factory()->create([
            'package_id' => $this->package->id,
            'key' => strtoupper(Str::random(16)),
        ]);

        VendorAccessKey::create([
            'vendor_id' => $this->vendor->id,
            'access_key_id' => $unsoldAccessKey->id,
            'commission_rate' => 15.5,
            'is_sold' => false,
        ]);

        // Create a sold vendor access key
        $soldAccessKey = AccessKey::factory()->create([
            'package_id' => $this->package->id,
            'key' => strtoupper(Str::random(16)),
        ]);

        $buyer = User::factory()->create();

        VendorAccessKey::create([
            'vendor_id' => $this->vendor->id,
            'access_key_id' => $soldAccessKey->id,
            'commission_rate' => 15.5,
            'is_sold' => true,
            'sold_at' => now(),
            'buyer_id' => $buyer->id,
        ]);

        // Test unsold filter
        $response = $this->actingAs($this->admin)->get(route('admin.vendors.access-keys', $this->vendor) . '?status=unsold');
        $response->assertStatus(200);
        $response->assertSee($unsoldAccessKey->key);
        $response->assertDontSee($soldAccessKey->key);

        // Test sold filter
        $response = $this->actingAs($this->admin)->get(route('admin.vendors.access-keys', $this->vendor) . '?status=sold');
        $response->assertStatus(200);
        $response->assertSee($soldAccessKey->key);
        $response->assertDontSee($unsoldAccessKey->key);
    }
}