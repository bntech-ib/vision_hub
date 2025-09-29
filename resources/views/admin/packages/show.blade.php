@extends('admin.layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Package Details</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="{{ route('admin.packages.edit', $package) }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-pencil"></i> Edit
            </a>
            <a href="{{ route('admin.packages.index') }}" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-arrow-left"></i> Back to Packages
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Package Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-3"><strong>Name:</strong></div>
                    <div class="col-sm-9">{{ $package->name }}</div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-3"><strong>Description:</strong></div>
                    <div class="col-sm-9">{{ $package->description ?? 'N/A' }}</div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-3"><strong>Price:</strong></div>
                    <div class="col-sm-9">${{ number_format($package->price, 2) }}</div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-3"><strong>Duration:</strong></div>
                    <div class="col-sm-9">{{ $package->duration_days ? $package->duration_days . ' days' : 'Lifetime' }}</div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-3"><strong>Status:</strong></div>
                    <div class="col-sm-9">
                        @if($package->is_active)
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-secondary">Inactive</span>
                        @endif
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-3"><strong>Created:</strong></div>
                    <div class="col-sm-9">{{ $package->created_at->format('M d, Y H:i') }}</div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-3"><strong>Updated:</strong></div>
                    <div class="col-sm-9">{{ $package->updated_at->format('M d, Y H:i') }}</div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Features</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-6">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="marketplace_access" disabled {{ $package->marketplace_access ? 'checked' : '' }}>
                            <label class="form-check-label" for="marketplace_access">Marketplace Access</label>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="brain_teaser_access" disabled {{ $package->brain_teaser_access ? 'checked' : '' }}>
                            <label class="form-check-label" for="brain_teaser_access">Brain Teaser Access</label>
                        </div>
                    </div>
                </div>
                
                <hr>
                
                <div class="row">
                    <div class="col-sm-6">
                        <div class="row">
                            <div class="col-sm-6"><strong>Ad Views Limit:</strong></div>
                            <div class="col-sm-6">{{ $package->ad_views_limit ?? 'Unlimited' }}</div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="row">
                            <div class="col-sm-6"><strong>Course Access Limit:</strong></div>
                            <div class="col-sm-6">{{ $package->course_access_limit ?? 'Unlimited' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Subscribers</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Email</th>
                                <th>Subscription Date</th>
                                <th>Expires</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($subscribers as $subscriber)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.users.show', $subscriber) }}">{{ $subscriber->name }}</a>
                                </td>
                                <td>{{ $subscriber->email }}</td>
                                <td>{{ $subscriber->created_at->format('M d, Y') }}</td>
                                <td>
                                    @if($subscriber->package_expires_at)
                                        {{ $subscriber->package_expires_at->format('M d, Y') }}
                                    @else
                                        Lifetime
                                    @endif
                                </td>
                                <td>
                                    @if($subscriber->package_expires_at && $subscriber->package_expires_at->isPast())
                                        <span class="badge bg-secondary">Expired</span>
                                    @else
                                        <span class="badge bg-success">Active</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center">No subscribers found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        Showing {{ $subscribers->firstItem() }} to {{ $subscribers->lastItem() }} of {{ $subscribers->total() }} subscribers
                    </div>
                    <div>
                        {{ $subscribers->links() }}
                    </div>
                </div>
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
                            <h3>{{ $stats['total_subscribers'] }}</h3>
                            <p class="mb-0">Total Subscribers</p>
                        </div>
                    </div>
                    <div class="col-12 mb-3">
                        <div class="stat-card stat-card-success p-3 rounded">
                            <h3>{{ $stats['active_subscribers'] }}</h3>
                            <p class="mb-0">Active Subscribers</p>
                        </div>
                    </div>
                    <div class="col-12 mb-3">
                        <div class="stat-card stat-card-info p-3 rounded">
                            <h3>${{ number_format($stats['total_revenue'], 2) }}</h3>
                            <p class="mb-0">Total Revenue</p>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="stat-card stat-card-warning p-3 rounded">
                            <h3>${{ number_format($stats['monthly_revenue'], 2) }}</h3>
                            <p class="mb-0">Monthly Revenue</p>
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
                    <a href="{{ route('admin.packages.edit', $package) }}" class="btn btn-primary">
                        <i class="bi bi-pencil"></i> Edit Package
                    </a>
                    
                    @if($package->is_active)
                        <button type="button" class="btn btn-warning" onclick="deactivatePackage({{ $package->id }})">
                            <i class="bi bi-pause"></i> Deactivate Package
                        </button>
                    @else
                        <button type="button" class="btn btn-success" onclick="activatePackage({{ $package->id }})">
                            <i class="bi bi-play"></i> Activate Package
                        </button>
                    @endif
                    
                    <button type="button" class="btn btn-danger" onclick="deletePackage({{ $package->id }})">
                        <i class="bi bi-trash"></i> Delete Package
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function activatePackage(packageId) {
    if (confirm('Are you sure you want to activate this package?')) {
        fetch(`/admin/packages/${packageId}/activate`, {
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
            alert('An error occurred while activating the package.');
        });
    }
}

function deactivatePackage(packageId) {
    if (confirm('Are you sure you want to deactivate this package?')) {
        fetch(`/admin/packages/${packageId}/deactivate`, {
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
            alert('An error occurred while deactivating the package.');
        });
    }
}

function deletePackage(packageId) {
    if (confirm('Are you sure you want to delete this package?')) {
        fetch(`/admin/packages/${packageId}`, {
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
                window.location.href = '{{ route('admin.packages.index') }}';
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while deleting the package.');
        });
    }
}
</script>
@endpush