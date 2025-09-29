@extends('admin.layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Image Details</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="{{ route('admin.images.edit', $image) }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-pencil"></i> Edit
            </a>
            <a href="{{ route('admin.images.index') }}" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-arrow-left"></i> Back to Images
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Image Preview</h5>
            </div>
            <div class="card-body text-center">
                @if($image->status === 'processed')
                    <img src="{{ $image->url }}" alt="{{ $image->name }}" class="img-fluid rounded">
                @else
                    <div class="bg-light border d-flex align-items-center justify-content-center" style="height: 300px;">
                        <div>
                            <i class="bi bi-file-earmark-image" style="font-size: 3rem;"></i>
                            <p class="mt-2">Image not available for preview</p>
                            <p class="text-muted">Status: {{ ucfirst($image->status) }}</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Image Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-3"><strong>ID:</strong></div>
                    <div class="col-sm-9">{{ $image->id }}</div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-3"><strong>Name:</strong></div>
                    <div class="col-sm-9">{{ $image->name }}</div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-3"><strong>Original Filename:</strong></div>
                    <div class="col-sm-9">{{ $image->original_filename }}</div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-3"><strong>File Path:</strong></div>
                    <div class="col-sm-9">{{ $image->file_path }}</div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-3"><strong>File Hash:</strong></div>
                    <div class="col-sm-9">{{ $image->file_hash }}</div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-3"><strong>MIME Type:</strong></div>
                    <div class="col-sm-9">{{ $image->mime_type }}</div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-3"><strong>File Size:</strong></div>
                    <div class="col-sm-9">{{ $image->formatted_size }} ({{ number_format($image->file_size) }} bytes)</div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-3"><strong>Dimensions:</strong></div>
                    <div class="col-sm-9">{{ $image->width }} x {{ $image->height }} pixels</div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-3"><strong>Status:</strong></div>
                    <div class="col-sm-9">
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
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-3"><strong>Processing Notes:</strong></div>
                    <div class="col-sm-9">{{ $image->processing_notes ?? 'N/A' }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Relationships</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong>Project:</strong>
                    @if($image->project)
                        <div class="mt-2">
                            <a href="{{ route('admin.projects.show', $image->project) }}">{{ $image->project->name }}</a>
                        </div>
                    @else
                        <div class="mt-2">
                            <span class="text-muted">No project assigned</span>
                        </div>
                    @endif
                </div>
                
                <div class="mb-3">
                    <strong>Uploader:</strong>
                    @if($image->uploader)
                        <div class="mt-2">
                            <a href="{{ route('admin.users.show', $image->uploader) }}">{{ $image->uploader->name }}</a>
                        </div>
                    @else
                        <div class="mt-2">
                            <span class="text-muted">Unknown uploader</span>
                        </div>
                    @endif
                </div>
                
                <div class="mb-3">
                    <strong>Tags:</strong>
                    @if($image->tags->count() > 0)
                        <div class="mt-2">
                            @foreach($image->tags as $tag)
                                <span class="badge bg-primary me-1">{{ $tag->name }}</span>
                            @endforeach
                        </div>
                    @else
                        <div class="mt-2">
                            <span class="text-muted">No tags assigned</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    @if($image->status !== 'flagged')
                        <button type="button" class="btn btn-warning" onclick="flagImage({{ $image->id }})">
                            <i class="bi bi-flag"></i> Flag Image
                        </button>
                    @endif
                    
                    <a href="{{ route('admin.images.download', $image) }}" class="btn btn-info">
                        <i class="bi bi-download"></i> Download
                    </a>
                    
                    <button type="button" class="btn btn-danger" onclick="deleteImage({{ $image->id }})">
                        <i class="bi bi-trash"></i> Delete Image
                    </button>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Processing Jobs</h5>
            </div>
            <div class="card-body">
                <p class="mb-2"><strong>Total Jobs:</strong> {{ $image->processing_jobs_count }}</p>
                
                @if($image->processingJobs->count() > 0)
                    <div class="list-group">
                        @foreach($image->processingJobs->take(5) as $job)
                            <a href="#" class="list-group-item list-group-item-action">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1">{{ $job->type }}</h6>
                                    @switch($job->status)
                                        @case('pending')
                                            <span class="badge bg-secondary">Pending</span>
                                            @break
                                        @case('processing')
                                            <span class="badge bg-warning">Processing</span>
                                            @break
                                        @case('completed')
                                            <span class="badge bg-success">Completed</span>
                                            @break
                                        @case('failed')
                                            <span class="badge bg-danger">Failed</span>
                                            @break
                                        @default
                                            <span class="badge bg-info">{{ ucfirst($job->status) }}</span>
                                    @endswitch
                                </div>
                                <small>{{ $job->created_at->format('M d, Y H:i') }}</small>
                            </a>
                        @endforeach
                    </div>
                    @if($image->processingJobs->count() > 5)
                        <div class="mt-2 text-center">
                            <a href="#" class="btn btn-sm btn-outline-primary">View All Jobs</a>
                        </div>
                    @endif
                @else
                    <p class="text-muted mb-0">No processing jobs found for this image.</p>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function flagImage(imageId) {
    const reason = prompt('Enter reason for flagging this image:');
    if (reason !== null) {
        fetch(`/admin/images/${imageId}/moderate`, {
            method: 'PUT',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                status: 'flagged',
                notes: reason
            })
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
            alert('An error occurred while flagging the image.');
        });
    }
}

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
                window.location.href = '{{ route('admin.images.index') }}';
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