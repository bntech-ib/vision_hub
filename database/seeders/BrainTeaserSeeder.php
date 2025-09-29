<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\BrainTeaser;
use App\Models\User;

class BrainTeaserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get admin users for brain teaser creators
        $admins = User::where('is_admin', true)->get();
        
        if ($admins->isEmpty()) {
            $this->command->info('No admin users found. Skipping brain teaser seeding.');
            return;
        }
        
        $categories = ['Logic', 'Math', 'Riddle', 'Word Play', 'Visual'];
        $difficulties = ['easy', 'medium', 'hard'];
        $statuses = ['draft', 'active', 'inactive'];
        
        $brainTeasers = [
            [
                'title' => 'The Missing Dollar',
                'question' => 'Three people check into a hotel room that costs $30. Each person pays $10. Later, the clerk realizes the room only costs $25 and gives the bellhop $5 to return to the guests. The bellhop decides to keep $2 for himself and gives each guest $1 back. Now each person paid $9, totaling $27. The bellhop has $2, totaling $29. Where is the missing dollar?',
                'options' => ['There is no missing dollar', 'The math is wrong', 'The bellhop stole it', 'The hotel kept it'],
                'correct_answer' => 'There is no missing dollar',
                'explanation' => 'The error is in adding the bellhop\'s $2 to the $27 paid by the guests. The $27 already includes the bellhop\'s $2. The correct calculation is: $25 (hotel) + $2 (bellhop) = $27 paid by guests.',
                'category' => 'Logic',
                'difficulty' => 'medium'
            ],
            [
                'title' => 'The Monty Hall Problem',
                'question' => 'You\'re on a game show with three doors. Behind one door is a car; behind the others, goats. You pick a door, say No. 1, and the host, who knows what\'s behind the doors, opens another door, say No. 3, which has a goat. He then asks if you want to pick door No. 2. Is it to your advantage to switch your choice?',
                'options' => ['Yes, switch', 'No, stay', 'It doesn\'t matter', 'Not enough information'],
                'correct_answer' => 'Yes, switch',
                'explanation' => 'Switching increases your chances from 1/3 to 2/3. When you initially pick, you have a 1/3 chance of selecting the car. After the host reveals a goat, switching gives you the 2/3 probability of winning.',
                'category' => 'Logic',
                'difficulty' => 'hard'
            ],
            [
                'title' => 'Simple Algebra',
                'question' => 'If 2x + 5 = 15, what is the value of x?',
                'options' => ['5', '10', '7.5', '2.5'],
                'correct_answer' => '5',
                'explanation' => 'Subtract 5 from both sides: 2x = 10. Divide by 2: x = 5.',
                'category' => 'Math',
                'difficulty' => 'easy'
            ],
            [
                'title' => 'Word Riddle',
                'question' => 'I speak without a mouth and hear without ears. I have no body, but come alive with wind. What am I?',
                'options' => ['Echo', 'Ghost', 'Radio', 'Whistle'],
                'correct_answer' => 'Echo',
                'explanation' => 'An echo is a sound that is reflected back, so it "speaks" without a mouth and "hears" without ears. It has no physical form but is created by sound waves bouncing off surfaces.',
                'category' => 'Riddle',
                'difficulty' => 'medium'
            ],
            [
                'title' => 'Sequence Pattern',
                'question' => 'What comes next in the sequence: 1, 1, 2, 3, 5, 8, 13, ?',
                'options' => ['21', '18', '20', '16'],
                'correct_answer' => '21',
                'explanation' => 'This is the Fibonacci sequence, where each number is the sum of the two preceding ones. 8 + 13 = 21.',
                'category' => 'Math',
                'difficulty' => 'medium'
            ],
            [
                'title' => 'Lateral Thinking',
                'question' => 'A man lives on the 20th floor of an apartment building. Every morning he takes the elevator down to the ground floor. When he comes home, he takes the elevator to the 10th floor and walks the rest of the way... except on rainy days, when he takes the elevator all the way to the 20th floor. Why?',
                'options' => ['He\'s afraid of heights', 'He\'s too short', 'He exercises', 'The elevator is broken'],
                'correct_answer' => 'He\'s too short',
                'explanation' => 'The man is too short to reach the button for the 20th floor. On rainy days, he uses his umbrella to press the button.',
                'category' => 'Logic',
                'difficulty' => 'hard'
            ]
        ];
        
        foreach ($brainTeasers as $teaserData) {
            $creator = $admins->random();
            $status = $statuses[array_rand($statuses)];
            
            BrainTeaser::create([
                'created_by' => $creator->id,
                'title' => $teaserData['title'],
                'question' => $teaserData['question'],
                'options' => $teaserData['options'],
                'correct_answer' => $teaserData['correct_answer'],
                'explanation' => $teaserData['explanation'],
                'category' => $teaserData['category'],
                'difficulty' => $teaserData['difficulty'],
                'reward_amount' => rand(1, 10) + (rand(0, 99) / 100),
                'start_date' => $status !== 'draft' ? now()->subDays(rand(1, 10)) : null,
                'end_date' => $status === 'active' ? now()->addDays(rand(5, 30)) : null,
                'is_daily' => rand(0, 1) === 1,
                'status' => $status,
                'total_attempts' => $status !== 'draft' ? rand(0, 100) : 0,
                'correct_attempts' => $status !== 'draft' ? rand(0, 50) : 0
            ]);
        }
        
        $this->command->info('Brain teasers seeded successfully!');
    }
}