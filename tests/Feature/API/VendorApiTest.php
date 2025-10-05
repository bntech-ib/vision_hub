<?php

namespace Tests\Feature\API;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\UserPackage;
use App\Models\AccessKey;
use App\Models\VendorAccessKey;
use Laravel\Sanctum\Sanctum;
use Illuminate\Support\Str;

class VendorApiTest extends TestCase
{
    use RefreshDatabase;

    private $vendor;
    private $package;

    protected function setUp(): void
    {
        parent::setUp();

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
     * Test that vendors can retrieve their access keys
     */
    public function test_vendors_can_retrieve_their_access_keys()
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

        Sanctum::actingAs($this->vendor);

        $response = $this->getJson('/api/v1/vendor/access-keys');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Vendor access keys retrieved successfully'
        ]);
        
        $response->assertJsonStructure([
            'success',
            'data' => [
                'data' => [
                    '*' => [
                        'id',
                        'vendor_id',
                        'access_key_id',
                        'commission_rate',
                        'is_sold',
                        'access_key' => [
                            'id',
                            'key',
                            'package_id',
                            'package' => [
                                'id',
                                'name',
                                'price'
                            ]
                        ]
                    ]
                ]
            ],
            'message'
        ]);
    }

    /**
     * Test that vendors can retrieve their access keys with buyer information
     */
    public function test_vendors_can_retrieve_their_access_keys_with_buyer_information()
    {
        // Create a buyer user
        $buyer = User::factory()->create([
            'name' => 'Test Buyer',
            'email' => 'buyer@example.com'
        ]);

        // Create an access key
        $accessKey = AccessKey::factory()->create([
            'package_id' => $this->package->id,
            'key' => strtoupper(Str::random(16)),
        ]);

        // Create a sold vendor access key
        $vendorAccessKey = VendorAccessKey::create([
            'vendor_id' => $this->vendor->id,
            'access_key_id' => $accessKey->id,
            'commission_rate' => 15.5,
            'is_sold' => true,
            'sold_to_user_id' => $buyer->id,
            'sold_at' => now(),
        ]);

        Sanctum::actingAs($this->vendor);

        $response = $this->getJson('/api/v1/vendor/access-keys');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Vendor access keys retrieved successfully'
        ]);
        
        // Check that buyer information is included
        $responseData = $response->json();
        $this->assertEquals($buyer->id, $responseData['data']['data'][0]['sold_to_user_id']);
        $this->assertEquals($buyer->name, $responseData['data']['data'][0]['buyer']['name']);
        $this->assertEquals($buyer->email, $responseData['data']['data'][0]['buyer']['email']);
    }

    /**
     * Test that non-vendors cannot access vendor endpoints
     */
    public function test_non_vendors_cannot_access_vendor_endpoints()
    {
        $user = User::factory()->create([
            'is_vendor' => false,
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/vendor/access-keys');

        $response->assertStatus(403);
        $response->assertJson([
            'success' => false,
            'message' => 'Unauthorized. Only vendors can access this endpoint.'
        ]);
    }

    /**
     * Test that vendors can filter access keys by status
     */
    public function test_vendors_can_filter_access_keys_by_status()
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
            'sold_to_user_id' => $buyer->id,
            'sold_at' => now(),
        ]);

        Sanctum::actingAs($this->vendor);

        // Test unsold filter
        $response = $this->getJson('/api/v1/vendor/access-keys?status=unsold');
        $response->assertStatus(200);
        $responseData = $response->json();
        
        // Should only have 1 item and it should be unsold
        $this->assertEquals(1, count($responseData['data']['data']));
        $this->assertFalse($responseData['data']['data'][0]['is_sold']);

        // Test sold filter
        $response = $this->getJson('/api/v1/vendor/access-keys?status=sold');
        $response->assertStatus(200);
        $responseData = $response->json();
        
        // Should only have 1 item and it should be sold
        $this->assertEquals(1, count($responseData['data']['data']));
        $this->assertTrue($responseData['data']['data'][0]['is_sold']);
    }

    /**
     * Test that vendors can get their statistics
     */
    public function test_vendors_can_get_their_statistics()
    {
        // Create some vendor access keys
        for ($i = 0; $i < 5; $i++) {
            $accessKey = AccessKey::factory()->create([
                'package_id' => $this->package->id,
                'key' => strtoupper(Str::random(16)),
            ]);

            // Make some of them sold
            $isSold = $i < 3; // First 3 are sold
            
            $vendorAccessKeyData = [
                'vendor_id' => $this->vendor->id,
                'access_key_id' => $accessKey->id,
                'commission_rate' => 15.5,
                'is_sold' => $isSold,
            ];
            
            if ($isSold) {
                $buyer = User::factory()->create();
                $vendorAccessKeyData['sold_to_user_id'] = $buyer->id;
                $vendorAccessKeyData['sold_at'] = now();
                $vendorAccessKeyData['earned_amount'] = $this->package->price * (15.5 / 100);
            }

            VendorAccessKey::create($vendorAccessKeyData);
        }

        Sanctum::actingAs($this->vendor);

        $response = $this->getJson('/api/v1/vendor/statistics');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Vendor statistics retrieved successfully'
        ]);
        
        $response->assertJsonStructure([
            'success',
            'data' => [
                'total_access_keys',
                'sold_access_keys',
                'total_earnings',
                'unsold_access_keys'
            ],
            'message'
        ]);
        
        $responseData = $response->json();
        $this->assertEquals(5, $responseData['data']['total_access_keys']);
        $this->assertEquals(3, $responseData['data']['sold_access_keys']);
        $this->assertEquals(2, $responseData['data']['unsold_access_keys']);
    }
}