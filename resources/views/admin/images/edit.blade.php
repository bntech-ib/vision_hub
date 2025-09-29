@extends('admin.layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Edit Image</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="{{ route('admin.images.show', $image) }}" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-eye"></i> View
            </a>
            <a href="{{ route('admin.images.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Images
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Image Details</h5>
            </div>
            <div class="card-body">
                <form id="editImageForm">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $image->name) }}" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="project_id" class="form-label">Project</label>
                        <select class="form-select" id="project_id" name="project_id" required>
                            <option value="">Select Project</option>
                            @foreach($projects as $project)
                                <option value="{{ $project->id }}" {{ old('project_id', $image->project_id) == $project->id ? 'selected' : '' }}>
                                    {{ $project->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="uploaded" {{ old('status', $image->status) == 'uploaded' ? 'selected' : '' }}>Uploaded</option>
                            <option value="processing" {{ old('status', $image->status) == 'processing' ? 'selected' : '' }}>Processing</option>
                            <option value="processed" {{ old('status', $image->status) == 'processed' ? 'selected' : '' }}>Processed</option>
                            <option value="error" {{ old('status', $image->status) == 'error' ? 'selected' : '' }}>Error</option>
                            <option value="flagged" {{ old('status', $image->status) == 'flagged' ? 'selected' : '' }}>Flagged</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="processing_notes" class="form-label">Processing Notes</label>
                        <textarea class="form-control" id="processing_notes" name="processing_notes" rows="4">{{ old('processing_notes', $image->processing_notes) }}</textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Update Image</button>
                    <a href="{{ route('admin.images.index') }}" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Image Preview</h5>
            </div>
            <div class="card-body text-center">
                @if($image->status === 'processed')
                    <img src="{{ $image->url }}" alt="{{ $image->name }}" class="img-fluid rounded">
                @else
                    <div class="bg-light border d-flex align-items-center justify-content-center" style="height: 200px;">
                        <div>
                            <i class="bi bi-file-earmark-image" style="font-size: 3rem;"></i>
                            <p class="mt-2">Image not available for preview</p>
                            <p class="text-muted">Status: {{ ucfirst($image->status) }}</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Image Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-5"><strong>ID:</strong></div>
                    <div class="col-sm-7">{{ $image->id }}</div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-5"><strong>Original Filename:</strong></div>
                    <div class="col-sm-7">{{ $image->original_filename }}</div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-5"><strong>File Size:</strong></div>
                    <div class="col-sm-7">{{ $image->formatted_size }}</div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-5"><strong>Dimensions:</strong></div>
                    <div class="col-sm-7">{{ $image->width }} x {{ $image->height }} pixels</div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-5"><strong>MIME Type:</strong></div>
                    <div class="col-sm-7">{{ $image->mime_type }}</div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-5"><strong>Created:</strong></div>
                    <div class="col-sm-7">{{ $image->created_at->format('M d, Y H:i') }}</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('editImageForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const imageId = {{ $image->id }};
    
    fetch(`/admin/images/${imageId}`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-HTTP-Method-Override': 'PUT'
        },
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            window.location.href = data.image ? `/admin/images/${data.image.id}` : '/admin/images';
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while updating the image.');
    });
});
</script>
@endpush