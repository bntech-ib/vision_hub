@extends('admin.layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Project Details</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="{{ route('admin.projects.edit', $project) }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-pencil"></i> Edit
            </a>
            <a href="{{ route('admin.projects.index') }}" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-arrow-left"></i> Back to Projects
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Project Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-3"><strong>ID:</strong></div>
                    <div class="col-sm-9">{{ $project->id }}</div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-3"><strong>Name:</strong></div>
                    <div class="col-sm-9">{{ $project->name }}</div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-3"><strong>Description:</strong></div>
                    <div class="col-sm-9">{{ $project->description ?? 'N/A' }}</div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-3"><strong>Status:</strong></div>
                    <div class="col-sm-9">
                        @switch($project->status)
                            @case('active')
                                <span class="badge bg-success">Active</span>
                                @break
                            @case('completed')
                                <span class="badge bg-info">Completed</span>
                                @break
                            @case('archived')
                                <span class="badge bg-secondary">Archived</span>
                                @break
                            @case('suspended')
                                <span class="badge bg-warning">Suspended</span>
                                @break
                            @default
                                <span class="badge bg-dark">{{ ucfirst($project->status) }}</span>
                        @endswitch
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-3"><strong>User:</strong></div>
                    <div class="col-sm-9">
                        @if($project->user)
                            <a href="{{ route('admin.users.show', $project->user) }}">{{ $project->user->name }}</a>
                        @else
                            <span class="text-muted">N/A</span>
                        @endif
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-3"><strong>Created:</strong></div>
                    <div class="col-sm-9">{{ $project->created_at->format('M d, Y H:i') }}</div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-3"><strong>Completed:</strong></div>
                    <div class="col-sm-9">
                        @if($project->completed_at)
                            {{ $project->completed_at->format('M d, Y H:i') }}
                        @else
                            <span class="text-muted">Not completed</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Settings</h5>
            </div>
            <div class="card-body">
                @if($project->settings)
                    <pre class="bg-light p-3">{{ json_encode($project->settings, JSON_PRETTY_PRINT) }}</pre>
                @else
                    <p class="text-muted">No settings configured.</p>
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
                    <div class="col-6 mb-3">
                        <div class="stat-card stat-card-primary p-3 rounded">
                            <h3>{{ $stats['total_images'] }}</h3>
                            <p class="mb-0">Total Images</p>
                        </div>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="stat-card stat-card-success p-3 rounded">
                            <h3>{{ $stats['processed_images'] }}</h3>
                            <p class="mb-0">Processed</p>
                        </div>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="stat-card stat-card-info p-3 rounded">
                            <h3>{{ $stats['total_jobs'] }}</h3>
                            <p class="mb-0">Total Jobs</p>
                        </div>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="stat-card stat-card-warning p-3 rounded">
                            <h3>{{ $stats['completed_jobs'] }}</h3>
                            <p class="mb-0">Completed Jobs</p>
                        </div>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="stat-card stat-card-danger p-3 rounded">
                            <h3>{{ $stats['failed_jobs'] }}</h3>
                            <p class="mb-0">Failed Jobs</p>
                        </div>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="stat-card stat-card-secondary p-3 rounded">
                            <h3>{{ number_format($stats['storage_used'] / 1024 / 1024, 2) }} MB</h3>
                            <p class="mb-0">Storage Used</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    @if($project->status !== 'suspended')
                        <button type="button" class="btn btn-warning" onclick="suspendProject({{ $project->id }})">
                            <i class="bi bi-pause"></i> Suspend Project
                        </button>
                    @else
                        <button type="button" class="btn btn-success" onclick="activateProject({{ $project->id }})">
                            <i class="bi bi-play"></i> Activate Project
                        </button>
                    @endif
                    
                    <a href="{{ route('admin.projects.images', $project) }}" class="btn btn-info">
                        <i class="bi bi-images"></i> View Images
                    </a>
                    
                    <a href="{{ route('admin.projects.jobs', $project) }}" class="btn btn-info">
                        <i class="bi bi-gear"></i> View Processing Jobs
                    </a>
                    
                    <button type="button" class="btn btn-danger" onclick="deleteProject({{ $project->id }})">
                        <i class="bi bi-trash"></i> Archive Project
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function suspendProject(projectId) {
    if (confirm('Are you sure you want to suspend this project?')) {
        fetch(`/admin/projects/${projectId}/suspend`, {
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
            alert('An error occurred while suspending the project.');
        });
    }
}

function activateProject(projectId) {
    if (confirm('Are you sure you want to activate this project?')) {
        fetch(`/admin/projects/${projectId}/activate`, {
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
            alert('An error occurred while activating the project.');
        });
    }
}

function deleteProject(projectId) {
    if (confirm('Are you sure you want to archive this project?')) {
        fetch(`/admin/projects/${projectId}`, {
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
                window.location.href = '{{ route('admin.projects.index') }}';
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while archiving the project.');
        });
    }
}
</script>
@endpush