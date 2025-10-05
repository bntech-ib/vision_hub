<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\SupportOption;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class SupportControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create an admin user for testing
        $this->adminUser = User::factory()->create([
            'is_admin' => true,
        ]);
    }

    /** @test */
    public function admin_can_view_support_options_index()
    {
        $this->actingAs($this->adminUser);
        
        $response = $this->get(route('admin.support.index'));
        
        $response->assertStatus(200);
        $response->assertViewIs('admin.support.index');
    }

    /** @test */
    public function admin_can_create_support_option_with_avatar()
    {
        Storage::fake('public');
        
        $this->actingAs($this->adminUser);
        
        $avatar = UploadedFile::fake()->image('avatar.jpg');
        
        $supportOptionData = [
            'title' => 'Test Support Option',
            'description' => 'This is a test support option',
            'avatar' => $avatar,
            'whatsapp_number' => '+1234567890',
            'whatsapp_message' => 'Hello, this is a test message',
            'sort_order' => 1,
            'is_active' => true,
        ];
        
        $response = $this->post(route('admin.support.store'), $supportOptionData);
        
        $response->assertStatus(302); // Redirect
        $response->assertSessionHas('success');
        
        $this->assertDatabaseHas('support_options', [
            'title' => 'Test Support Option',
        ]);
    }

    /** @test */
    public function admin_can_update_support_option_avatar()
    {
        Storage::fake('public');
        
        $this->actingAs($this->adminUser);
        
        $supportOption = SupportOption::factory()->create();
        
        $newAvatar = UploadedFile::fake()->image('new-avatar.jpg');
        
        $updatedData = [
            'title' => 'Updated Support Option',
            'description' => 'This is an updated support option',
            'avatar' => $newAvatar,
            'whatsapp_number' => '+0987654321',
            'whatsapp_message' => 'Hello, this is an updated message',
            'sort_order' => 2,
            'is_active' => false,
        ];
        
        $response = $this->put(route('admin.support.update', $supportOption), $updatedData);
        
        $response->assertStatus(302); // Redirect
        $response->assertSessionHas('success');
        
        $this->assertDatabaseHas('support_options', [
            'title' => 'Updated Support Option',
            'id' => $supportOption->id,
        ]);
    }

    /** @test */
    public function admin_can_delete_support_option_with_avatar()
    {
        Storage::fake('public');
        
        $this->actingAs($this->adminUser);
        
        $supportOption = SupportOption::factory()->create();
        
        $response = $this->delete(route('admin.support.destroy', $supportOption));
        
        $response->assertStatus(302); // Redirect
        $response->assertSessionHas('success');
        
        $this->assertDatabaseMissing('support_options', [
            'id' => $supportOption->id,
        ]);
    }
}