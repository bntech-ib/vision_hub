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

class VendorManagementTest extends TestCase
{
    use RefreshDatabase;

    private $admin;
    private $user;
    private $vendor;
    private $package;

    protected function setUp(): void
    {
        parent::setUp();

        // Create an admin user
        $this->admin = User::factory()->create([
            'is_admin' => true,
        ]);

        // Create a regular user
        $this->user = User::factory()->create([
            'is_vendor' => false,
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
     * Test that admin can view the vendors list
     */
    public function test_admin_can_view_vendors_list()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.vendors.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.vendors.index');
        $response->assertSee('Vendors');
        $response->assertSee($this->vendor->vendor_company_name);
    }

    /**
     * Test that admin can view the create vendor form
     */
    public function test_admin_can_view_create_vendor_form()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.vendors.create'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.vendors.create');
        $response->assertSee('Create Vendor');
        $response->assertSee($this->user->name);
    }

    /**
     * Test that admin can convert a user to a vendor
     */
    public function test_admin_can_convert_user_to_vendor()
    {
        $vendorData = [
            'user_id' => $this->user->id,
            'vendor_company_name' => 'New Vendor Company',
            'vendor_description' => 'A great vendor',
            'vendor_website' => 'https://newvendor.com',
            'vendor_commission_rate' => 10.5,
        ];

        $response = $this->actingAs($this->admin)->postJson(route('admin.vendors.store'), $vendorData);

        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
            'message' => 'User successfully converted to vendor'
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'is_vendor' => true,
            'vendor_company_name' => 'New Vendor Company',
            'vendor_commission_rate' => 10.5,
        ]);
    }

    /**
     * Test that admin can view vendor details
     */
    public function test_admin_can_view_vendor_details()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.vendors.show', $this->vendor));

        $response->assertStatus(200);
        $response->assertViewIs('admin.vendors.show');
        $response->assertSee($this->vendor->vendor_company_name);
        $response->assertSee($this->vendor->vendor_description);
    }

    /**
     * Test that admin can view the edit vendor form
     */
    public function test_admin_can_view_edit_vendor_form()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.vendors.edit', $this->vendor));

        $response->assertStatus(200);
        $response->assertViewIs('admin.vendors.edit');
        $response->assertSee('Edit Vendor');
        $response->assertSee($this->vendor->vendor_company_name);
    }

    /**
     * Test that admin can update vendor information
     */
    public function test_admin_can_update_vendor()
    {
        $updatedData = [
            'vendor_company_name' => 'Updated Company Name',
            'vendor_description' => 'Updated Description',
            'vendor_website' => 'https://updatedcompany.com',
            'vendor_commission_rate' => 20.5,
        ];

        $response = $this->actingAs($this->admin)->putJson(route('admin.vendors.update', $this->vendor), $updatedData);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Vendor updated successfully'
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $this->vendor->id,
            'vendor_company_name' => 'Updated Company Name',
            'vendor_description' => 'Updated Description',
            'vendor_website' => 'https://updatedcompany.com',
            'vendor_commission_rate' => 20.5,
        ]);
    }

    /**
     * Test that admin can generate access keys for a vendor
     */
    public function test_admin_can_generate_access_keys_for_vendor()
    {
        $accessKeyData = [
            'package_id' => $this->package->id,
            'quantity' => 3,
            'expires_at' => now()->addDays(30)->format('Y-m-d'),
            'commission_rate' => 15.5,
        ];

        $response = $this->actingAs($this->admin)->postJson(
            route('admin.vendors.generate-access-keys', $this->vendor),
            $accessKeyData
        );

        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
            'message' => '3 access key(s) generated for vendor successfully'
        ]);

        // Check that 3 vendor access keys were created
        $this->assertDatabaseCount('vendor_access_keys', 3);
        
        // Check that 3 access keys were created
        $this->assertDatabaseCount('access_keys', 3);
        
        // Check that all access keys belong to the vendor
        $vendorAccessKeys = VendorAccessKey::all();
        foreach ($vendorAccessKeys as $vendorAccessKey) {
            $this->assertEquals($this->vendor->id, $vendorAccessKey->vendor_id);
            $this->assertEquals(15.5, $vendorAccessKey->commission_rate);
        }
    }

    /**
     * Test that admin can remove vendor status from a user
     */
    public function test_admin_can_remove_vendor_status()
    {
        $response = $this->actingAs($this->admin)->deleteJson(route('admin.vendors.destroy', $this->vendor));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Vendor status removed successfully'
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $this->vendor->id,
            'is_vendor' => false,
            'vendor_company_name' => null,
            'vendor_description' => null,
            'vendor_website' => null,
            'vendor_commission_rate' => 0,
        ]);
    }

    /**
     * Test that admin cannot remove vendor status if vendor has sold access keys
     */
    public function test_admin_cannot_remove_vendor_with_sold_access_keys()
    {
        // Create a vendor access key
        $accessKey = AccessKey::factory()->create([
            'package_id' => $this->package->id,
            'key' => strtoupper(Str::random(16)),
        ]);

        $vendorAccessKey = VendorAccessKey::create([
            'vendor_id' => $this->vendor->id,
            'access_key_id' => $accessKey->id,
            'commission_rate' => 15.5,
            'is_sold' => true,
            'sold_at' => now(),
            'buyer_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->admin)->deleteJson(route('admin.vendors.destroy', $this->vendor));

        $response->assertStatus(400);
        $response->assertJson([
            'success' => false,
            'message' => 'Cannot remove vendor with sold access keys'
        ]);

        // Verify vendor status was not removed
        $this->assertDatabaseHas('users', [
            'id' => $this->vendor->id,
            'is_vendor' => true,
        ]);
    }
}