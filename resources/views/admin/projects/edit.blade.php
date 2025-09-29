@extends('admin.layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Edit Project</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="{{ route('admin.projects.show', $project) }}" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-eye"></i> View
            </a>
            <a href="{{ route('admin.projects.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Projects
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Project Details</h5>
            </div>
            <div class="card-body">
                <form id="editProjectForm">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $project->name) }}" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="4">{{ old('description', $project->description) }}</textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="user_id" class="form-label">User</label>
                        <select class="form-select" id="user_id" name="user_id" required>
                            <option value="">Select User</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ old('user_id', $project->user_id) == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }} ({{ $user->email }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="active" {{ old('status', $project->status) == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="completed" {{ old('status', $project->status) == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="archived" {{ old('status', $project->status) == 'archived' ? 'selected' : '' }}>Archived</option>
                            <option value="suspended" {{ old('status', $project->status) == 'suspended' ? 'selected' : '' }}>Suspended</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="settings" class="form-label">Settings (JSON)</label>
                        <textarea class="form-control" id="settings" name="settings" rows="6">{{ old('settings', json_encode($project->settings, JSON_PRETTY_PRINT)) }}</textarea>
                        <div class="form-text">Enter valid JSON format for project settings.</div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Update Project</button>
                    <a href="{{ route('admin.projects.index') }}" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Project Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-4"><strong>ID:</strong></div>
                    <div class="col-sm-8">{{ $project->id }}</div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-4"><strong>Created:</strong></div>
                    <div class="col-sm-8">{{ $project->created_at->format('M d, Y H:i') }}</div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-4"><strong>Completed:</strong></div>
                    <div class="col-sm-8">
                        @if($project->completed_at)
                            {{ $project->completed_at->format('M d, Y H:i') }}
                        @else
                            <span class="text-muted">Not completed</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('editProjectForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const projectId = {{ $project->id }};
    
    fetch(`/admin/projects/${projectId}`, {
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
            window.location.href = data.project ? `/admin/projects/${data.project.id}` : '/admin/projects';
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while updating the project.');
    });
});
</script>
@endpush