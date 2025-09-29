<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProcessingJob;
use App\Models\Image;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Carbon\Carbon;

class ProcessingJobController extends Controller
{
    /**
     * Display a listing of processing jobs
     */
    public function index(Request $request): View
    {
        $query = ProcessingJob::with(['image.project', 'user']);

        // Apply filters
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('job_id', 'like', '%' . $request->search . '%')
                  ->orWhere('job_type', 'like', '%' . $request->search . '%')
                  ->orWhereHas('image', function ($imageQuery) use ($request) {
                      $imageQuery->where('name', 'like', '%' . $request->search . '%');
                  })
                  ->orWhereHas('user', function ($userQuery) use ($request) {
                      $userQuery->where('name', 'like', '%' . $request->search . '%')
                                ->orWhere('email', 'like', '%' . $request->search . '%');
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('job_type')) {
            $query->where('job_type', $request->job_type);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $jobs = $query->orderBy('created_at', 'desc')->paginate(20);
        
        $users = User::select('id', 'name', 'email')->get();
        $jobTypes = ProcessingJob::distinct('job_type')->pluck('job_type');

        return view('admin.processing-jobs.index', compact('jobs', 'users', 'jobTypes'));
    }

    /**
     * Display the specified processing job
     */
    public function show(ProcessingJob $processingJob): View
    {
        $processingJob->load(['image.project.user', 'user']);

        return view('admin.processing-jobs.show', compact('processingJob'));
    }

    /**
     * Show the form for editing the specified processing job
     */
    public function edit(ProcessingJob $processingJob): View
    {
        $processingJob->load(['image', 'user']);
        
        return view('admin.processing-jobs.edit', compact('processingJob'));
    }

    /**
     * Update the specified processing job
     */
    public function update(Request $request, ProcessingJob $processingJob): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,processing,completed,failed',
            'error_message' => 'nullable|string|max:1000',
            'progress' => 'nullable|integer|between:0,100',
        ]);

        // Update completed_at when changing to completed or failed
        if (in_array($validated['status'], ['completed', 'failed']) && $processingJob->status !== $validated['status']) {
            $validated['completed_at'] = now();
            if ($validated['status'] === 'completed') {
                $validated['progress'] = 100;
            }
        }

        $processingJob->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Processing job updated successfully',
            'job' => $processingJob->fresh(['image', 'user'])
        ]);
    }

    /**
     * Remove the specified processing job
     */
    public function destroy(ProcessingJob $processingJob): JsonResponse
    {
        $processingJob->delete();

        return response()->json([
            'success' => true,
            'message' => 'Processing job deleted successfully'
        ]);
    }

    /**
     * Cancel a processing job
     */
    public function cancel(ProcessingJob $processingJob): JsonResponse
    {
        if (!in_array($processingJob->status, ['pending', 'processing'])) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot cancel a job that is already completed or failed'
            ], 400);
        }

        $processingJob->update([
            'status' => 'failed',
            'error_message' => 'Cancelled by administrator',
            'completed_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Processing job cancelled successfully'
        ]);
    }

    /**
     * Retry a failed processing job
     */
    public function retry(ProcessingJob $processingJob): JsonResponse
    {
        if ($processingJob->status !== 'failed') {
            return response()->json([
                'success' => false,
                'message' => 'Only failed jobs can be retried'
            ], 400);
        }

        $processingJob->update([
            'status' => 'pending',
            'error_message' => null,
            'completed_at' => null,
            'progress' => 0,
            'started_at' => null
        ]);

        // Here you would typically dispatch the job to the queue again
        // dispatch(new ProcessImageJob($processingJob));

        return response()->json([
            'success' => true,
            'message' => 'Processing job queued for retry'
        ]);
    }

    /**
     * Get failed processing jobs
     */
    public function failed(Request $request): JsonResponse
    {
        $jobs = ProcessingJob::where('status', 'failed')
            ->with(['image.project', 'user'])
            ->when($request->filled('job_type'), function ($query) use ($request) {
                $query->where('job_type', $request->job_type);
            })
            ->orderBy('updated_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $jobs
        ]);
    }

    /**
     * Get processing job statistics
     */
    public function statistics(): JsonResponse
    {
        $stats = [
            'total_jobs' => ProcessingJob::count(),
            'by_status' => ProcessingJob::selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status'),
            'by_type' => ProcessingJob::selectRaw('job_type, COUNT(*) as count')
                ->groupBy('job_type')
                ->pluck('count', 'job_type'),
            'success_rate' => $this->calculateSuccessRate(),
            'average_processing_time' => $this->calculateAverageProcessingTime(),
            'jobs_per_day' => $this->getJobsPerDay(),
            'recent_failures' => ProcessingJob::where('status', 'failed')
                ->with(['image', 'user'])
                ->orderBy('updated_at', 'desc')
                ->limit(5)
                ->get(['id', 'job_type', 'error_message', 'image_id', 'user_id', 'updated_at'])
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Bulk retry failed jobs
     */
    public function bulkRetry(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'job_ids' => 'required|array',
            'job_ids.*' => 'exists:processing_jobs,id'
        ]);

        $jobs = ProcessingJob::whereIn('id', $validated['job_ids'])
            ->where('status', 'failed');

        $count = $jobs->count();

        $jobs->update([
            'status' => 'pending',
            'error_message' => null,
            'completed_at' => null,
            'progress' => 0,
            'started_at' => null
        ]);

        return response()->json([
            'success' => true,
            'message' => "{$count} jobs queued for retry"
        ]);
    }

    /**
     * Cleanup old completed jobs
     */
    public function cleanup(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'days' => 'required|integer|min:1|max:365'
        ]);

        $cutoffDate = Carbon::now()->subDays($validated['days']);
        
        $deletedCount = ProcessingJob::where('status', 'completed')
            ->where('completed_at', '<', $cutoffDate)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => "Cleaned up {$deletedCount} old processing jobs"
        ]);
    }

    /**
     * Calculate success rate
     */
    private function calculateSuccessRate(): float
    {
        $total = ProcessingJob::whereIn('status', ['completed', 'failed'])->count();
        
        if ($total === 0) {
            return 0;
        }

        $successful = ProcessingJob::where('status', 'completed')->count();
        
        return round(($successful / $total) * 100, 2);
    }

    /**
     * Calculate average processing time
     */
    private function calculateAverageProcessingTime(): ?float
    {
        $jobs = ProcessingJob::where('status', 'completed')
            ->whereNotNull('started_at')
            ->whereNotNull('completed_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, started_at, completed_at)) as avg_time')
            ->first();

        return $jobs->avg_time ? round($jobs->avg_time, 2) : null;
    }

    /**
     * Get jobs per day for the last 30 days
     */
    private function getJobsPerDay(): array
    {
        $thirtyDaysAgo = Carbon::now()->subDays(30);
        
        $jobs = ProcessingJob::where('created_at', '>=', $thirtyDaysAgo)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return $jobs->pluck('count', 'date')->toArray();
    }
}