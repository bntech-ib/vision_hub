@extends('admin.layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Vendors</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="{{ route('admin.vendors.create') }}" class="btn btn-sm btn-primary">
                <i class="bi bi-plus"></i> Create Vendor
            </a>
        </div>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.vendors.index') }}" class="row g-3">
                    <div class="col-md-10">
                        <label for="search" class="form-label">Search</label>
                        <input type="text" class="form-control" id="search" name="search" value="{{ request('search') ?? '' }}" placeholder="Search vendors by name, email, or company...">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">Search</button>
                        <a href="{{ route('admin.vendors.index') }}" class="btn btn-secondary">Clear</a>
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
                        <th>Vendor</th>
                        <th>Company</th>
                        <th>Email</th>
                        <th>Commission Rate</th>
                        <th>Access Keys</th>
                        <th>Sold</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($vendors as $vendor)
                    <tr>
                        <td>
                            <a href="{{ route('admin.vendors.show', $vendor) }}">
                                {{ $vendor->name }}
                            </a>
                        </td>
                        <td>{{ $vendor->vendor_company_name }}</td>
                        <td>{{ $vendor->email }}</td>
                        <td>{{ $vendor->vendor_commission_rate }}%</td>
                        <td>{{ $vendor->created_vendor_access_keys_count }}</td>
                        <td>{{ $vendor->sold_vendor_access_keys_count }}</td>
                        <td>{{ $vendor->created_at?->format('M d, Y') ?? 'N/A' }}</td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="{{ route('admin.vendors.show', $vendor) }}" class="btn btn-outline-primary" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('admin.vendors.edit', $vendor) }}" class="btn btn-outline-secondary" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button type="button" class="btn btn-outline-danger" title="Remove Vendor Status" 
                                        onclick="removeVendorStatus('{{ $vendor->id }}')">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center">No vendors found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="d-flex justify-content-between align-items-center">
            <div>
                Showing {{ $vendors->firstItem() ?? 0 }} to {{ $vendors->lastItem() ?? 0 }} of {{ $vendors->total() ?? 0 }} vendors
            </div>
            <div>
                {{ $vendors->links() }}
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
                location.reload();
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
</script>
@endpush