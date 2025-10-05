@extends('admin.layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Edit User</h1>
        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Back to List</a>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.users.update', $user) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="mb-3">
                    <label for="name" class="form-label">Name</label>
                    <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                    @error('name')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username" value="{{ old('username', $user->username) }}">
                    @error('username')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" value="{{ old('email', $user->email) }}" required>
                    @error('email')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password">
                    <div class="form-text">Leave blank to keep current password</div>
                    @error('password')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="mb-3">
                    <label for="full_name" class="form-label">Full Name</label>
                    <input type="text" class="form-control" id="full_name" name="full_name" value="{{ old('full_name', $user->full_name) }}">
                    @error('full_name')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="text" class="form-control" id="phone" name="phone" value="{{ old('phone', $user->phone) }}">
                            @error('phone')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="country" class="form-label">Country</label>
                            <input type="text" class="form-control" id="country" name="country" value="{{ old('country', $user->country) }}">
                            @error('country')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="current_package_id" class="form-label">Package</label>
                            <select class="form-control" id="current_package_id" name="current_package_id">
                                <option value="">No Package</option>
                                @foreach($packages as $package)
                                    <option value="{{ $package->id }}" {{ old('current_package_id', $user->current_package_id) == $package->id ? 'selected' : '' }}>
                                        {{ $package->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('current_package_id')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="wallet_balance" class="form-label">Wallet Balance (₦)</label>
                            <input type="number" step="0.01" class="form-control" id="wallet_balance" name="wallet_balance" value="{{ old('wallet_balance', $user->wallet_balance) }}">
                            @error('wallet_balance')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="referral_earnings" class="form-label">Referral Earnings (₦)</label>
                            <input type="number" step="0.01" class="form-control" id="referral_earnings" name="referral_earnings" value="{{ old('referral_earnings', $user->referral_earnings) }}">
                            @error('referral_earnings')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="is_admin" name="is_admin" value="1" {{ old('is_admin', $user->is_admin) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_admin">
                            Is Admin
                        </label>
                    </div>
                </div>
                
                <!-- Vendor Section -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Vendor Information</h5>
                    </div>
                    <div class="card-body">
                        @if($user->isVendor())
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i> This user is already a vendor.
                                <a href="{{ route('admin.vendors.show', $user) }}" class="btn btn-sm btn-primary float-end">View Vendor Details</a>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="vendor_company_name" class="form-label">Company Name</label>
                                        <input type="text" class="form-control" id="vendor_company_name" name="vendor_company_name" value="{{ old('vendor_company_name', $user->vendor_company_name) }}" required>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="vendor_commission_rate" class="form-label">Commission Rate (%)</label>
                                        <input type="number" class="form-control" id="vendor_commission_rate" name="vendor_commission_rate" step="0.01" min="0" max="100" value="{{ old('vendor_commission_rate', $user->vendor_commission_rate) }}" required>
                                        <div class="form-text">Percentage of sales that goes to the vendor</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="vendor_website" class="form-label">Website</label>
                                <input type="url" class="form-control" id="vendor_website" name="vendor_website" value="{{ old('vendor_website', $user->vendor_website) }}">
                            </div>
                            
                            <div class="mb-3">
                                <label for="vendor_description" class="form-label">Description</label>
                                <textarea class="form-control" id="vendor_description" name="vendor_description" rows="3">{{ old('vendor_description', $user->vendor_description) }}</textarea>
                            </div>
                        @else
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="make_vendor" name="make_vendor" value="1" {{ old('make_vendor') ? 'checked' : '' }}>
                                <label class="form-check-label" for="make_vendor">
                                    Make this user a vendor
                                </label>
                            </div>
                            
                            <div id="vendor_fields" style="display: none;">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="vendor_company_name" class="form-label">Company Name</label>
                                            <input type="text" class="form-control" id="vendor_company_name" name="vendor_company_name" value="{{ old('vendor_company_name') }}">
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="vendor_commission_rate" class="form-label">Commission Rate (%)</label>
                                            <input type="number" class="form-control" id="vendor_commission_rate" name="vendor_commission_rate" step="0.01" min="0" max="100" value="{{ old('vendor_commission_rate', 15.5) }}">
                                            <div class="form-text">Percentage of sales that goes to the vendor</div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="vendor_website" class="form-label">Website</label>
                                    <input type="url" class="form-control" id="vendor_website" name="vendor_website" value="{{ old('vendor_website') }}">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="vendor_description" class="form-label">Description</label>
                                    <textarea class="form-control" id="vendor_description" name="vendor_description" rows="3">{{ old('vendor_description') }}</textarea>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
                
                <div class="d-flex justify-content-end mt-3">
                    <a href="{{ route('admin.users.index') }}" class="btn btn-secondary me-2">Cancel</a>
                    <button type="submit" class="btn btn-primary">Update User</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const makeVendorCheckbox = document.getElementById('make_vendor');
    const vendorFields = document.getElementById('vendor_fields');
    
    if (makeVendorCheckbox) {
        makeVendorCheckbox.addEventListener('change', function() {
            vendorFields.style.display = this.checked ? 'block' : 'none';
        });
        
        // Show vendor fields if checkbox is already checked (e.g., on validation error)
        if (makeVendorCheckbox.checked) {
            vendorFields.style.display = 'block';
        }
    }
});
</script>
@endpush
@endsection