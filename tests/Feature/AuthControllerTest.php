<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\AccessKey;
use App\Models\UserPackage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $user;
    protected UserPackage $package;
    protected AccessKey $accessKey;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a test package
        $this->package = UserPackage::factory()->create([
            'name' => 'Test Package',
            'price' => 0,
            'duration_days' => 30,
            'features' => ['feature1', 'feature2'],
            'is_active' => true
        ]);

        // Create a test user
        $this->user = User::factory()->create([
            'name' => 'Test User',
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'current_package_id' => $this->package->id,
        ]);

        // Create an access key
        $this->accessKey = AccessKey::factory()->create([
            'key' => 'TEST123KEY',
            'package_id' => $this->package->id,
            'created_by' => $this->user->id,
            'is_active' => true,
            'is_used' => false,
        ]);
    }

    /** @test */
    public function it_can_login_with_email_and_password()
    {
        $response = $this->postJson('/api/v1/auth/login/email', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'user' => [
                        'id',
                        'username',
                        'email',
                        'fullName',
                        'phone',
                        'country',
                        'package',
                        'referralCode',
                        'createdAt',
                        'updatedAt',
                    ],
                    'token',
                ],
                'message',
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Login successful',
                'data' => [
                    'user' => [
                        'email' => 'test@example.com',
                        'username' => 'testuser',
                        'fullName' => 'Test User',
                    ],
                ],
            ]);

        // Ensure the token is present
        $this->assertArrayHasKey('token', $response->json('data'));
        $this->assertNotEmpty($response->json('data.token'));
    }

    /** @test */
    public function it_fails_to_login_with_invalid_credentials()
    {
        $response = $this->postJson('/api/v1/auth/login/email', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => [
                    'email',
                ],
            ]);
    }

    /** @test */
    public function it_requires_email_and_password()
    {
        $response = $this->postJson('/api/v1/auth/login/email', []);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'The email field is required. (and 1 more error)',
            ])
            ->assertJsonStructure([
                'message',
                'errors' => [
                    'email',
                    'password',
                ],
            ]);
    }

    /** @test */
    public function it_requires_valid_email_format()
    {
        $response = $this->postJson('/api/v1/auth/login/email', [
            'email' => 'invalid-email',
            'password' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'The email field must be a valid email address.',
            ])
            ->assertJsonStructure([
                'message',
                'errors' => [
                    'email',
                ],
            ]);
    }
}