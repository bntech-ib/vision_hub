@extends('admin.layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Access Keys</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="{{ route('admin.access-keys.create') }}" class="btn btn-sm btn-primary">
                <i class="bi bi-plus"></i> Create Access Key
            </a>
        </div>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.access-keys.index') }}" class="row g-3">
                    <div class="col-md-4">
                        <label for="search" class="form-label">Search</label>
                        <input type="text" class="form-control" id="search" name="search" value="{{ request('search') ?? '' }}" placeholder="Search access keys...">
                    </div>
                    <div class="col-md-3">
                        <label for="package" class="form-label">Package</label>
                        <select class="form-select" id="package" name="package">
                            <option value="">All Packages</option>
                            @foreach($packages as $package)
                                <option value="{{ $package->id }}" {{ (request('package') ?? '') == $package->id ? 'selected' : '' }}>
                                    {{ $package->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">All Statuses</option>
                            <option value="used" {{ (request('status') ?? '') == 'used' ? 'selected' : '' }}>Used</option>
                            <option value="unused" {{ (request('status') ?? '') == 'unused' ? 'selected' : '' }}>Unused</option>
                            <option value="expired" {{ (request('status') ?? '') == 'expired' ? 'selected' : '' }}>Expired</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">Filter</button>
                        <a href="{{ route('admin.access-keys.index') }}" class="btn btn-secondary">Clear</a>
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
                        <th>Access Key</th>
                        <th>Package</th>
                        <th>Created By</th>
                        <th>Status</th>
                        <th>Used By</th>
                        <th>Expires At</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($accessKeys as $accessKey)
                    <tr>
                        <td>
                            <code class="text-break">{{ $accessKey->key }}</code>
                        </td>
                        <td>
                            <a href="{{ route('admin.packages.show', $accessKey->package) }}">
                                {{ $accessKey->package->name }}
                            </a>
                        </td>
                        <td>
                            {{ $accessKey->creator->name }}
                        </td>
                        <td>
                            @if($accessKey->is_used)
                                <span class="badge bg-success">Used</span>
                            @elseif(!$accessKey->is_active)
                                <span class="badge bg-secondary">Inactive</span>
                            @elseif($accessKey->expires_at && $accessKey->expires_at->isPast())
                                <span class="badge bg-warning">Expired</span>
                            @else
                                <span class="badge bg-primary">Available</span>
                            @endif
                        </td>
                        <td>
                            @if($accessKey->user)
                                <a href="{{ route('admin.users.show', $accessKey->user) }}">
                                    {{ $accessKey->user->name }}
                                </a>
                            @else
                                <span class="text-muted">Not used</span>
                            @endif
                        </td>
                        <td>
                            @if($accessKey->expires_at)
                                {{ $accessKey->expires_at->format('M d, Y') }}
                                @if($accessKey->expires_at->isPast())
                                    <br><small class="text-danger">(Expired)</small>
                                @endif
                            @else
                                <span class="text-muted">Never</span>
                            @endif
                        </td>
                        <td>
                            {{ $accessKey->created_at?->format('M d, Y') ?? 'N/A' }}
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="{{ route('admin.access-keys.show', $accessKey) }}" class="btn btn-outline-primary" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @if(!$accessKey->is_used)
                                    @if($accessKey->is_active)
                                        <button type="button" class="btn btn-outline-warning" title="Deactivate" 
                                                onclick="deactivateAccessKey('{{ $accessKey->id }}')">
                                            <i class="bi bi-pause"></i>
                                        </button>
                                    @else
                                        <button type="button" class="btn btn-outline-success" title="Activate" 
                                                onclick="activateAccessKey('{{ $accessKey->id }}')">
                                            <i class="bi bi-play"></i>
                                        </button>
                                    @endif
                                    <button type="button" class="btn btn-outline-danger" title="Delete" 
                                            onclick="deleteAccessKey('{{ $accessKey->id }}')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center">No access keys found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="d-flex justify-content-between align-items-center">
            <div>
                Showing {{ $accessKeys->firstItem() ?? 0 }} to {{ $accessKeys->lastItem() ?? 0 }} of {{ $accessKeys->total() ?? 0 }} access keys
            </div>
            <div>
                {{ $accessKeys->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function activateAccessKey(accessKeyId) {
    if (confirm('Are you sure you want to activate this access key?')) {
        fetch(`/admin/access-keys/${accessKeyId}/activate`, {
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
            alert('An error occurred while activating the access key.');
        });
    }
}

function deactivateAccessKey(accessKeyId) {
    if (confirm('Are you sure you want to deactivate this access key?')) {
        fetch(`/admin/access-keys/${accessKeyId}/deactivate`, {
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
            alert('An error occurred while deactivating the access key.');
        });
    }
}

function deleteAccessKey(accessKeyId) {
    if (confirm('Are you sure you want to delete this access key?')) {
        fetch(`/admin/access-keys/${accessKeyId}`, {
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
            alert('An error occurred while deleting the access key.');
        });
    }
}
</script>
@endpush