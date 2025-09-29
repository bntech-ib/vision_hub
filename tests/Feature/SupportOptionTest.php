<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\SupportOption;
use Illuminate\Support\Facades\Log;

class SupportOptionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_retrieve_public_support_options()
    {
        // Create some support options
        SupportOption::factory()->count(3)->create(['is_active' => true]);
        SupportOption::factory()->create(['is_active' => false]); // Inactive option

        $response = $this->getJson('/api/v1/support-options');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Support options retrieved successfully'
            ]);
            
        // Print the response for debugging
        $responseData = $response->json();
        \Log::info('Support options response:', $responseData);
        
        // Check that we have the data key
        $this->assertArrayHasKey('data', $responseData);
        
        // Check that we have the right number of items
        $this->assertCount(3, $responseData['data']);
        
        // Check the first item structure
        $firstItem = $responseData['data'][0];
        $this->assertArrayHasKey('id', $firstItem);
        $this->assertArrayHasKey('title', $firstItem);
        $this->assertArrayHasKey('description', $firstItem);
        $this->assertArrayHasKey('icon', $firstItem);
        
        // This is the failing assertion - let's see what's actually there
        $this->assertArrayHasKey('whatsapp_link', $firstItem, 'Response: ' . json_encode($responseData));

        // Should only return active support options
        $this->assertCount(3, $responseData['data']);
    }

    /** @test */
    public function it_can_create_a_support_option()
    {
        $response = $this->postJson('/api/v1/support-options', [
            'title' => 'Test Support',
            'description' => 'Test Description',
            'icon' => 'test-icon',
            'whatsapp_number' => '+1234567890',
            'whatsapp_message' => 'Test message',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Support option created successfully'
            ]);

        $this->assertDatabaseHas('support_options', [
            'title' => 'Test Support',
            'description' => 'Test Description',
        ]);
    }

    /** @test */
    public function it_can_update_a_support_option()
    {
        $supportOption = SupportOption::factory()->create();

        $response = $this->putJson("/api/v1/support-options/{$supportOption->id}", [
            'title' => 'Updated Title',
            'description' => 'Updated Description',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Support option updated successfully'
            ]);

        $this->assertDatabaseHas('support_options', [
            'id' => $supportOption->id,
            'title' => 'Updated Title',
            'description' => 'Updated Description',
        ]);
    }

    /** @test */
    public function it_can_delete_a_support_option()
    {
        $supportOption = SupportOption::factory()->create();

        $response = $this->deleteJson("/api/v1/support-options/{$supportOption->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Support option deleted successfully'
            ]);

        $this->assertDatabaseMissing('support_options', [
            'id' => $supportOption->id,
        ]);
    }
}