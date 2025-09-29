@extends('admin.layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">User Management</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="{{ route('admin.users.create') }}" class="btn btn-sm btn-primary">
                <i class="bi bi-plus"></i> Add User
            </a>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="row mb-3">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.users.index') }}">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label for="search" class="form-label">Search</label>
                            <input type="text" class="form-control" id="search" name="search" 
                                   value="{{ request('search') }}" placeholder="Name, email, username...">
                        </div>
                        <div class="col-md-3">
                            <label for="package" class="form-label">Package</label>
                            <select class="form-select" id="package" name="package">
                                <option value="">All Packages</option>
                                @foreach($packages as $package)
                                    <option value="{{ $package->id }}" 
                                            {{ request('package') == $package->id ? 'selected' : '' }}>
                                        {{ $package->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">All Users</option>
                                <option value="admin" {{ request('status') == 'admin' ? 'selected' : '' }}>Admin Users</option>
                                <option value="premium" {{ request('status') == 'premium' ? 'selected' : '' }}>Premium Users</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-outline-primary">
                                <i class="bi bi-search"></i> Filter
                            </button>
                            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-clockwise"></i>
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Users Table -->
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Users ({{ $users->total() }})</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>User</th>
                                <th>Email</th>
                                <th>Package</th>
                                <th>Wallet Balance</th>
                                <th>Status</th>
                                <th>Joined</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0">
                                                @if($user->profile_image)
                                                    <img src="{{ Storage::url($user->profile_image) }}" 
                                                         alt="{{ $user->name }}" class="rounded-circle" width="32" height="32">
                                                @else
                                                    <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center" 
                                                         style="width: 32px; height: 32px;">
                                                        <i class="bi bi-person text-white"></i>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="flex-grow-1 ms-2">
                                                <div class="fw-bold">{{ $user->name }}</div>
                                                @if($user->username)
                                                    <small class="text-muted">@ {{$user->username }}</small>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        {{ $user->email }}
                                        @if($user->email_verified_at)
                                            <i class="bi bi-check-circle-fill text-success ms-1" title="Verified"></i>
                                        @else
                                            <i class="bi bi-exclamation-circle-fill text-warning ms-1" title="Not Verified"></i>
                                        @endif
                                    </td>
                                    <td>
                                        @if($user->currentPackage)
                                            <span class="badge bg-primary">{{ $user->currentPackage->name }}</span>
                                            @if($user->package_expires_at && $user->package_expires_at->isPast())
                                                <br><small class="text-danger">Expired</small>
                                            @elseif($user->package_expires_at)
                                                <br><small class="text-muted">Expires {{ $user->package_expires_at->format('M d, Y') }}</small>
                                            @endif
                                        @else
                                            <span class="badge bg-secondary">Free</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="fw-bold">${{ number_format($user->wallet_balance, 2) }}</span>
                                    </td>
                                    <td>
                                        @if($user->is_admin)
                                            <span class="badge bg-danger">Admin</span>
                                        @else
                                            <span class="badge bg-success">User</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small>{{ $user->created_at->format('M d, Y') }}</small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('admin.users.show', $user) }}" 
                                               class="btn btn-outline-primary">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.users.edit', $user) }}" 
                                               class="btn btn-outline-secondary">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            @if(!$user->is_admin || App\Models\User::where('is_admin', true)->count() > 1)
                                                <button type="button" class="btn btn-outline-danger" 
                                                        onclick="confirmDelete({{ $user->id }})">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <i class="bi bi-people display-1 text-muted"></i>
                                        <p class="text-muted mt-2">No users found</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($users->hasPages())
                <div class="card-footer">
                    {{ $users->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this user? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete User</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function confirmDelete(userId) {
    const form = document.getElementById('deleteForm');
    form.action = `/admin/users/${userId}`;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>
@endpush