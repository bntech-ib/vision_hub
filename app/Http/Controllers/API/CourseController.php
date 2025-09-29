<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\Image;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class CourseController extends Controller
{
    /**
     * Get all available courses with filtering
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Course::with(['instructor', 'images'])
                ->where('status', 'active');

            // Apply filters
            if ($request->has('category') && $request->category) {
                $query->where('category', $request->category);
            }

            if ($request->has('level') && $request->level) {
                $query->where('level', $request->level);
            }

            if ($request->has('minPrice') && $request->minPrice !== null) {
                $query->where('price', '>=', $request->minPrice);
            }

            if ($request->has('maxPrice') && $request->maxPrice !== null) {
                $query->where('price', '<=', $request->maxPrice);
            }

            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'LIKE', "%{$search}%")
                      ->orWhere('description', 'LIKE', "%{$search}%")
                      ->orWhere('tags', 'LIKE', "%{$search}%");
                });
            }

            // Sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            
            if (in_array($sortBy, ['title', 'price', 'created_at', 'rating', 'total_enrollments'])) {
                $query->orderBy($sortBy, $sortOrder);
            }

            $limit = $request->get('limit', 10);
            $page = $request->get('page', 1);

            $total = $query->count();
            $courses = $query->skip(($page - 1) * $limit)
                ->take($limit)
                ->get();

            // Format courses data to match documentation
            $formattedCourses = $courses->map(function ($course) {
                // Get image URLs
                $imageUrls = $course->images->pluck('file_path')->map(function ($path) {
                    return Storage::url($path);
                })->toArray();
                
                // Get full image objects with metadata
                $imageObjects = $course->images->map(function ($image) {
                    return [
                        'id' => (string)$image->id,
                        'name' => $image->name,
                        'originalFilename' => $image->original_filename,
                        'url' => Storage::url($image->file_path),
                        'mimeType' => $image->mime_type,
                        'fileSize' => (int)$image->file_size,
                        'width' => $image->width,
                        'height' => $image->height,
                        'formattedSize' => $image->formatted_size,
                        'createdAt' => $image->created_at->toISOString(),
                        'updatedAt' => $image->updated_at->toISOString()
                    ];
                })->toArray();
                
                return [
                    'id' => (string)$course->id,
                    'instructorId' => (string)$course->instructor_id,
                    'instructor' => [
                        'id' => (string)$course->instructor->id,
                        'username' => $course->instructor->username,
                        'name' => $course->instructor->name,
                        'country' => $course->instructor->country,
                    ],
                    'title' => $course->title,
                    'description' => $course->description,
                    'price' => (int)$course->price,
                    'currency' => 'NGN',
                    'category' => $course->category,
                    'level' => $course->level,
                    'duration' => (int)$course->duration_hours,
                    'thumbnail' => $course->thumbnail,
                    'images' => $imageObjects, // Include full image objects with metadata
                    'curriculum' => $course->curriculum,
                    'tags' => $course->tags,
                    'status' => $course->status,
                    'enrollmentCount' => (int)$course->total_enrollments,
                    'rating' => $course->rating ? (float)$course->rating : null,
                    'createdAt' => $course->created_at->toISOString(),
                    'updatedAt' => $course->updated_at->toISOString()
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'courses' => $formattedCourses
                ],
                'meta' => [
                    'pagination' => [
                        'total' => $total,
                        'count' => $formattedCourses->count(),
                        'per_page' => (int)$limit,
                        'current_page' => (int)$page,
                        'total_pages' => (int)ceil($total / $limit)
                    ]
                ],
                'message' => 'Courses retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch courses',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get course categories
     */
    public function categories(): JsonResponse
    {
        try {
            $categories = Course::select('category')
                ->where('status', 'active')
                ->groupBy('category')
                ->pluck('category')
                ->filter()
                ->values()
                ->map(function ($category) {
                    return [
                        'value' => $category,
                        'label' => ucfirst($category)
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => ['categories' => $categories],
                'message' => 'Categories retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch categories',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get single course details
     */
    public function show($id): JsonResponse
    {
        try {
            $course = Course::with(['instructor', 'images'])->find($id);

            if (!$course || $course->status !== 'active') {
                return response()->json([
                    'success' => false,
                    'message' => 'Course not found'
                ], 404);
            }

            $user = Auth::user();
            $isEnrolled = false;

            if ($user) {
                $isEnrolled = CourseEnrollment::where('user_id', $user->id)
                    ->where('course_id', $course->id)
                    ->where('status', 'active')
                    ->exists();
            }

            // Increment view count
            $course->increment('view_count');

            // Get image URLs
            $imageUrls = $course->images->pluck('file_path')->map(function ($path) {
                return Storage::url($path);
            })->toArray();

            // Get full image objects with metadata
            $imageObjects = $course->images->map(function ($image) {
                return [
                    'id' => (string)$image->id,
                    'name' => $image->name,
                    'originalFilename' => $image->original_filename,
                    'url' => Storage::url($image->file_path),
                    'mimeType' => $image->mime_type,
                    'fileSize' => (int)$image->file_size,
                    'width' => $image->width,
                    'height' => $image->height,
                    'formattedSize' => $image->formatted_size,
                    'createdAt' => $image->created_at->toISOString(),
                    'updatedAt' => $image->updated_at->toISOString()
                ];
            })->toArray();

            // Format course data to match documentation
            $formattedCourse = [
                'id' => (string)$course->id,
                'instructorId' => (string)$course->instructor_id,
                'instructor' => [
                    'id' => (string)$course->instructor->id,
                    'username' => $course->instructor->username,
                    'name' => $course->instructor->name,
                    'country' => $course->instructor->country,
                ],
                'title' => $course->title,
                'description' => $course->description,
                'price' => (int)$course->price,
                'currency' => 'NGN',
                'category' => $course->category,
                'level' => $course->level,
                'duration' => (int)$course->duration_hours,
                'thumbnail' => $course->thumbnail,
                'images' => $imageObjects, // Include full image objects with metadata
                'curriculum' => $course->curriculum,
                'tags' => $course->tags,
                'videoSource' => $isEnrolled ? $course->video_source : null,
                'status' => $course->status,
                'enrollmentCount' => (int)$course->total_enrollments,
                'rating' => $course->rating ? (float)$course->rating : null,
                'isEnrolled' => $isEnrolled,
                'createdAt' => $course->created_at->toISOString(),
                'updatedAt' => $course->updated_at->toISOString()
            ];

            return response()->json([
                'success' => true,
                'data' => ['course' => $formattedCourse],
                'message' => 'Course retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch course',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Enroll in a course
     */
    public function enroll(Request $request, $id): JsonResponse
    {
        try {
            $user = Auth::user();

            // Check if user has active package with course access
            if (!$user->hasActivePackage()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You need an active package to enroll in courses'
                ], 403);
            }

            // Find the course
            $course = Course::where('id', $id)
                ->where('status', 'active')
                ->first();

            if (!$course) {
                // Let's also check if the course exists but is not active
                $courseExists = Course::where('id', $id)->exists();
                if ($courseExists) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Course is not available for enrollment'
                    ], 404);
                }
                
                return response()->json([
                    'success' => false,
                    'message' => 'Course not found'
                ], 404);
            }

            // Check if already enrolled
            $existingEnrollment = CourseEnrollment::where('user_id', $user->id)
                ->where('course_id', $course->id)
                ->first();

            if ($existingEnrollment) {
                if ($existingEnrollment->status === 'active') {
                    return response()->json([
                        'success' => false,
                        'message' => 'You are already enrolled in this course'
                    ], 400);
                } else {
                    // Reactivate enrollment if it was cancelled
                    $existingEnrollment->update(['status' => 'active']);
                    
                    return response()->json([
                        'success' => true,
                        'message' => 'Successfully re-enrolled in the course',
                        'data' => [
                            'enrollment' => [
                                'id' => (string)$existingEnrollment->id,
                                'userId' => (string)$existingEnrollment->user_id,
                                'courseId' => (string)$existingEnrollment->course_id,
                                'status' => $existingEnrollment->status,
                                'progress' => (int)$existingEnrollment->progress_percentage,
                                'enrolledAt' => $existingEnrollment->enrolled_at->toISOString(),
                                'completedAt' => $existingEnrollment->completed_at ? $existingEnrollment->completed_at->toISOString() : null
                            ]
                        ]
                    ]);
                }
            }

            // Check course access limit for user's package
            $activePackage = $user->activePackage();
            if ($activePackage && $activePackage->course_access_limit > 0) {
                $currentEnrollments = CourseEnrollment::where('user_id', $user->id)
                    ->where('status', 'active')
                    ->count();

                if ($currentEnrollments >= $activePackage->course_access_limit) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You have reached your course enrollment limit for your current package'
                    ], 403);
                }
            }

            DB::beginTransaction();

            try {
                // Handle payment if course is not free
                if ($course->price > 0) {
                    if ($user->wallet_balance < $course->price) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Insufficient wallet balance'
                        ], 400);
                    }

                    // Deduct from user's wallet
                    $user->deductFromWallet($course->price);

                    // Add to instructor's wallet (with platform commission)
                    $platformCommission = $course->price * 0.10; // 10% platform fee
                    $instructorAmount = $course->price - $platformCommission;
                    
                    $instructor = User::find($course->instructor_id);
                    $instructor->addToWallet($instructorAmount);

                    // Create transaction records
                    $purchaseTransaction = Transaction::create([
                        'user_id' => $user->id,
                        'type' => 'purchase',
                        'amount' => $course->price,
                        'description' => "Enrolled in course: {$course->title}",
                        'status' => 'completed',
                        'reference_type' => 'App\Models\Course',
                        'reference_id' => $course->id,
                        'metadata' => [
                            'course_id' => $course->id,
                            'instructor_id' => $course->instructor_id
                        ]
                    ]);

                    $earningTransaction = Transaction::create([
                        'user_id' => $instructor->id,
                        'type' => 'earning',
                        'amount' => $instructorAmount,
                        'description' => "Course enrollment: {$course->title}",
                        'status' => 'completed',
                        'reference_type' => 'App\Models\Course',
                        'reference_id' => $course->id,
                        'metadata' => [
                            'course_id' => $course->id,
                            'student_id' => $user->id,
                            'platform_commission' => $platformCommission
                        ]
                    ]);
                }

                // Create enrollment record
                $enrollment = CourseEnrollment::create([
                    'user_id' => $user->id,
                    'course_id' => $course->id,
                    'status' => 'active',
                    'enrolled_at' => now()
                ]);

                // Update course enrollment count
                $course->increment('total_enrollments');

                DB::commit();

                // Format enrollment data to match documentation
                $formattedEnrollment = [
                    'id' => (string)$enrollment->id,
                    'userId' => (string)$enrollment->user_id,
                    'courseId' => (string)$enrollment->course_id,
                    'status' => $enrollment->status,
                    'progress' => (int)$enrollment->progress_percentage,
                    'enrolledAt' => $enrollment->enrolled_at->toISOString(),
                    'completedAt' => $enrollment->completed_at ? $enrollment->completed_at->toISOString() : null
                ];

                return response()->json([
                    'success' => true,
                    'message' => 'Successfully enrolled in the course',
                    'data' => ['enrollment' => $formattedEnrollment]
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Enrollment failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user's enrolled courses
     */
    public function myEnrollments(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            
            // Check if user is authenticated
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $query = CourseEnrollment::with(['course.instructor', 'course.images'])
                ->where('user_id', $user->id)
                ->orderBy('enrolled_at', 'desc');

            if ($request->has('status') && $request->status) {
                $query->where('status', $request->status);
            }

            $limit = $request->get('limit', 10);
            $page = $request->get('page', 1);

            $total = $query->count();
            $enrollments = $query->skip(($page - 1) * $limit)
                ->take($limit)
                ->get();
                
            // Debug information
            \Log::info('My Enrollments Query', [
                'user_id' => $user->id,
                'total_enrollments' => $total,
                'enrollments_found' => $enrollments->count()
            ]);

            // Format enrollments data to match documentation
            $formattedEnrollments = $enrollments->map(function ($enrollment) {
                // Debug information
                \Log::info('Processing enrollment', [
                    'enrollment_id' => $enrollment->id,
                    'course_id' => $enrollment->course_id,
                    'course_exists' => $enrollment->course ? 'yes' : 'no',
                    'course_status' => $enrollment->course ? $enrollment->course->status : null
                ]);
                
                // Check if course exists and is active
                $courseData = null;
                if ($enrollment->course) {
                    // Get image URLs
                    $imageUrls = $enrollment->course->images->pluck('file_path')->map(function ($path) {
                        return Storage::url($path);
                    })->toArray();
                    
                    // Get full image objects with metadata
                    $imageObjects = $enrollment->course->images->map(function ($image) {
                        return [
                            'id' => (string)$image->id,
                            'name' => $image->name,
                            'originalFilename' => $image->original_filename,
                            'url' => Storage::url($image->file_path),
                            'mimeType' => $image->mime_type,
                            'fileSize' => (int)$image->file_size,
                            'width' => $image->width,
                            'height' => $image->height,
                            'formattedSize' => $image->formatted_size,
                            'createdAt' => $image->created_at->toISOString(),
                            'updatedAt' => $image->updated_at->toISOString()
                        ];
                    })->toArray();
                    
                    $courseData = [
                        'id' => (string)$enrollment->course->id,
                        'title' => $enrollment->course->title,
                        'thumbnailUrl' => $enrollment->course->thumbnail,
                        'images' => $imageObjects, // Include full image objects with metadata
                        'instructor' => $enrollment->course->instructor ? [
                            'id' => (string)$enrollment->course->instructor->id,
                            'username' => $enrollment->course->instructor->username,
                            'name' => $enrollment->course->instructor->name,
                            'country' => $enrollment->course->instructor->country,
                        ] : null,
                        'duration' => (int)$enrollment->course->duration_hours,
                        'lessonsCount' => count($enrollment->course->curriculum ?? []),
                        'curriculum' => $enrollment->course->curriculum,
                        'tags' => $enrollment->course->tags,
                        'status' => $enrollment->course->status
                    ];
                    
                    // Include video source for enrolled courses
                    if ($enrollment->course->video_source) {
                        $courseData['videoSource'] = $enrollment->course->video_source;
                    }
                }
                
                return [
                    'id' => (string)$enrollment->id,
                    'userId' => (string)$enrollment->user_id,
                    'courseId' => (string)$enrollment->course_id,
                    'progress' => (int)$enrollment->progress_percentage,
                    'completedLessons' => [], // Placeholder for completed lessons
                    'enrolledAt' => $enrollment->enrolled_at->toISOString(),
                    'course' => $courseData
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'enrollments' => $formattedEnrollments
                ],
                'meta' => [
                    'pagination' => [
                        'total' => $total,
                        'count' => $formattedEnrollments->count(),
                        'per_page' => (int)$limit,
                        'current_page' => (int)$page,
                        'total_pages' => (int)ceil($total / $limit)
                    ]
                ],
                'message' => 'My enrollments retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch enrollments',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update course progress
     */
    public function updateProgress(Request $request, $id): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'lessonId' => 'required',
                'completed' => 'required|boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = Auth::user();

            $enrollment = CourseEnrollment::where('user_id', $user->id)
                ->where('course_id', $id)
                ->where('status', 'active')
                ->first();

            if (!$enrollment) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not enrolled in this course'
                ], 404);
            }

            // Update progress based on completed lessons
            $completedLessons = $enrollment->metadata['completedLessons'] ?? [];
            
            if ($request->completed && !in_array($request->lessonId, $completedLessons)) {
                $completedLessons[] = $request->lessonId;
            } elseif (!$request->completed && in_array($request->lessonId, $completedLessons)) {
                $completedLessons = array_diff($completedLessons, [$request->lessonId]);
            }
            
            // Calculate progress based on completed lessons
            $course = $enrollment->course;
            $totalLessons = count($course->curriculum ?? []);
            $progress = $totalLessons > 0 ? (count($completedLessons) / $totalLessons) * 100 : 0;
            
            $enrollment->update([
                'progress_percentage' => $progress,
                'metadata' => ['completedLessons' => array_values($completedLessons)],
                'last_accessed_at' => now()
            ]);

            // Mark as completed if progress is 100%
            if ($progress >= 100 && $enrollment->status !== 'completed') {
                $enrollment->update([
                    'status' => 'completed',
                    'completed_at' => now()
                ]);
            }

            // Format enrollment data to match documentation
            $formattedEnrollment = [
                'id' => (string)$enrollment->id,
                'userId' => (string)$enrollment->user_id,
                'courseId' => (string)$enrollment->course_id,
                'progress' => (int)$progress,
                'completedLessons' => array_values($completedLessons),
                'enrolledAt' => $enrollment->enrolled_at->toISOString()
            ];

            return response()->json([
                'success' => true,
                'message' => 'Progress updated successfully',
                'data' => ['enrollment' => $formattedEnrollment]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update progress',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new course (for instructors)
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'category' => 'required|string|max:100',
                'level' => 'required|in:beginner,intermediate,advanced',
                'price' => 'required|numeric|min:0',
                'duration' => 'required|integer|min:1',
                'thumbnail' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
                'video_source' => 'nullable|string|max:500',
                'images' => 'sometimes|array',
                'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048',
                'curriculum' => 'required|array|min:1',
                'curriculum.*.title' => 'required|string|max:255',
                'curriculum.*.duration' => 'required|integer|min:1',
                'tags' => 'nullable|string|max:500'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = Auth::user();

            // Handle thumbnail upload
            $thumbnailUrl = null;
            if ($request->hasFile('thumbnail')) {
                $path = $request->file('thumbnail')->store('course-thumbnails', 'public');
                $thumbnailUrl = Storage::url($path);
            }

            DB::beginTransaction();

            try {
                // Create the course
                $course = Course::create([
                    'instructor_id' => $user->id,
                    'title' => $request->title,
                    'description' => $request->description,
                    'category' => $request->category,
                    'level' => $request->level,
                    'price' => $request->price,
                    'duration_hours' => $request->duration,
                    'thumbnail' => $thumbnailUrl,
                    'video_source' => $request->video_source,
                    'curriculum' => $request->curriculum,
                    'tags' => $request->tags,
                    'status' => 'pending_review'
                ]);

                // Handle image uploads
                if ($request->hasFile('images')) {
                    foreach ($request->file('images') as $imageFile) {
                        $path = $imageFile->store('course-images', 'public');
                        
                        // Create image record
                        $image = Image::create([
                            'name' => $imageFile->getClientOriginalName(),
                            'original_filename' => $imageFile->getClientOriginalName(),
                            'file_path' => $path,
                            'file_hash' => hash_file('sha256', $imageFile->path()),
                            'mime_type' => $imageFile->getMimeType(),
                            'file_size' => $imageFile->getSize(),
                            'uploaded_by' => $user->id,
                            'status' => 'processed'
                        ]);
                        
                        // Associate image with course
                        $course->images()->attach($image->id);
                    }
                }

                DB::commit();

                // Load relationships
                $course->load(['instructor', 'images']);

                // Get image URLs
                $imageUrls = $course->images->pluck('file_path')->map(function ($path) {
                    return Storage::url($path);
                })->toArray();

                // Get full image objects with metadata
                $imageObjects = $course->images->map(function ($image) {
                    return [
                        'id' => (string)$image->id,
                        'name' => $image->name,
                        'originalFilename' => $image->original_filename,
                        'url' => Storage::url($image->file_path),
                        'mimeType' => $image->mime_type,
                        'fileSize' => (int)$image->file_size,
                        'width' => $image->width,
                        'height' => $image->height,
                        'formattedSize' => $image->formatted_size,
                        'createdAt' => $image->created_at->toISOString(),
                        'updatedAt' => $image->updated_at->toISOString()
                    ];
                })->toArray();

                // Format course data to match documentation
                $formattedCourse = [
                    'id' => (string)$course->id,
                    'instructorId' => (string)$course->instructor_id,
                    'instructor' => [
                        'id' => (string)$course->instructor->id,
                        'username' => $course->instructor->username,
                        'name' => $course->instructor->name,
                        'country' => $course->instructor->country,
                    ],
                    'title' => $course->title,
                    'description' => $course->description,
                    'price' => (int)$course->price,
                    'currency' => 'NGN',
                    'category' => $course->category,
                    'level' => $course->level,
                    'duration' => (int)$course->duration_hours,
                    'thumbnail' => $course->thumbnail,
                    'images' => $imageObjects, // Include full image objects with metadata
                    'curriculum' => $course->curriculum,
                    'tags' => $course->tags,
                    'status' => $course->status,
                    'enrollmentCount' => (int)$course->total_enrollments,
                    'rating' => $course->rating ? (float)$course->rating : null,
                    'createdAt' => $course->created_at->toISOString(),
                    'updatedAt' => $course->updated_at->toISOString()
                ];

                return response()->json([
                    'success' => true,
                    'message' => 'Course created successfully',
                    'data' => ['course' => $formattedCourse]
                ], 201);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create course',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get instructor's courses
     */
    public function myCourses(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();

            $query = Course::with(['images'])->where('instructor_id', $user->id)
                ->orderBy('created_at', 'desc');

            if ($request->has('status') && $request->status) {
                $query->where('status', $request->status);
            }

            $limit = $request->get('limit', 10);
            $page = $request->get('page', 1);

            $total = $query->count();
            $courses = $query->skip(($page - 1) * $limit)
                ->take($limit)
                ->get();

            // Format courses data to match documentation
            $formattedCourses = $courses->map(function ($course) {
                // Get image URLs
                $imageUrls = $course->images->pluck('file_path')->map(function ($path) {
                    return Storage::url($path);
                })->toArray();
                
                // Get full image objects with metadata
                $imageObjects = $course->images->map(function ($image) {
                    return [
                        'id' => (string)$image->id,
                        'name' => $image->name,
                        'originalFilename' => $image->original_filename,
                        'url' => Storage::url($image->file_path),
                        'mimeType' => $image->mime_type,
                        'fileSize' => (int)$image->file_size,
                        'width' => $image->width,
                        'height' => $image->height,
                        'formattedSize' => $image->formatted_size,
                        'createdAt' => $image->created_at->toISOString(),
                        'updatedAt' => $image->updated_at->toISOString()
                    ];
                })->toArray();
                
                return [
                    'id' => (string)$course->id,
                    'instructorId' => (string)$course->instructor_id,
                    'title' => $course->title,
                    'description' => $course->description,
                    'price' => (int)$course->price,
                    'currency' => 'NGN',
                    'category' => $course->category,
                    'level' => $course->level,
                    'duration' => (int)$course->duration_hours,
                    'thumbnail' => $course->thumbnail,
                    'images' => $imageObjects, // Include full image objects with metadata
                    'curriculum' => $course->curriculum,
                    'tags' => $course->tags,
                    'status' => $course->status,
                    'enrollmentCount' => (int)$course->total_enrollments,
                    'rating' => $course->rating ? (float)$course->rating : null,
                    'createdAt' => $course->created_at->toISOString(),
                    'updatedAt' => $course->updated_at->toISOString()
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'courses' => $formattedCourses
                ],
                'meta' => [
                    'pagination' => [
                        'total' => $total,
                        'count' => $formattedCourses->count(),
                        'per_page' => (int)$limit,
                        'current_page' => (int)$page,
                        'total_pages' => (int)ceil($total / $limit)
                    ]
                ],
                'message' => 'Courses retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch your courses',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update a course
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $user = Auth::user();

            $course = Course::with(['images'])->where('id', $id)
                ->where('instructor_id', $user->id)
                ->first();

            if (!$course) {
                return response()->json([
                    'success' => false,
                    'message' => 'Course not found or you don\'t have permission to edit it'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'title' => 'sometimes|string|max:255',
                'description' => 'sometimes|string',
                'category' => 'sometimes|string|max:100',
                'level' => 'sometimes|in:beginner,intermediate,advanced',
                'price' => 'sometimes|numeric|min:0',
                'duration' => 'sometimes|integer|min:1',
                'thumbnail' => 'sometimes|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
                'video_source' => 'nullable|string|max:500',
                'images' => 'sometimes|array',
                'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048',
                'curriculum' => 'sometimes|array|min:1',
                'curriculum.*.title' => 'required_with:curriculum|string|max:255',
                'curriculum.*.duration' => 'required_with:curriculum|integer|min:1',
                'tags' => 'nullable|string|max:500',
                'status' => 'sometimes|in:active,inactive,pending_review'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            try {
                // Handle thumbnail upload if provided
                if ($request->hasFile('thumbnail')) {
                    $path = $request->file('thumbnail')->store('course-thumbnails', 'public');
                    $course->thumbnail = Storage::url($path);
                }

                // Update other fields
                $course->title = $request->title ?? $course->title;
                $course->description = $request->description ?? $course->description;
                $course->category = $request->category ?? $course->category;
                $course->level = $request->level ?? $course->level;
                $course->price = $request->price ?? $course->price;
                $course->duration_hours = $request->duration ?? $course->duration_hours;
                $course->curriculum = $request->curriculum ?? $course->curriculum;
                $course->tags = $request->tags ?? $course->tags;
                $course->status = $request->status ?? $course->status;
                $course->video_source = $request->video_source ?? $course->video_source;
                
                $course->save();

                // Handle image uploads
                if ($request->hasFile('images')) {
                    foreach ($request->file('images') as $imageFile) {
                        $path = $imageFile->store('course-images', 'public');
                        
                        // Create image record
                        $image = Image::create([
                            'name' => $imageFile->getClientOriginalName(),
                            'original_filename' => $imageFile->getClientOriginalName(),
                            'file_path' => $path,
                            'file_hash' => hash_file('sha256', $imageFile->path()),
                            'mime_type' => $imageFile->getMimeType(),
                            'file_size' => $imageFile->getSize(),
                            'uploaded_by' => $user->id,
                            'status' => 'processed'
                        ]);
                        
                        // Associate image with course
                        $course->images()->attach($image->id);
                    }
                }

                DB::commit();

                // Load relationships
                $course->load(['instructor', 'images']);

                // Get image URLs
                $imageUrls = $course->images->pluck('file_path')->map(function ($path) {
                    return Storage::url($path);
                })->toArray();

                // Get full image objects with metadata
                $imageObjects = $course->images->map(function ($image) {
                    return [
                        'id' => (string)$image->id,
                        'name' => $image->name,
                        'originalFilename' => $image->original_filename,
                        'url' => Storage::url($image->file_path),
                        'mimeType' => $image->mime_type,
                        'fileSize' => (int)$image->file_size,
                        'width' => $image->width,
                        'height' => $image->height,
                        'formattedSize' => $image->formatted_size,
                        'createdAt' => $image->created_at->toISOString(),
                        'updatedAt' => $image->updated_at->toISOString()
                    ];
                })->toArray();

                // Format course data to match documentation
                $formattedCourse = [
                    'id' => (string)$course->id,
                    'instructorId' => (string)$course->instructor_id,
                    'instructor' => [
                        'id' => (string)$course->instructor->id,
                        'username' => $course->instructor->username,
                        'name' => $course->instructor->name,
                        'country' => $course->instructor->country,
                    ],
                    'title' => $course->title,
                    'description' => $course->description,
                    'price' => (int)$course->price,
                    'currency' => 'NGN',
                    'category' => $course->category,
                    'level' => $course->level,
                    'duration' => (int)$course->duration_hours,
                    'thumbnail' => $course->thumbnail,
                    'images' => $imageObjects, // Include full image objects with metadata
                    'curriculum' => $course->curriculum,
                    'tags' => $course->tags,
                    'status' => $course->status,
                    'enrollmentCount' => (int)$course->total_enrollments,
                    'rating' => $course->rating ? (float)$course->rating : null,
                    'createdAt' => $course->created_at->toISOString(),
                    'updatedAt' => $course->updated_at->toISOString()
                ];

                return response()->json([
                    'success' => true,
                    'message' => 'Course updated successfully',
                    'data' => ['course' => $formattedCourse]
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update course',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a course
     */
    public function destroy($id): JsonResponse
    {
        try {
            $user = Auth::user();

            $course = Course::where('id', $id)
                ->where('instructor_id', $user->id)
                ->first();

            if (!$course) {
                return response()->json([
                    'success' => false,
                    'message' => 'Course not found or you don\'t have permission to delete it'
                ], 404);
            }

            $course->update(['status' => 'deleted']);

            return response()->json([
                'success' => true,
                'message' => 'Course deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete course',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}