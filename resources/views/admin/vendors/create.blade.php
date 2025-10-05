@extends('admin.layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Create Vendor</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="{{ route('admin.vendors.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Vendors
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Vendor Details</h5>
            </div>
            <div class="card-body">
                <form id="createVendorForm">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="user_id" class="form-label">Select User</label>
                        <select class="form-select" id="user_id" name="user_id" required>
                            <option value="">Choose a user</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                            @endforeach
                        </select>
                        <div class="form-text">Select an existing user to convert to vendor</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="vendor_company_name" class="form-label">Company Name</label>
                        <input type="text" class="form-control" id="vendor_company_name" name="vendor_company_name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="vendor_description" class="form-label">Description</label>
                        <textarea class="form-control" id="vendor_description" name="vendor_description" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="vendor_website" class="form-label">Website</label>
                        <input type="url" class="form-control" id="vendor_website" name="vendor_website">
                    </div>
                    
                    <div class="mb-3">
                        <label for="vendor_commission_rate" class="form-label">Commission Rate (%)</label>
                        <input type="number" class="form-control" id="vendor_commission_rate" name="vendor_commission_rate" step="0.01" min="0" max="100" required>
                        <div class="form-text">Percentage of sales that goes to the vendor</div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Create Vendor</button>
                    <a href="{{ route('admin.vendors.index') }}" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Vendor Information</h5>
            </div>
            <div class="card-body">
                <p>Convert existing users to vendors who can sell access keys.</p>
                <ul>
                    <li>Each vendor can have their own commission rate</li>
                    <li>Vendors can generate access keys for sale</li>
                    <li>Commission is calculated based on the package price</li>
                    <li>Vendors earn money when their access keys are sold</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('createVendorForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch("{{ route('admin.vendors.store') }}", {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Vendor created successfully!');
            window.location.href = '/admin/vendors';
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
        alert('An error occurred while creating the vendor. Please try again.');
    });
});
</script>
@endpush