<?php

namespace App\Http\Controllers;

use App\Models\Image;
use App\Models\ProcessingJob;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ProcessingJobController extends Controller
{
    /**
     * Display a listing of processing jobs
     */
    public function index(Request $request): JsonResponse
    {
        $query = Auth::user()->processingJobs()->with(['image', 'user:id,name']);
        
        // Filter by status if provided
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        // Filter by job type if provided
        if ($request->has('job_type')) {
            $query->where('job_type', $request->job_type);
        }
        
        // Filter by project if provided
        if ($request->has('project_id')) {
            $query->whereHas('image', function ($q) use ($request) {
                $q->where('project_id', $request->project_id);
            });
        }

        $jobs = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $jobs,
        ]);
    }

    /**
     * Create a new processing job
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'image_id' => 'required|exists:images,id',
            'job_type' => 'required|string|in:resize,crop,filter,enhance,analyze,detect_objects,extract_text,generate_thumbnail',
            'parameters' => 'required|array',
        ]);

        // Verify image belongs to user's project
        $image = Image::with('project')->findOrFail($validated['image_id']);
        if ($image->project->user_id !== Auth::id()) {
            throw ValidationException::withMessages([
                'image_id' => 'You do not have permission to process this image.',
            ]);
        }

        // Validate parameters based on job type
        $this->validateJobParameters($validated['job_type'], $validated['parameters']);

        $job = ProcessingJob::create([
            'image_id' => $validated['image_id'],
            'user_id' => Auth::id(),
            'job_type' => $validated['job_type'],
            'parameters' => $validated['parameters'],
        ]);

        // Here you would typically queue the actual processing job
        // For now, we'll simulate it
        $this->simulateProcessing($job);

        return response()->json([
            'success' => true,
            'message' => 'Processing job created successfully',
            'data' => $job->load(['image', 'user:id,name']),
        ], 201);
    }

    /**
     * Display the specified processing job
     */
    public function show(ProcessingJob $processingJob): JsonResponse
    {
        $this->authorizeJob($processingJob);

        $processingJob->load(['image.project', 'user:id,name']);

        return response()->json([
            'success' => true,
            'data' => $processingJob,
        ]);
    }

    /**
     * Cancel a processing job
     */
    public function cancel(ProcessingJob $processingJob): JsonResponse
    {
        $this->authorizeJob($processingJob);

        if (!in_array($processingJob->status, ['pending', 'processing'])) {
            throw ValidationException::withMessages([
                'job' => 'Cannot cancel a job that is already completed or failed.',
            ]);
        }

        $processingJob->update([
            'status' => 'failed',
            'error_message' => 'Cancelled by user',
            'completed_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Processing job cancelled successfully',
            'data' => $processingJob,
        ]);
    }

    /**
     * Get job types and their parameter schemas
     */
    public function jobTypes(): JsonResponse
    {
        $jobTypes = [
            'resize' => [
                'description' => 'Resize image to specified dimensions',
                'parameters' => [
                    'width' => 'required|integer|min:1|max:5000',
                    'height' => 'required|integer|min:1|max:5000',
                    'maintain_aspect_ratio' => 'boolean',
                ],
            ],
            'crop' => [
                'description' => 'Crop image to specified area',
                'parameters' => [
                    'x' => 'required|integer|min:0',
                    'y' => 'required|integer|min:0',
                    'width' => 'required|integer|min:1',
                    'height' => 'required|integer|min:1',
                ],
            ],
            'filter' => [
                'description' => 'Apply filter effects to image',
                'parameters' => [
                    'filter_type' => 'required|string|in:blur,sharpen,brightness,contrast,grayscale,sepia',
                    'intensity' => 'required|numeric|between:0,100',
                ],
            ],
            'enhance' => [
                'description' => 'Enhance image quality',
                'parameters' => [
                    'enhancement_type' => 'required|string|in:auto_enhance,noise_reduction,color_correction',
                    'strength' => 'required|numeric|between:0,100',
                ],
            ],
            'analyze' => [
                'description' => 'Analyze image content and properties',
                'parameters' => [
                    'analysis_type' => 'required|string|in:color_palette,histogram,metadata,quality_score',
                ],
            ],
            'detect_objects' => [
                'description' => 'Detect and identify objects in the image',
                'parameters' => [
                    'confidence_threshold' => 'required|numeric|between:0,1',
                    'max_objects' => 'integer|min:1|max:100',
                ],
            ],
            'extract_text' => [
                'description' => 'Extract text from image using OCR',
                'parameters' => [
                    'language' => 'string|in:en,es,fr,de,it,pt,zh,ja,ko',
                    'detect_orientation' => 'boolean',
                ],
            ],
            'generate_thumbnail' => [
                'description' => 'Generate thumbnail images',
                'parameters' => [
                    'sizes' => 'required|array',
                    'sizes.*' => 'required|integer|min:50|max:1000',
                    'quality' => 'integer|between:1,100',
                ],
            ],
        ];

        return response()->json([
            'success' => true,
            'data' => $jobTypes,
        ]);
    }

    /**
     * Validate job parameters based on type
     */
    private function validateJobParameters(string $jobType, array $parameters): void
    {
        $rules = [];

        switch ($jobType) {
            case 'resize':
                $rules = [
                    'width' => 'required|integer|min:1|max:5000',
                    'height' => 'required|integer|min:1|max:5000',
                    'maintain_aspect_ratio' => 'boolean',
                ];
                break;
            case 'crop':
                $rules = [
                    'x' => 'required|integer|min:0',
                    'y' => 'required|integer|min:0',
                    'width' => 'required|integer|min:1',
                    'height' => 'required|integer|min:1',
                ];
                break;
            case 'filter':
                $rules = [
                    'filter_type' => 'required|string|in:blur,sharpen,brightness,contrast,grayscale,sepia',
                    'intensity' => 'required|numeric|between:0,100',
                ];
                break;
            // Add more validation rules for other job types
        }

        if (!empty($rules)) {
            validator($parameters, $rules)->validate();
        }
    }

    /**
     * Simulate processing (replace with actual processing logic)
     */
    private function simulateProcessing(ProcessingJob $job): void
    {
        // In a real application, you would queue this job for background processing
        // For demo purposes, we'll simulate immediate completion
        
        $simulatedResults = [
            'resize' => ['new_width' => 800, 'new_height' => 600, 'file_size_reduction' => '45%'],
            'analyze' => ['dominant_colors' => ['#FF5733', '#33FF57', '#3357FF'], 'brightness' => 75, 'contrast' => 85],
            'detect_objects' => ['objects' => [['name' => 'person', 'confidence' => 0.95], ['name' => 'car', 'confidence' => 0.87]]],
        ];

        $result = $simulatedResults[$job->job_type] ?? ['status' => 'processed', 'timestamp' => now()->toISOString()];

        $job->markAsCompleted($result);
    }

    /**
     * Authorize job access
     */
    private function authorizeJob(ProcessingJob $job): void
    {
        if ($job->user_id !== Auth::id()) {
            throw ValidationException::withMessages([
                'job' => 'You do not have permission to access this processing job.',
            ]);
        }
    }
}