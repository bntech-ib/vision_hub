<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\AccessKey;
use App\Models\UserPackage;
use Laravel\Sanctum\Sanctum;

class PackageUpgradeTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /** @test */
    public function user_can_upgrade_package_with_valid_access_key()
    {
        // Create user with current package
        $currentPackage = UserPackage::factory()->create([
            'name' => 'Basic Package',
            'price' => 1000,
            'is_active' => true
        ]);

        $user = User::factory()->create([
            'current_package_id' => $currentPackage->id
        ]);

        // Create new package
        $newPackage = UserPackage::factory()->create([
            'name' => 'Premium Package',
            'price' => 2000,
            'is_active' => true
        ]);

        // Create access key for new package
        $accessKey = AccessKey::factory()->create([
            'package_id' => $newPackage->id,
            'is_used' => false,
            'is_active' => true
        ]);

        // Debug: Check initial state
        $this->assertFalse($accessKey->is_used);
        $this->assertNull($accessKey->used_by);

        // Authenticate the user
        Sanctum::actingAs($user);

        // Attempt to upgrade package
        $response = $this->postJson('/api/v1/user/package/upgrade', [
            'access_key' => $accessKey->key
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Package upgraded successfully.'
        ]);

        // Verify user's package was updated
        $user->refresh();
        $this->assertEquals($newPackage->id, $user->current_package_id);

        // Verify access key was marked as used
        $accessKey->refresh();
        $this->assertTrue($accessKey->is_used);
        $this->assertEquals($user->id, $accessKey->used_by);
    }

    /** @test */
    public function user_cannot_upgrade_with_invalid_access_key()
    {
        // Create user
        $user = User::factory()->create();

        // Authenticate the user
        Sanctum::actingAs($user);

        // Attempt to upgrade with invalid access key
        $response = $this->postJson('/api/v1/user/package/upgrade', [
            'access_key' => 'INVALID-KEY'
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function user_cannot_upgrade_with_used_access_key()
    {
        // Create user
        $user = User::factory()->create();

        // Create used access key
        $accessKey = AccessKey::factory()->create([
            'is_used' => true,
            'is_active' => true
        ]);

        // Authenticate the user
        Sanctum::actingAs($user);

        // Attempt to upgrade with used access key
        $response = $this->postJson('/api/v1/user/package/upgrade', [
            'access_key' => $accessKey->key
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'message' => 'The access key is invalid, expired, or has already been used.'
        ]);
    }

    /** @test */
    public function user_cannot_upgrade_with_inactive_package()
    {
        // Create user
        $user = User::factory()->create();

        // Create inactive package
        $inactivePackage = UserPackage::factory()->create([
            'is_active' => false
        ]);

        // Create access key for inactive package
        $accessKey = AccessKey::factory()->create([
            'package_id' => $inactivePackage->id,
            'is_used' => false,
            'is_active' => true
        ]);

        // Authenticate the user
        Sanctum::actingAs($user);

        // Attempt to upgrade with access key for inactive package
        $response = $this->postJson('/api/v1/user/package/upgrade', [
            'access_key' => $accessKey->key
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'message' => 'The package associated with this access key is not available.'
        ]);
    }

    /** @test */
    public function unauthenticated_user_cannot_upgrade_package()
    {
        // Attempt to upgrade without authentication
        $response = $this->postJson('/api/v1/user/package/upgrade', [
            'access_key' => 'SOME-KEY'
        ]);

        $response->assertStatus(401);
    }
}