@extends('admin.layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Create Access Key</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="{{ route('admin.access-keys.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Access Keys
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Access Key Details</h5>
            </div>
            <div class="card-body">
                <form id="createAccessKeyForm">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="package_id" class="form-label">Package</label>
                        <select class="form-select" id="package_id" name="package_id" required>
                            <option value="">Select a package</option>
                            @foreach($packages as $package)
                                <option value="{{ $package->id }}">{{ $package->name }}</option>
                            @endforeach
                        </select>
                        <div class="form-text">Select the package this access key will grant access to</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="quantity" class="form-label">Quantity</label>
                        <input type="number" class="form-control" id="quantity" name="quantity" min="1" max="100" value="1" required>
                        <div class="form-text">Number of access keys to generate (max 100)</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="expires_at" class="form-label">Expiration Date</label>
                        <input type="date" class="form-control" id="expires_at" name="expires_at">
                        <div class="form-text">Leave blank for no expiration</div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Generate Access Key(s)</button>
                    <a href="{{ route('admin.access-keys.index') }}" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Access Key Information</h5>
            </div>
            <div class="card-body">
                <p>Access keys are required for user registration.</p>
                <ul>
                    <li>Each access key grants access to a specific package</li>
                    <li>Access keys can be used only once</li>
                    <li>Access keys can have expiration dates</li>
                    <li>Used access keys cannot be reused</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('createAccessKeyForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('{{ route('admin.access-keys.store') }}', {
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
            window.location.href = '{{ route('admin.access-keys.index') }}';
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while creating the access key.');
    });
});
</script>
@endpush