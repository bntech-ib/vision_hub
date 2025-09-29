<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\SupportOption;

class SupportOptionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_generates_whatsapp_link_correctly()
    {
        $supportOption = SupportOption::create([
            'title' => 'Test Support',
            'description' => 'Test Description',
            'whatsapp_number' => '+1234567890',
            'whatsapp_message' => 'Test message',
            'is_active' => true,
        ]);

        $this->assertEquals(
            'https://wa.me/1234567890?text=Test%20message',
            $supportOption->whatsapp_link
        );
    }

    /** @test */
    public function it_returns_null_when_missing_whatsapp_data()
    {
        $supportOption = SupportOption::create([
            'title' => 'Test Support',
            'description' => 'Test Description',
            'is_active' => true,
        ]);

        $this->assertNull($supportOption->whatsapp_link);
    }
}