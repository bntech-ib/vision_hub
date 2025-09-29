<?php

namespace App\Console\Commands;

use App\Models\BrainTeaser;
use App\Models\User;
use Illuminate\Console\Command;

class TestBrainTeaserCreation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-brain-teaser-creation';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test brain teaser creation with created_by field';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Get the first admin user
        $admin = User::where('is_admin', true)->first();

        if (!$admin) {
            $this->error('No admin user found');
            return 1;
        }

        // Create a brain teaser
        $brainTeaser = BrainTeaser::create([
            'title' => 'Test Brain Teaser',
            'question' => 'What is VisionHub?',
            'options' => json_encode(['online game', 'finance app', 'online ads', 'online earning app']),
            'correct_answer' => 'online earning app',
            'explanation' => 'VisionHub is an online earning app',
            'category' => 'other',
            'difficulty' => 'medium',
            'reward_amount' => 10,
            'status' => 'active',
            'is_daily' => true,
            'created_by' => $admin->id
        ]);

        $this->info('Brain teaser created successfully with ID: ' . $brainTeaser->id);
        return 0;
    }
}