@extends('admin.layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Vendor Details</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="{{ route('admin.vendors.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Vendors
            </a>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card stat-card stat-card-primary">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-0">Total Keys</h5>
                        <h2 class="mb-0">{{ $stats['total_access_keys'] ?? 0 }}</h2>
                    </div>
                    <i class="bi bi-key fs-1 text-primary"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card stat-card-success">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-0">Sold Keys</h5>
                        <h2 class="mb-0">{{ $stats['sold_access_keys'] ?? 0 }}</h2>
                    </div>
                    <i class="bi bi-currency-dollar fs-1 text-success"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card stat-card-info">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-0">Earnings</h5>
                        <h2 class="mb-0">${{ number_format($stats['total_earnings'] ?? 0, 2) }}</h2>
                    </div>
                    <i class="bi bi-wallet fs-1 text-info"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card stat-card-warning">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-0">Commission</h5>
                        <h2 class="mb-0">{{ $vendor->vendor_commission_rate }}%</h2>
                    </div>
                    <i class="bi bi-percent fs-1 text-warning"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Vendor Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-3"><strong>Name:</strong></div>
                    <div class="col-sm-9">{{ $vendor->name }}</div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-3"><strong>Email:</strong></div>
                    <div class="col-sm-9">{{ $vendor->email }}</div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-3"><strong>Company:</strong></div>
                    <div class="col-sm-9">{{ $vendor->vendor_company_name ?? 'N/A' }}</div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-3"><strong>Description:</strong></div>
                    <div class="col-sm-9">{{ $vendor->vendor_description ?? 'N/A' }}</div>
                </div>
                @if($vendor->vendor_website)
                <hr>
                <div class="row">
                    <div class="col-sm-3"><strong>Website:</strong></div>
                    <div class="col-sm-9">
                        <a href="{{ $vendor->vendor_website }}" target="_blank">{{ $vendor->vendor_website }}</a>
                    </div>
                </div>
                @endif
                <hr>
                <div class="row">
                    <div class="col-sm-3"><strong>Commission Rate:</strong></div>
                    <div class="col-sm-9">{{ $vendor->vendor_commission_rate }}%</div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-3"><strong>Created:</strong></div>
                    <div class="col-sm-9">{{ $vendor->created_at?->format('M d, Y H:i') ?? 'N/A' }}</div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('admin.vendors.edit', $vendor) }}" class="btn btn-primary">
                        <i class="bi bi-pencil"></i> Edit Vendor
                    </a>
                    
                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#generateKeysModal">
                        <i class="bi bi-key"></i> Generate Access Keys
                    </button>
                    
                    <a href="{{ route('admin.vendors.access-keys', $vendor) }}" class="btn btn-info">
                        <i class="bi bi-list"></i> View Access Keys
                    </a>
                    
                    <button type="button" class="btn btn-danger" onclick="removeVendorStatus({{ $vendor->id }})">
                        <i class="bi bi-trash"></i> Remove Vendor Status
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Generate Access Keys Modal -->
<div class="modal fade" id="generateKeysModal" tabindex="-1" aria-labelledby="generateKeysModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="generateKeysModalLabel">Generate Access Keys for Vendor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="generateKeysForm">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="package_id" class="form-label">Package</label>
                        <select class="form-select" id="package_id" name="package_id" required>
                            <option value="">Choose a package</option>
                            @foreach(App\Models\UserPackage::where('is_active', true)->get() as $package)
                                <option value="{{ $package->id }}">{{ $package->name }} (${{ number_format($package->price, 2) }})</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="quantity" class="form-label">Quantity</label>
                        <input type="number" class="form-control" id="quantity" name="quantity" min="1" max="100" value="1" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="expires_at" class="form-label">Expires At</label>
                        <input type="date" class="form-control" id="expires_at" name="expires_at">
                    </div>
                    
                    <div class="mb-3">
                        <label for="commission_rate" class="form-label">Commission Rate (%)</label>
                        <input type="number" class="form-control" id="commission_rate" name="commission_rate" step="0.01" min="0" max="100" value="{{ $vendor->vendor_commission_rate }}" required>
                        <div class="form-text">Percentage of sales that goes to the vendor</div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="generateAccessKeys({{ $vendor->id }})">Generate Keys</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function removeVendorStatus(vendorId) {
    if (confirm('Are you sure you want to remove this vendor status? This will not delete the user account.')) {
        fetch(`/admin/vendors/${vendorId}`, {
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
                window.location.href = '{{ route('admin.vendors.index') }}';
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while removing the vendor status.');
        });
    }
}

function generateAccessKeys(vendorId) {
    const form = document.getElementById('generateKeysForm');
    const formData = new FormData(form);
    
    fetch(`/admin/vendors/${vendorId}/generate-access-keys`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            // Close the modal
            bootstrap.Modal.getInstance(document.getElementById('generateKeysModal')).hide();
            // Reload the page to show updated stats
            location.reload();
        } else {
            // Display validation errors if present
            if (data.errors) {
                let errorMessages = Object.values(data.errors).flat().join('\n');
                alert('Validation Error:\n' + errorMessages);
            } else {
                alert('Error: ' + data.message);
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while generating access keys. Please try again.');
    });
}
</script>
@endpush

@push('styles')
<style>
.stat-card {
    border-left: 4px solid;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    transition: transform 0.2s;
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.stat-card-primary {
    border-left-color: #0d6efd;
}

.stat-card-success {
    border-left-color: #198754;
}

.stat-card-info {
    border-left-color: #0dcaf0;
}

.stat-card-warning {
    border-left-color: #ffc107;
}
</style>
@endpush