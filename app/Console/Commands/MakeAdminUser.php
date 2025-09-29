<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class MakeAdminUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:admin {email?} {--name=} {--password=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create an admin user or make existing user an admin';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email') ?: $this->ask('Enter admin email');
        $user = User::where('email', $email)->first();
        
        if ($user) {
            // Make existing user admin
            $user->update(['is_admin' => true]);
            $this->info("User {$email} is now an admin.");
        } else {
            // Create new admin user
            $name = $this->option('name') ?: $this->ask('Enter admin name');
            $password = $this->option('password') ?: $this->secret('Enter admin password');
            
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
                'is_admin' => true,
                'email_verified_at' => now(),
            ]);
            
            $this->info("Admin user created successfully!");
            $this->info("Email: {$email}");
            $this->info("Password: {$password}");
        }
        
        return 0;
    }
}
