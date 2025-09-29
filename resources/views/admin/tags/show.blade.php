@extends('admin.layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Tag Details</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="{{ route('admin.tags.edit', $tag) }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-pencil"></i> Edit
            </a>
            <a href="{{ route('admin.tags.index') }}" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-arrow-left"></i> Back to Tags
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Tag Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-3"><strong>Name:</strong></div>
                    <div class="col-sm-9">{{ $tag->name }}</div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-3"><strong>Slug:</strong></div>
                    <div class="col-sm-9">{{ $tag->slug }}</div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-3"><strong>Description:</strong></div>
                    <div class="col-sm-9">{{ $tag->description ?? 'N/A' }}</div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-3"><strong>Color:</strong></div>
                    <div class="col-sm-9">
                        <span class="badge" style="background-color: {{ $tag->color }}; color: {{ getContrastColor($tag->color) }};">
                            {{ $tag->color }}
                        </span>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-3"><strong>Created:</strong></div>
                    <div class="col-sm-9">{{ $tag->created_at->format('M d, Y H:i') }}</div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-3"><strong>Created By:</strong></div>
                    <div class="col-sm-9">
                        @if($tag->creator)
                            <a href="{{ route('admin.users.show', $tag->creator) }}">{{ $tag->creator->name }}</a>
                        @else
                            <span class="text-muted">N/A</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Recent Images with this Tag</h5>
            </div>
            <div class="card-body">
                @if($tag->images->count() > 0)
                    <div class="row">
                        @foreach($tag->images as $image)
                            <div class="col-md-3 mb-3">
                                <div class="card">
                                    @if($image->status === 'processed')
                                        <img src="{{ $image->url }}" class="card-img-top" alt="{{ $image->name }}" style="height: 100px; object-fit: cover;">
                                    @else
                                        <div class="bg-light border d-flex align-items-center justify-content-center" style="height: 100px;">
                                            <i class="bi bi-file-earmark-image"></i>
                                        </div>
                                    @endif
                                    <div class="card-body p-2">
                                        <h6 class="card-title mb-1">
                                            <a href="{{ route('admin.images.show', $image) }}">{{ Str::limit($image->name, 20) }}</a>
                                        </h6>
                                        <small class="text-muted">
                                            @if($image->project)
                                                {{ Str::limit($image->project->name, 15) }}
                                            @else
                                                No project
                                            @endif
                                        </small>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted">No images found with this tag.</p>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Statistics</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-12 mb-3">
                        <div class="stat-card stat-card-primary p-3 rounded">
                            <h3>{{ $stats['total_images'] }}</h3>
                            <p class="mb-0">Total Images</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Usage by Project</h5>
            </div>
            <div class="card-body">
                @if($stats['usage_by_project']->count() > 0)
                    <div class="list-group">
                        @foreach($stats['usage_by_project'] as $projectStat)
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between">
                                    <span>
                                        @if($projectStat->project)
                                            <a href="{{ route('admin.projects.show', $projectStat->project) }}">{{ $projectStat->project->name }}</a>
                                        @else
                                            Unknown Project
                                        @endif
                                    </span>
                                    <span class="badge bg-primary">{{ $projectStat->count }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted mb-0">No usage data available.</p>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('admin.tags.edit', $tag) }}" class="btn btn-primary">
                        <i class="bi bi-pencil"></i> Edit Tag
                    </a>
                    
                    <button type="button" class="btn btn-danger" onclick="deleteTag({{ $tag->id }})">
                        <i class="bi bi-trash"></i> Delete Tag
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function deleteTag(tagId) {
    if (confirm('Are you sure you want to delete this tag? This will remove it from all images.')) {
        fetch(`/admin/tags/${tagId}`, {
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
                window.location.href = '{{ route('admin.tags.index') }}';
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while deleting the tag.');
        });
    }
}

// Helper function to determine text color based on background color
function getContrastColor(hexColor) {
    // Convert hex to RGB
    const r = parseInt(hexColor.substr(1, 2), 16);
    const g = parseInt(hexColor.substr(3, 2), 16);
    const b = parseInt(hexColor.substr(5, 2), 16);
    
    // Calculate brightness
    const brightness = (r * 299 + g * 587 + b * 114) / 1000;
    
    // Return black or white based on brightness
    return brightness > 128 ? '#000000' : '#ffffff';
}
</script>
@endpush