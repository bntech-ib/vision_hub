@extends('admin.layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Images</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-outline-secondary">Export</button>
            <button type="button" class="btn btn-sm btn-outline-secondary">Bulk Actions</button>
        </div>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.images.index') }}" class="row g-3">
                    <div class="col-md-3">
                        <label for="search" class="form-label">Search</label>
                        <input type="text" class="form-control" id="search" name="search" value="{{ request('search') }}" placeholder="Search images...">
                    </div>
                    <div class="col-md-2">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">All Statuses</option>
                            <option value="uploaded" {{ request('status') == 'uploaded' ? 'selected' : '' }}>Uploaded</option>
                            <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>Processing</option>
                            <option value="processed" {{ request('status') == 'processed' ? 'selected' : '' }}>Processed</option>
                            <option value="error" {{ request('status') == 'error' ? 'selected' : '' }}>Error</option>
                            <option value="flagged" {{ request('status') == 'flagged' ? 'selected' : '' }}>Flagged</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="project_id" class="form-label">Project</label>
                        <select class="form-select" id="project_id" name="project_id">
                            <option value="">All Projects</option>
                            @foreach($projects as $project)
                                <option value="{{ $project->id }}" {{ request('project_id') == $project->id ? 'selected' : '' }}>
                                    {{ $project->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="mime_type" class="form-label">Type</label>
                        <select class="form-select" id="mime_type" name="mime_type">
                            <option value="">All Types</option>
                            <option value="image/jpeg" {{ request('mime_type') == 'image/jpeg' ? 'selected' : '' }}>JPEG</option>
                            <option value="image/png" {{ request('mime_type') == 'image/png' ? 'selected' : '' }}>PNG</option>
                            <option value="image/gif" {{ request('mime_type') == 'image/gif' ? 'selected' : '' }}>GIF</option>
                            <option value="image/webp" {{ request('mime_type') == 'image/webp' ? 'selected' : '' }}>WebP</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">Filter</button>
                        <a href="{{ route('admin.images.index') }}" class="btn btn-secondary">Clear</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Project</th>
                        <th>Uploader</th>
                        <th>Status</th>
                        <th>Size</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($images as $image)
                    <tr>
                        <td>{{ $image->id }}</td>
                        <td>
                            @if($image->status === 'processed')
                                <img src="{{ $image->url }}" alt="{{ $image->name }}" width="50" height="50" class="img-thumbnail">
                            @else
                                <div class="bg-light border d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                    <i class="bi bi-file-earmark-image"></i>
                                </div>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('admin.images.show', $image) }}">{{ $image->name }}</a>
                            <small class="text-muted d-block">{{ $image->original_filename }}</small>
                        </td>
                        <td>
                            @if($image->project)
                                <a href="{{ route('admin.projects.show', $image->project) }}">{{ $image->project->name }}</a>
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </td>
                        <td>
                            @if($image->uploader)
                                <a href="{{ route('admin.users.show', $image->uploader) }}">{{ $image->uploader->name }}</a>
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </td>
                        <td>
                            @switch($image->status)
                                @case('uploaded')
                                    <span class="badge bg-secondary">Uploaded</span>
                                    @break
                                @case('processing')
                                    <span class="badge bg-warning">Processing</span>
                                    @break
                                @case('processed')
                                    <span class="badge bg-success">Processed</span>
                                    @break
                                @case('error')
                                    <span class="badge bg-danger">Error</span>
                                    @break
                                @case('flagged')
                                    <span class="badge bg-dark">Flagged</span>
                                    @break
                                @default
                                    <span class="badge bg-info">{{ ucfirst($image->status) }}</span>
                            @endswitch
                        </td>
                        <td>{{ $image->formatted_size }}</td>
                        <td>{{ $image->created_at->format('M d, Y') }}</td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="{{ route('admin.images.show', $image) }}" class="btn btn-outline-primary" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('admin.images.edit', $image) }}" class="btn btn-outline-secondary" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button type="button" class="btn btn-outline-danger" title="Delete" 
                                        onclick="deleteImage({{ $image->id }})">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center">No images found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="d-flex justify-content-between align-items-center">
            <div>
                Showing {{ $images->firstItem() }} to {{ $images->lastItem() }} of {{ $images->total() }} images
            </div>
            <div>
                {{ $images->links() }}
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function deleteImage(imageId) {
    if (confirm('Are you sure you want to delete this image?')) {
        fetch(`/admin/images/${imageId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while deleting the image.');
        });
    }
}
</script>
@endpush