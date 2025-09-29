<?php

namespace App\Http\Controllers;

use App\Models\Image;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ImageController extends Controller
{
    /**
     * Display a listing of images for a project
     */
    public function index(Request $request, Project $project): JsonResponse
    {
        $this->authorizeProject($project);

        $query = $project->images()->with('tags', 'uploader:id,name');
        
        // Filter by status if provided
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        // Filter by tags if provided
        if ($request->has('tags')) {
            $tags = explode(',', $request->tags);
            $query->whereHas('tags', function ($q) use ($tags) {
                $q->whereIn('slug', $tags);
            });
        }

        $images = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $images,
        ]);
    }

    /**
     * Store newly uploaded images
     */
    public function store(Request $request, Project $project): JsonResponse
    {
        $this->authorizeProject($project);

        $validated = $request->validate([
            'images' => 'required|array|max:10',
            'images.*' => 'required|file|image|mimes:jpeg,png,jpg,gif,webp|max:10240', // 10MB max
            'names' => 'nullable|array',
            'names.*' => 'nullable|string|max:255',
        ]);

        $uploadedImages = [];

        foreach ($validated['images'] as $index => $file) {
            $image = $this->processUpload($file, $project, $validated['names'][$index] ?? null);
            $uploadedImages[] = $image;
        }

        return response()->json([
            'success' => true,
            'message' => count($uploadedImages) . ' image(s) uploaded successfully',
            'data' => $uploadedImages,
        ], 201);
    }

    /**
     * Display the specified image
     */
    public function show(Project $project, Image $image): JsonResponse
    {
        $this->authorizeProject($project);
        $this->authorizeImage($image, $project);

        $image->load(['tags', 'uploader:id,name', 'processingJobs' => function ($query) {
            $query->latest();
        }]);

        return response()->json([
            'success' => true,
            'data' => $image,
        ]);
    }

    /**
     * Update the specified image
     */
    public function update(Request $request, Project $project, Image $image): JsonResponse
    {
        $this->authorizeProject($project);
        $this->authorizeImage($image, $project);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'processing_notes' => 'nullable|string|max:1000',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
        ]);

        if (isset($validated['tags'])) {
            $image->tags()->sync($validated['tags']);
            unset($validated['tags']);
        }

        $image->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Image updated successfully',
            'data' => $image->fresh()->load(['tags', 'uploader:id,name']),
        ]);
    }

    /**
     * Remove the specified image
     */
    public function destroy(Project $project, Image $image): JsonResponse
    {
        $this->authorizeProject($project);
        $this->authorizeImage($image, $project);

        // Delete the file from storage
        Storage::delete($image->file_path);

        // Delete the image record
        $image->delete();

        return response()->json([
            'success' => true,
            'message' => 'Image deleted successfully',
        ]);
    }

    /**
     * Download the original image
     */
    public function download(Project $project, Image $image)
    {
        $this->authorizeProject($project);
        $this->authorizeImage($image, $project);

        if (!Storage::exists($image->file_path)) {
            return response()->json([
                'success' => false,
                'message' => 'File not found',
            ], 404);
        }

        return Storage::download($image->file_path, $image->original_filename);
    }

    /**
     * Process uploaded file
     */
    private function processUpload(UploadedFile $file, Project $project, ?string $name = null): Image
    {
        // Generate file hash for deduplication
        $fileHash = hash_file('sha256', $file->getRealPath());
        
        // Check for duplicate
        $existingImage = Image::where('file_hash', $fileHash)->first();
        if ($existingImage) {
            throw ValidationException::withMessages([
                'images' => 'Duplicate file detected: ' . $file->getClientOriginalName(),
            ]);
        }

        // Get image dimensions
        $dimensions = getimagesize($file->getRealPath());
        
        // Store the file
        $filePath = $file->store('images/' . date('Y/m'), 'public');
        
        // Create image record
        return Image::create([
            'name' => $name ?: pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
            'original_filename' => $file->getClientOriginalName(),
            'file_path' => $filePath,
            'file_hash' => $fileHash,
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'width' => $dimensions[0] ?? null,
            'height' => $dimensions[1] ?? null,
            'project_id' => $project->id,
            'uploaded_by' => Auth::id(),
            'metadata' => [
                'original_name' => $file->getClientOriginalName(),
                'uploaded_at' => now()->toISOString(),
            ],
        ]);
    }

    /**
     * Authorize project access
     */
    private function authorizeProject(Project $project): void
    {
        if ($project->user_id !== Auth::id()) {
            throw ValidationException::withMessages([
                'project' => 'You do not have permission to access this project.',
            ]);
        }
    }

    /**
     * Authorize image belongs to project
     */
    private function authorizeImage(Image $image, Project $project): void
    {
        if ($image->project_id !== $project->id) {
            throw ValidationException::withMessages([
                'image' => 'Image does not belong to this project.',
            ]);
        }
    }
}