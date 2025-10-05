<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\UserPackage;

class PackageWelcomeBonusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Update existing packages with welcome bonus values based on their price
        UserPackage::where('price', 0)->update(['welcome_bonus' => 100]); // Free packages get 100
        UserPackage::where('price', '>', 0)->where('price', '<=', 10)->update(['welcome_bonus' => 250]); // Basic packages get 250
        UserPackage::where('price', '>', 10)->where('price', '<=', 30)->update(['welcome_bonus' => 500]); // Mid-tier packages get 500
        UserPackage::where('price', '>', 30)->update(['welcome_bonus' => 1000]); // Premium packages get 1000
    }
}