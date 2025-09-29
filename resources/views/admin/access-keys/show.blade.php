@extends('admin.layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Access Key Details</h1>
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
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Access Key Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-3"><strong>Access Key:</strong></div>
                    <div class="col-sm-9">
                        <code class="text-break">{{ $accessKey->key }}</code>
                        <button class="btn btn-sm btn-outline-primary ms-2" onclick="copyToClipboard('{{ $accessKey->key }}')">
                            <i class="bi bi-clipboard"></i> Copy
                        </button>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-3"><strong>Package:</strong></div>
                    <div class="col-sm-9">
                        <a href="{{ route('admin.packages.show', $accessKey->package) }}">
                            {{ $accessKey->package->name }}
                        </a>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-3"><strong>Status:</strong></div>
                    <div class="col-sm-9">
                        @if($accessKey->is_used)
                            <span class="badge bg-success">Used</span>
                        @elseif(!$accessKey->is_active)
                            <span class="badge bg-secondary">Inactive</span>
                        @elseif($accessKey->expires_at && $accessKey->expires_at->isPast())
                            <span class="badge bg-warning">Expired</span>
                        @else
                            <span class="badge bg-primary">Available</span>
                        @endif
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-3"><strong>Created By:</strong></div>
                    <div class="col-sm-9">
                        {{ $accessKey->creator->name }}
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-3"><strong>Created At:</strong></div>
                    <div class="col-sm-9">
                        {{ $accessKey->created_at ? $accessKey->created_at->format('M d, Y H:i') }}
                    </div>
                </div>
                @if($accessKey->expires_at)
                <hr>
                <div class="row">
                    <div class="col-sm-3"><strong>Expires At:</strong></div>
                    <div class="col-sm-9">
                        {{ $accessKey->expires_at->format('M d, Y H:i') }}
                        @if($accessKey->expires_at->isPast())
                            <span class="text-danger">(Expired)</span>
                        @endif
                    </div>
                </div>
                @endif
                @if($accessKey->is_used)
                <hr>
                <div class="row">
                    <div class="col-sm-3"><strong>Used By:</strong></div>
                    <div class="col-sm-9">
                        <a href="{{ route('admin.users.show', $accessKey->user) }}">
                            {{ $accessKey->user->name }}
                        </a>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-3"><strong>Used At:</strong></div>
                    <div class="col-sm-9">
                        {{ $accessKey->used_at ? $accessKey->used_at->format('M d, Y H:i') : 'N/A' }}
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Package Details</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-4"><strong>Name:</strong></div>
                    <div class="col-sm-8">{{ $accessKey->package->name }}</div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-4"><strong>Price:</strong></div>
                    <div class="col-sm-8">${{ number_format($accessKey->package->price, 2) }}</div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-4"><strong>Duration:</strong></div>
                    <div class="col-sm-8">
                        {{ $accessKey->package->duration_days ? $accessKey->package->duration_days . ' days' : 'Lifetime' }}
                    </div>
                </div>
                @if($accessKey->package->features)
                <hr>
                <div class="row">
                    <div class="col-sm-4"><strong>Features:</strong></div>
                    <div class="col-sm-8">
                        <ul class="mb-0">
                            @foreach($accessKey->package->features as $feature)
                                <li>{{ $feature }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                @endif
            </div>
        </div>
        
        @if(!$accessKey->is_used)
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    @if($accessKey->is_active)
                        <button type="button" class="btn btn-warning" onclick="deactivateAccessKey({{ $accessKey->id }})">
                            <i class="bi bi-pause"></i> Deactivate Access Key
                        </button>
                    @else
                        <button type="button" class="btn btn-success" onclick="activateAccessKey({{ $accessKey->id }})">
                            <i class="bi bi-play"></i> Activate Access Key
                        </button>
                    @endif
                    
                    <button type="button" class="btn btn-danger" onclick="deleteAccessKey({{ $accessKey->id }})">
                        <i class="bi bi-trash"></i> Delete Access Key
                    </button>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        alert('Access key copied to clipboard!');
    }).catch(err => {
        console.error('Failed to copy: ', err);
        // Fallback for older browsers
        const textArea = document.createElement("textarea");
        textArea.value = text;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        alert('Access key copied to clipboard!');
    });
}

function activateAccessKey(accessKeyId) {
    if (confirm('Are you sure you want to activate this access key?')) {
        fetch(`/admin/access-keys/${accessKeyId}/activate`, {
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
            alert('An error occurred while activating the access key.');
        });
    }
}

function deactivateAccessKey(accessKeyId) {
    if (confirm('Are you sure you want to deactivate this access key?')) {
        fetch(`/admin/access-keys/${accessKeyId}/deactivate`, {
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
            alert('An error occurred while deactivating the access key.');
        });
    }
}

function deleteAccessKey(accessKeyId) {
    if (confirm('Are you sure you want to delete this access key?')) {
        fetch(`/admin/access-keys/${accessKeyId}`, {
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
                window.location.href = '{{ route('admin.access-keys.index') }}';
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while deleting the access key.');
        });
    }
}
</script>
@endpush