@extends('admin.layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Edit Processing Job</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="{{ route('admin.processing-jobs.show', $processingJob) }}" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-eye"></i> View
            </a>
            <a href="{{ route('admin.processing-jobs.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Jobs
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Job Details</h5>
            </div>
            <div class="card-body">
                <form id="editJobForm">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="pending" {{ old('status', $processingJob->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="processing" {{ old('status', $processingJob->status) == 'processing' ? 'selected' : '' }}>Processing</option>
                            <option value="completed" {{ old('status', $processingJob->status) == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="failed" {{ old('status', $processingJob->status) == 'failed' ? 'selected' : '' }}>Failed</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="progress" class="form-label">Progress (%)</label>
                        <input type="number" class="form-control" id="progress" name="progress" 
                               value="{{ old('progress', $processingJob->progress) }}" min="0" max="100">
                    </div>
                    
                    <div class="mb-3">
                        <label for="error_message" class="form-label">Error Message</label>
                        <textarea class="form-control" id="error_message" name="error_message" rows="4">{{ old('error_message', $processingJob->error_message) }}</textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Update Job</button>
                    <a href="{{ route('admin.processing-jobs.index') }}" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Job Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-5"><strong>Job ID:</strong></div>
                    <div class="col-sm-7">{{ $processingJob->job_id }}</div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-5"><strong>Job Type:</strong></div>
                    <div class="col-sm-7">{{ $processingJob->job_type }}</div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-5"><strong>Current Status:</strong></div>
                    <div class="col-sm-7">
                        @switch($processingJob->status)
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
                                <span class="badge bg-info">{{ ucfirst($processingJob->status) }}</span>
                        @endswitch
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-5"><strong>Created:</strong></div>
                    <div class="col-sm-7">{{ $processingJob->created_at->format('M d, Y H:i:s') }}</div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Relationships</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong>Image:</strong>
                    @if($processingJob->image)
                        <div class="mt-2">
                            <a href="{{ route('admin.images.show', $processingJob->image) }}">{{ $processingJob->image->name }}</a>
                        </div>
                    @else
                        <div class="mt-2">
                            <span class="text-muted">No image assigned</span>
                        </div>
                    @endif
                </div>
                
                <div>
                    <strong>User:</strong>
                    @if($processingJob->user)
                        <div class="mt-2">
                            <a href="{{ route('admin.users.show', $processingJob->user) }}">{{ $processingJob->user->name }}</a>
                        </div>
                    @else
                        <div class="mt-2">
                            <span class="text-muted">Unknown user</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('editJobForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const jobId = {{ $processingJob->id }};
    
    fetch(`/admin/processing-jobs/${jobId}`, {
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
            window.location.href = data.job ? `/admin/processing-jobs/${data.job.id}` : '/admin/processing-jobs';
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while updating the job.');
    });
});
</script>
@endpush