<?php

namespace Database\Factories;

use App\Models\AccessKey;
use App\Models\UserPackage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class AccessKeyFactory extends Factory
{
    protected $model = AccessKey::class;

    public function definition(): array
    {
        // Create an admin user if one doesn't exist
        $adminUser = User::where('is_admin', true)->first() ?? User::factory()->admin()->create();
        
        return [
            'key' => strtoupper(Str::random(16)),
            'package_id' => UserPackage::factory(),
            'created_by' => $adminUser->id,
            'is_used' => false,
            'used_by' => null,
            'used_at' => null,
            'is_active' => true,
            'expires_at' => null,
        ];
    }

    public function used(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_used' => true,
            'used_by' => User::factory(),
            'used_at' => now(),
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
            'expires_at' => now()->subDay(),
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}