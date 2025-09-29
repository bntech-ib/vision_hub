<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\SupportOption;

class SupportOptionControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_call_accessor_in_controller_context()
    {
        // Create a support option with whatsapp data
        $supportOption = SupportOption::create([
            'title' => 'Test Support',
            'description' => 'Test Description',
            'whatsapp_number' => '+1234567890',
            'whatsapp_message' => 'Test message',
            'is_active' => true,
        ]);

        // Test that the accessor works directly
        $this->assertEquals(
            'https://wa.me/1234567890?text=Test%20message',
            $supportOption->whatsapp_link
        );

        // Test that we can access it in an array context
        $data = [
            'id' => $supportOption->id,
            'title' => $supportOption->title,
            'description' => $supportOption->description,
            'icon' => $supportOption->icon,
            'whatsapp_link' => $supportOption->whatsapp_link,
        ];

        $this->assertArrayHasKey('whatsapp_link', $data);
        $this->assertEquals(
            'https://wa.me/1234567890?text=Test%20message',
            $data['whatsapp_link']
        );
    }
}