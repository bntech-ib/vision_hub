@extends('admin.layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Vendor Access Keys</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="{{ route('admin.vendors.show', $vendor) }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Vendor
            </a>
        </div>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.vendors.access-keys', $vendor) }}" class="row g-3">
                    <div class="col-md-8">
                        <label for="status" class="form-label">Filter by Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">All Statuses</option>
                            <option value="unsold" {{ request('status') == 'unsold' ? 'selected' : '' }}>Unsold</option>
                            <option value="sold" {{ request('status') == 'sold' ? 'selected' : '' }}>Sold</option>
                        </select>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">Filter</button>
                        <a href="{{ route('admin.vendors.access-keys', $vendor) }}" class="btn btn-secondary">Clear</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">{{ $vendor->vendor_company_name }} - Access Keys</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Key</th>
                        <th>Package</th>
                        <th>Commission Rate</th>
                        <th>Status</th>
                        <th>Buyer</th>
                        <th>Created</th>
                        <th>Expires</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($vendorAccessKeys as $vendorAccessKey)
                    <tr>
                        <td>{{ $vendorAccessKey->accessKey->key }}</td>
                        <td>{{ $vendorAccessKey->accessKey->package->name ?? 'N/A' }}</td>
                        <td>{{ $vendorAccessKey->commission_rate }}%</td>
                        <td>
                            @if($vendorAccessKey->is_sold)
                                <span class="badge bg-success">Sold</span>
                            @else
                                <span class="badge bg-secondary">Unsold</span>
                            @endif
                        </td>
                        <td>
                            @if($vendorAccessKey->is_sold && $vendorAccessKey->buyer)
                                {{ $vendorAccessKey->buyer->name }}
                            @else
                                N/A
                            @endif
                        </td>
                        <td>{{ $vendorAccessKey->created_at?->format('M d, Y H:i') ?? 'N/A' }}</td>
                        <td>{{ $vendorAccessKey->accessKey->expires_at?->format('M d, Y') ?? 'Never' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center">No access keys found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="d-flex justify-content-between align-items-center">
            <div>
                Showing {{ $vendorAccessKeys->firstItem() ?? 0 }} to {{ $vendorAccessKeys->lastItem() ?? 0 }} of {{ $vendorAccessKeys->total() ?? 0 }} access keys
            </div>
            <div>
                {{ $vendorAccessKeys->links() }}
            </div>
        </div>
    </div>
</div>
@endsection