<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\UserPackage;

class UserVendorConversionTest extends TestCase
{
    use RefreshDatabase;

    private $admin;
    private $user;
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

        // Create a package
        $this->package = UserPackage::factory()->create([
            'name' => 'Test Package',
            'price' => 10.00,
        ]);
    }

    /**
     * Test that the vendor section appears in the user edit form
     */
    public function test_vendor_section_appears_in_user_edit_form()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.users.edit', $this->user));

        $response->assertStatus(200);
        $response->assertSee('Vendor Information');
        $response->assertSee('Make this user a vendor');
    }

    /**
     * Test that admin can convert a user to a vendor through the user edit form
     */
    public function test_admin_can_convert_user_to_vendor_through_user_edit_form()
    {
        $vendorData = [
            'name' => $this->user->name,
            'email' => $this->user->email,
            'make_vendor' => true,
            'vendor_company_name' => 'Test Vendor Company',
            'vendor_description' => 'A great vendor',
            'vendor_website' => 'https://testvendor.com',
            'vendor_commission_rate' => 15.5,
        ];

        $response = $this->actingAs($this->admin)->put(route('admin.users.update', $this->user), $vendorData);

        $response->assertRedirect(route('admin.users.show', $this->user));
        $response->assertSessionHas('success', 'User updated successfully.');

        // Check that user is now a vendor
        $this->user->refresh();
        $this->assertTrue($this->user->isVendor());
        $this->assertEquals('Test Vendor Company', $this->user->vendor_company_name);
        $this->assertEquals(15.5, $this->user->vendor_commission_rate);
    }

    /**
     * Test that vendor information is displayed for vendor users
     */
    public function test_vendor_information_is_displayed_for_vendor_users()
    {
        // Make the user a vendor
        $this->user->makeVendor([
            'vendor_company_name' => 'Existing Vendor Company',
            'vendor_description' => 'An existing vendor',
            'vendor_website' => 'https://existingvendor.com',
            'vendor_commission_rate' => 20.0,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.users.edit', $this->user));

        $response->assertStatus(200);
        $response->assertSee('This user is already a vendor');
        $response->assertSee('Existing Vendor Company');
        $response->assertSee('20');
    }

    /**
     * Test that admin can update vendor information through the user edit form
     */
    public function test_admin_can_update_vendor_information_through_user_edit_form()
    {
        // Make the user a vendor
        $this->user->makeVendor([
            'vendor_company_name' => 'Original Company',
            'vendor_description' => 'Original description',
            'vendor_website' => 'https://original.com',
            'vendor_commission_rate' => 10.0,
        ]);

        $updatedData = [
            'name' => $this->user->name,
            'email' => $this->user->email,
            'vendor_company_name' => 'Updated Company Name',
            'vendor_description' => 'Updated description',
            'vendor_website' => 'https://updated.com',
            'vendor_commission_rate' => 25.5,
        ];

        $response = $this->actingAs($this->admin)->put(route('admin.users.update', $this->user), $updatedData);

        $response->assertRedirect(route('admin.users.show', $this->user));
        $response->assertSessionHas('success', 'User updated successfully.');

        // Check that vendor information was updated
        $this->user->refresh();
        $this->assertTrue($this->user->isVendor());
        $this->assertEquals('Updated Company Name', $this->user->vendor_company_name);
        $this->assertEquals('Updated description', $this->user->vendor_description);
        $this->assertEquals('https://updated.com', $this->user->vendor_website);
        $this->assertEquals(25.5, $this->user->vendor_commission_rate);
    }
}