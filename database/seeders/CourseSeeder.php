<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Course;
use App\Models\User;

class CourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get instructor users
        $instructors = User::where('is_admin', false)->take(5)->get();
        
        if ($instructors->isEmpty()) {
            $this->command->info('No instructors found. Skipping course seeding.');
            return;
        }
        
        $categories = ['Photography', 'Design', 'Marketing', 'Technology', 'Business'];
        $difficulties = ['beginner', 'intermediate', 'advanced'];
        $statuses = ['draft', 'published', 'archived'];
        
        $courses = [
            [
                'title' => 'Photography Fundamentals',
                'description' => 'Learn the basics of photography including composition, lighting, and camera settings.',
                'category' => 'Photography',
                'difficulty' => 'beginner'
            ],
            [
                'title' => 'Advanced Photoshop Techniques',
                'description' => 'Master advanced photo editing techniques in Adobe Photoshop.',
                'category' => 'Design',
                'difficulty' => 'advanced'
            ],
            [
                'title' => 'Digital Marketing Strategy',
                'description' => 'Learn how to create and implement effective digital marketing campaigns.',
                'category' => 'Marketing',
                'difficulty' => 'intermediate'
            ],
            [
                'title' => 'Web Development Bootcamp',
                'description' => 'Complete guide to modern web development with HTML, CSS, and JavaScript.',
                'category' => 'Technology',
                'difficulty' => 'beginner'
            ],
            [
                'title' => 'Entrepreneurship 101',
                'description' => 'Essential skills and knowledge for starting your own business.',
                'category' => 'Business',
                'difficulty' => 'beginner'
            ],
            [
                'title' => 'Mobile App Design',
                'description' => 'Learn UI/UX principles for designing mobile applications.',
                'category' => 'Design',
                'difficulty' => 'intermediate'
            ],
            [
                'title' => 'Data Analysis with Python',
                'description' => 'Learn how to analyze and visualize data using Python.',
                'category' => 'Technology',
                'difficulty' => 'advanced'
            ],
            [
                'title' => 'Portrait Photography Masterclass',
                'description' => 'Master the art of portrait photography with professional techniques.',
                'category' => 'Photography',
                'difficulty' => 'intermediate'
            ]
        ];
        
        foreach ($courses as $courseData) {
            $instructor = $instructors->random();
            $status = $statuses[array_rand($statuses)];
            
            Course::create([
                'instructor_id' => $instructor->id,
                'title' => $courseData['title'],
                'description' => $courseData['description'],
                'category' => $courseData['category'],
                'difficulty' => $courseData['difficulty'],
                'price' => rand(19, 199) + (rand(0, 99) / 100),
                'duration_hours' => rand(2, 20),
                'thumbnail_url' => null,
                'curriculum' => $this->generateCurriculum(),
                'status' => $status,
                'rating' => $status === 'published' ? rand(30, 50) / 10 : 0,
                'total_enrollments' => $status === 'published' ? rand(0, 100) : 0,
                'view_count' => rand(0, 500)
            ]);
        }
        
        $this->command->info('Courses seeded successfully!');
    }
    
    private function generateCurriculum(): array
    {
        $modules = rand(3, 8);
        $curriculum = [];
        
        for ($i = 1; $i <= $modules; $i++) {
            $lessons = rand(3, 10);
            $module = [
                'title' => 'Module ' . $i . ': ' . $this->getModuleTitle(),
                'lessons' => []
            ];
            
            for ($j = 1; $j <= $lessons; $j++) {
                $module['lessons'][] = [
                    'title' => 'Lesson ' . $j . ': ' . $this->getLessonTitle(),
                    'duration_minutes' => rand(5, 30)
                ];
            }
            
            $curriculum[] = $module;
        }
        
        return $curriculum;
    }
    
    private function getModuleTitle(): string
    {
        $titles = [
            'Introduction', 'Getting Started', 'Core Concepts', 'Advanced Techniques',
            'Best Practices', 'Case Studies', 'Hands-on Project', 'Conclusion'
        ];
        
        return $titles[array_rand($titles)];
    }
    
    private function getLessonTitle(): string
    {
        $titles = [
            'Overview', 'Key Principles', 'Practical Examples', 'Common Mistakes',
            'Tips and Tricks', 'Real-world Applications', 'Exercise', 'Summary'
        ];
        
        return $titles[array_rand($titles)];
    }
}