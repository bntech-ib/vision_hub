@extends('admin.layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Edit Package</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="{{ route('admin.packages.show', $package) }}" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-eye"></i> View
            </a>
            <a href="{{ route('admin.packages.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Packages
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Package Details</h5>
            </div>
            <div class="card-body">
                <form id="editPackageForm">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $package->name) }}" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="4">{{ old('description', $package->description) }}</textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="price" class="form-label">Price ($)</label>
                        <input type="number" class="form-control" id="price" name="price" step="0.01" min="0" value="{{ old('price', $package->price) }}" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="duration_days" class="form-label">Duration (Days)</label>
                        <input type="number" class="form-control" id="duration_days" name="duration_days" min="1" value="{{ old('duration_days', $package->duration_days) }}" required>
                        <div class="form-text">Number of days the package is valid for</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="features" class="form-label">Features (comma separated)</label>
                        <input type="text" class="form-control" id="features" name="features" value="{{ old('features', is_array($package->features) ? implode(', ', $package->features) : $package->features) }}">
                        <div class="form-text">Enter features separated by commas. Leave empty for no features.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Access Features</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="marketplace_access" name="marketplace_access" value="1" {{ old('marketplace_access', $package->marketplace_access) ? 'checked' : '' }}>
                            <label class="form-check-label" for="marketplace_access">Marketplace Access</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="brain_teaser_access" name="brain_teaser_access" value="1" {{ old('brain_teaser_access', $package->brain_teaser_access) ? 'checked' : '' }}>
                            <label class="form-check-label" for="brain_teaser_access">Brain Teaser Access</label>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="ad_views_limit" class="form-label">Ad Views Limit</label>
                        <input type="number" class="form-control" id="ad_views_limit" name="ad_views_limit" min="0" value="{{ old('ad_views_limit', $package->ad_views_limit) }}">
                        <div class="form-text">Maximum ad views per month (0 for unlimited)</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="daily_earning_limit" class="form-label">Daily Earning Limit ($)</label>
                        <input type="number" class="form-control" id="daily_earning_limit" name="daily_earning_limit" step="0.01" min="0" value="{{ old('daily_earning_limit', $package->daily_earning_limit) }}">
                        <div class="form-text">Maximum daily earning limit for ad interactions (0 for no limit)</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="ad_limits" class="form-label">Ad Interaction Limits</label>
                        <input type="number" class="form-control" id="ad_limits" name="ad_limits" min="0" value="{{ old('ad_limits', $package->ad_limits) }}">
                        <div class="form-text">Maximum number of ad interactions per day (0 for unlimited)</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="course_access_limit" class="form-label">Course Access Limit</label>
                        <input type="number" class="form-control" id="course_access_limit" name="course_access_limit" min="0" value="{{ old('course_access_limit', $package->course_access_limit) }}">
                        <div class="form-text">Maximum courses per month (0 for unlimited)</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="referral_earning_percentage" class="form-label">Referral Earning Amount ($)</label>
                        <input type="number" class="form-control" id="referral_earning_percentage" name="referral_earning_percentage" min="0" step="0.01" value="{{ old('referral_earning_percentage', $package->referral_earning_percentage) }}">
                        <div class="form-text">Fixed amount of referral earnings users with this package will receive</div>
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" {{ old('is_active', $package->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">Active</label>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Update Package</button>
                    <a href="{{ route('admin.packages.index') }}" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Package Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-5"><strong>ID:</strong></div>
                    <div class="col-sm-7">{{ $package->id }}</div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-5"><strong>Created:</strong></div>
                    <div class="col-sm-7">{{ $package->created_at->format('M d, Y H:i') }}</div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-5"><strong>Subscribers:</strong></div>
                    <div class="col-sm-7">{{ $package->users_count }}</div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Preview</h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5>{{ $package->name }}</h5>
                        <p class="text-muted">{{ $package->description ?? 'No description' }}</p>
                    </div>
                    <div>
                        <h5>${{ number_format($package->price, 2) }}</h5>
                    </div>
                </div>
                <hr>
                <div class="d-flex justify-content-between small">
                    <span>Duration:</span>
                    <span>{{ $package->duration_days ? $package->duration_days . ' days' : 'Lifetime' }}</span>
                </div>
                <div class="d-flex justify-content-between small">
                    <span>Daily Earning Limit:</span>
                    <span>${{ number_format($package->daily_earning_limit, 2) }}</span>
                </div>
                <div class="d-flex justify-content-between small">
                    <span>Ad Interaction Limits:</span>
                    <span>{{ $package->ad_limits }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('editPackageForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const packageId = "{{ $package->id }}";
    
    // Process features input
    const featuresInput = document.getElementById('features');
    let featuresArray = [];
    if (featuresInput.value.trim() !== '') {
        featuresArray = featuresInput.value.split(',')
            .map(feature => feature.trim())
            .filter(feature => feature !== '');
    }
    formData.set('features', JSON.stringify(featuresArray));
    
    // Ensure all checkboxes are properly handled
    if (!document.getElementById('marketplace_access').checked) {
        formData.set('marketplace_access', '0');
    }
    if (!document.getElementById('brain_teaser_access').checked) {
        formData.set('brain_teaser_access', '0');
    }
    if (!document.getElementById('is_active').checked) {
        formData.set('is_active', '0');
    }
    
    fetch(`/admin/packages/${packageId}`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-HTTP-Method-Override': 'PUT'
        },
    })
    .then(response => {
        // Handle non-JSON responses (like validation errors from Laravel)
        const contentType = response.headers.get("content-type");
        if (!contentType || !contentType.includes("application/json")) {
            return response.text().then(text => {
                throw new Error("Server returned non-JSON response: " + text);
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            alert('Package updated successfully!');
            window.location.href = data.package ? `/admin/packages/${data.package.id}` : '/admin/packages';
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
        alert('An error occurred while updating the package. Please try again.');
    });
});
</script>
@endpush