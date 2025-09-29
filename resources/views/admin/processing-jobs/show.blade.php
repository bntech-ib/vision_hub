@extends('admin.layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Processing Job Details</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="{{ route('admin.processing-jobs.edit', $processingJob) }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-pencil"></i> Edit
            </a>
            <a href="{{ route('admin.processing-jobs.index') }}" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-arrow-left"></i> Back to Jobs
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Job Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-3"><strong>Job ID:</strong></div>
                    <div class="col-sm-9">{{ $processingJob->job_id }}</div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-3"><strong>Job Type:</strong></div>
                    <div class="col-sm-9">{{ $processingJob->job_type }}</div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-3"><strong>Status:</strong></div>
                    <div class="col-sm-9">
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
                    <div class="col-sm-3"><strong>Progress:</strong></div>
                    <div class="col-sm-9">
                        @if($processingJob->status === 'processing')
                            <div class="progress mb-2">
                                <div class="progress-bar" role="progressbar" style="width: {{ $processingJob->progress }}%" 
                                     aria-valuenow="{{ $processingJob->progress }}" aria-valuemin="0" aria-valuemax="100">
                                    {{ $processingJob->progress }}%
                                </div>
                            </div>
                        @elseif($processingJob->status === 'completed')
                            <span class="text-success">100% Complete</span>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-3"><strong>Created:</strong></div>
                    <div class="col-sm-9">{{ $processingJob->created_at->format('M d, Y H:i:s') }}</div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-3"><strong>Started:</strong></div>
                    <div class="col-sm-9">
                        @if($processingJob->started_at)
                            {{ $processingJob->started_at->format('M d, Y H:i:s') }}
                        @else
                            <span class="text-muted">Not started</span>
                        @endif
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-3"><strong>Completed:</strong></div>
                    <div class="col-sm-9">
                        @if($processingJob->completed_at)
                            {{ $processingJob->completed_at->format('M d, Y H:i:s') }}
                        @else
                            <span class="text-muted">Not completed</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Parameters</h5>
            </div>
            <div class="card-body">
                @if($processingJob->parameters)
                    <pre class="bg-light p-3">{{ json_encode($processingJob->parameters, JSON_PRETTY_PRINT) }}</pre>
                @else
                    <p class="text-muted">No parameters configured.</p>
                @endif
            </div>
        </div>

        @if($processingJob->status === 'failed')
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Error Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-3"><strong>Error Message:</strong></div>
                    <div class="col-sm-9">
                        @if($processingJob->error_message)
                            <div class="alert alert-danger mb-0">
                                {{ $processingJob->error_message }}
                            </div>
                        @else
                            <span class="text-muted">No error message provided.</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if($processingJob->status === 'completed' && $processingJob->result)
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Result</h5>
            </div>
            <div class="card-body">
                <pre class="bg-light p-3">{{ json_encode($processingJob->result, JSON_PRETTY_PRINT) }}</pre>
            </div>
        </div>
        @endif
    </div>

    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Relationships</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong>Image:</strong>
                    @if($processingJob->image)
                        <div class="mt-2">
                            <a href="{{ route('admin.images.show', $processingJob->image) }}">{{ $processingJob->image->name }}</a>
                            <small class="d-block text-muted">{{ $processingJob->image->original_filename }}</small>
                        </div>
                    @else
                        <div class="mt-2">
                            <span class="text-muted">No image assigned</span>
                        </div>
                    @endif
                </div>
                
                <div class="mb-3">
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

        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    @if($processingJob->status === 'failed')
                        <button type="button" class="btn btn-success" onclick="retryJob({{ $processingJob->id }})">
                            <i class="bi bi-arrow-repeat"></i> Retry Job
                        </button>
                    @endif
                    
                    @if(in_array($processingJob->status, ['pending', 'processing']))
                        <button type="button" class="btn btn-warning" onclick="cancelJob({{ $processingJob->id }})">
                            <i class="bi bi-x-circle"></i> Cancel Job
                        </button>
                    @endif
                    
                    <button type="button" class="btn btn-danger" onclick="deleteJob({{ $processingJob->id }})">
                        <i class="bi bi-trash"></i> Delete Job
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function retryJob(jobId) {
    if (confirm('Are you sure you want to retry this processing job?')) {
        fetch(`/admin/processing-jobs/${jobId}/retry`, {
            method: 'PUT',
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
            alert('An error occurred while retrying the job.');
        });
    }
}

function cancelJob(jobId) {
    if (confirm('Are you sure you want to cancel this processing job?')) {
        fetch(`/admin/processing-jobs/${jobId}/cancel`, {
            method: 'PUT',
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
            alert('An error occurred while cancelling the job.');
        });
    }
}

function deleteJob(jobId) {
    if (confirm('Are you sure you want to delete this processing job?')) {
        fetch(`/admin/processing-jobs/${jobId}`, {
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
                window.location.href = '{{ route('admin.processing-jobs.index') }}';
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while deleting the job.');
        });
    }
}
</script>
@endpush