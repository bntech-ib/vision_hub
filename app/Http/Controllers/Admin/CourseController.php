<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\User;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    /**
     * Display a listing of courses
     */
    public function index(Request $request)
    {
        $query = Course::with('instructor:id,name,email');
        
        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('instructor', function($instructorQuery) use ($search) {
                      $instructorQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Filter by category
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        
        $courses = $query->latest()->paginate(20);
        
        $categories = [
            'programming' => 'Programming',
            'design' => 'Design',
            'business' => 'Business',
            'marketing' => 'Marketing',
            'photography' => 'Photography',
            'music' => 'Music',
            'other' => 'Other',
        ];
        
        $statuses = [
            'draft' => 'Draft',
            'pending' => 'Pending Review',
            'active' => 'Active',
            'archived' => 'Archived',
        ];
        
        return view('admin.courses.index', compact('courses', 'categories', 'statuses'));
    }
    
    /**
     * Show the form for creating a new course
     */
    public function create()
    {
        $instructors = User::select('id', 'name', 'email')->get();
        
        $categories = [
            'programming' => 'Programming',
            'design' => 'Design',
            'business' => 'Business',
            'marketing' => 'Marketing',
            'photography' => 'Photography',
            'music' => 'Music',
            'other' => 'Other',
        ];
        
        $levels = [
            'beginner' => 'Beginner',
            'intermediate' => 'Intermediate',
            'advanced' => 'Advanced',
        ];
        
        $statuses = [
            'draft' => 'Draft',
            'pending' => 'Pending Review',
            'active' => 'Active',
            'archived' => 'Archived',
        ];
        
        return view('admin.courses.create', compact('instructors', 'categories', 'levels', 'statuses'));
    }
    
    /**
     * Store a newly created course
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'instructor_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|string',
            'level' => 'required|string|in:beginner,intermediate,advanced',
            'price' => 'required|numeric|min:0',
            'duration_hours' => 'required|integer|min:1',
            'status' => 'required|string|in:draft,pending,active,archived',
        ]);
        
        Course::create($validated);
        
        return redirect()->route('admin.courses.index')
            ->with('success', 'Course created successfully.');
    }
    
    /**
     * Display the specified course
     */
    public function show(Course $course)
    {
        $course->load(['instructor:id,name,email', 'enrollments' => function($q) {
            $q->latest()->limit(10);
        }]);
        
        $stats = [
            'enrollment_count' => $course->enrollment_count,
            'view_count' => $course->view_count,
            'rating' => $course->rating,
        ];
        
        return view('admin.courses.show', compact('course', 'stats'));
    }
    
    /**
     * Show the form for editing the specified course
     */
    public function edit(Course $course)
    {
        $instructors = User::select('id', 'name', 'email')->get();
        
        $categories = [
            'programming' => 'Programming',
            'design' => 'Design',
            'business' => 'Business',
            'marketing' => 'Marketing',
            'photography' => 'Photography',
            'music' => 'Music',
            'other' => 'Other',
        ];
        
        $levels = [
            'beginner' => 'Beginner',
            'intermediate' => 'Intermediate',
            'advanced' => 'Advanced',
        ];
        
        $statuses = [
            'draft' => 'Draft',
            'pending' => 'Pending Review',
            'active' => 'Active',
            'archived' => 'Archived',
        ];
        
        return view('admin.courses.edit', compact('course', 'instructors', 'categories', 'levels', 'statuses'));
    }
    
    /**
     * Update the specified course
     */
    public function update(Request $request, Course $course)
    {
        $validated = $request->validate([
            'instructor_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|string',
            'level' => 'required|string|in:beginner,intermediate,advanced',
            'price' => 'required|numeric|min:0',
            'duration_hours' => 'required|integer|min:1',
            'status' => 'required|string|in:draft,pending,active,archived',
        ]);
        
        $course->update($validated);
        
        return redirect()->route('admin.courses.show', $course)
            ->with('success', 'Course updated successfully.');
    }
    
    /**
     * Remove the specified course
     */
    public function destroy(Course $course)
    {
        $course->delete();
        
        return redirect()->route('admin.courses.index')
            ->with('success', 'Course deleted successfully.');
    }
}