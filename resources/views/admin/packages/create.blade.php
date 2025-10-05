@extends('admin.layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Create Package</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
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
                <form id="createPackageForm">
                    @csrf
                    
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="4"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="price" class="form-label">Price ($)</label>
                        <input type="number" class="form-control" id="price" name="price" step="0.01" min="0" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="duration_days" class="form-label">Duration (Days)</label>
                        <input type="number" class="form-control" id="duration_days" name="duration_days" min="1" required>
                        <div class="form-text">Number of days the package is valid for</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="features" class="form-label">Features (comma separated)</label>
                        <input type="text" class="form-control" id="features" name="features" placeholder="feature1, feature2, feature3">
                        <div class="form-text">Enter features separated by commas. Leave empty for no features.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Access Features</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="marketplace_access" name="marketplace_access" value="1">
                            <label class="form-check-label" for="marketplace_access">Marketplace Access</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="brain_teaser_access" name="brain_teaser_access" value="1">
                            <label class="form-check-label" for="brain_teaser_access">Brain Teaser Access</label>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="ad_views_limit" class="form-label">Ad Views Limit</label>
                        <input type="number" class="form-control" id="ad_views_limit" name="ad_views_limit" min="0" value="0">
                        <div class="form-text">Maximum ad views per month (0 for unlimited)</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="daily_earning_limit" class="form-label">Daily Earning Limit ($)</label>
                        <input type="number" class="form-control" id="daily_earning_limit" name="daily_earning_limit" step="0.01" min="0" value="0">
                        <div class="form-text">Maximum daily earning limit for ad interactions (0 for no limit)</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="ad_limits" class="form-label">Ad Interaction Limits</label>
                        <input type="number" class="form-control" id="ad_limits" name="ad_limits" min="0" value="0">
                        <div class="form-text">Maximum number of ad interactions per day (0 for unlimited)</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="course_access_limit" class="form-label">Course Access Limit</label>
                        <input type="number" class="form-control" id="course_access_limit" name="course_access_limit" min="0">
                        <div class="form-text">Maximum courses per month (0 for unlimited)</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="referral_earning_percentage" class="form-label">Referral Earning Amount ($)</label>
                        <input type="number" class="form-control" id="referral_earning_percentage" name="referral_earning_percentage" min="0" step="0.01" value="0">
                        <div class="form-text">Fixed amount of referral earnings users with this package will receive</div>
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" checked>
                        <label class="form-check-label" for="is_active">Active</label>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Create Package</button>
                    <a href="{{ route('admin.packages.index') }}" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Package Information</h5>
            </div>
            <div class="card-body">
                <p>Packages define the features and limitations available to users.</p>
                <ul>
                    <li>Each package must have a unique name</li>
                    <li>Price is in USD</li>
                    <li>Duration determines how long the package is valid</li>
                    <li>Features can be enabled/disabled per package</li>
                    <li>Limits help control resource usage</li>
                    <li>Referral earning amount determines how much users earn from referrals</li>
                    <li>Daily earning limit controls maximum daily earnings from ad interactions</li>
                    <li>Ad interaction limits control how many ads users can interact with per day</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('createPackageForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Process features input
    const featuresInput = document.getElementById('features');
    let featuresArray = [];
    if (featuresInput.value.trim() !== '') {
        featuresArray = featuresInput.value.split(',')
            .map(feature => feature.trim())
            .filter(feature => feature !== '');
    }
    // Add features as JSON to form data
    const formData = new FormData(this);
    formData.append('features', JSON.stringify(featuresArray));
    
    // Ensure all checkboxes are properly handled
    if (!document.getElementById('marketplace_access').checked) {
        formData.append('marketplace_access', '0');
    }
    if (!document.getElementById('brain_teaser_access').checked) {
        formData.append('brain_teaser_access', '0');
    }
    if (!document.getElementById('is_active').checked) {
        formData.append('is_active', '0');
    }
    
    fetch("{{ route('admin.packages.store') }}", {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
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
            alert('Package created successfully!');
            window.location.href = '/admin/packages';
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
        alert('An error occurred while creating the package. Please try again.');
    });
});
</script>
@endpush