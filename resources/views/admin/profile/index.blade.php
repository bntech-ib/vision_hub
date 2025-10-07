@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-person-circle"></i> My Profile</h2>
    </div>

    <div class="row">
        <div class="col-md-4">
            <!-- Profile Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Profile Information</h5>
                </div>
                <div class="card-body text-center">
                    <div class="mb-3">
                        @if(auth()->user()->profile_image)
                            <img src="{{ Storage::url(auth()->user()->profile_image) }}" 
                                 alt="Profile Image" 
                                 class="rounded-circle" 
                                 style="width: 150px; height: 150px; object-fit: cover;">
                        @else
                            <div class="bg-light rounded-circle d-flex align-items-center justify-content-center" 
                                 style="width: 150px; height: 150px; margin: 0 auto;">
                                <i class="bi bi-person-fill" style="font-size: 4rem; color: #6c757d;"></i>
                            </div>
                        @endif
                    </div>
                    <h4>{{ auth()->user()->name }}</h4>
                    <p class="text-muted">{{ auth()->user()->email }}</p>
                    <p class="badge bg-primary">Administrator</p>
                    
                    @if(auth()->user()->two_factor_enabled)
                        <p class="badge bg-success">
                            <i class="bi bi-shield-check"></i> 2FA Enabled
                        </p>
                    @else
                        <p class="badge bg-warning text-dark">
                            <i class="bi bi-shield-x"></i> 2FA Disabled
                        </p>
                    @endif
                </div>
            </div>
            
            <!-- Security Card -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Security</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <span>Two-Factor Authentication</span>
                                <span>
                                    @if(auth()->user()->two_factor_enabled)
                                        <span class="badge bg-success">Enabled</span>
                                    @else
                                        <span class="badge bg-danger">Disabled</span>
                                    @endif
                                </span>
                            </div>
                        </li>
                        <li class="list-group-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <span>Last Login</span>
                                <span class="text-muted">-</span>
                            </div>
                        </li>
                        <li class="list-group-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <span>Account Created</span>
                                <span class="text-muted">{{ auth()->user()->created_at->format('M d, Y') }}</span>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <!-- Profile Update Form -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Update Profile</h5>
                </div>
                <div class="card-body">
                    <form id="profile-form">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Full Name</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="name" 
                                           name="name" 
                                           value="{{ auth()->user()->name }}" 
                                           required>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" 
                                           class="form-control" 
                                           id="email" 
                                           name="email" 
                                           value="{{ auth()->user()->email }}" 
                                           required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="full_name" class="form-label">Display Name</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="full_name" 
                                           name="full_name" 
                                           value="{{ auth()->user()->full_name }}">
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="phone" class="form-label">Phone Number</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="phone" 
                                           name="phone" 
                                           value="{{ auth()->user()->phone }}">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="country" class="form-label">Country</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="country" 
                                           name="country" 
                                           value="{{ auth()->user()->country }}">
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="profile_image" class="form-label">Profile Image</label>
                                    <input type="file" 
                                           class="form-control" 
                                           id="profile_image" 
                                           name="profile_image" 
                                           accept="image/*">
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Update Profile
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Password Update Form -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Change Password</h5>
                </div>
                <div class="card-body">
                    <form id="password-form">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Current Password</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="current_password" 
                                   name="current_password" 
                                   required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="new_password" class="form-label">New Password</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="new_password" 
                                   name="new_password" 
                                   required>
                            <div class="form-text">Password must be at least 8 characters long.</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="new_password_confirmation" class="form-label">Confirm New Password</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="new_password_confirmation" 
                                   name="new_password_confirmation" 
                                   required>
                        </div>
                        
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-key"></i> Change Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Two-Factor Authentication Card -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Two-Factor Authentication</h5>
                </div>
                <div class="card-body">
                    <p>Two-factor authentication adds an extra layer of security to your account by requiring more than just a password to sign in.</p>
                    
                    @if(auth()->user()->two_factor_enabled)
                        <div class="alert alert-success">
                            <i class="bi bi-shield-check"></i> Two-factor authentication is enabled on your account.
                        </div>
                        
                        <a href="{{ route('admin.settings.security') }}" class="btn btn-outline-primary">
                            <i class="bi bi-gear"></i> Manage 2FA Settings
                        </a>
                        
                        <button id="disable-2fa" class="btn btn-danger ms-2">
                            <i class="bi bi-shield-x"></i> Disable 2FA
                        </button>
                    @else
                        <div class="alert alert-warning">
                            <i class="bi bi-shield-exclamation"></i> Two-factor authentication is not enabled on your account.
                        </div>
                        
                        <a href="{{ route('admin.settings.security') }}" class="btn btn-primary">
                            <i class="bi bi-shield-plus"></i> Enable 2FA
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Profile form submission
    $('#profile-form').submit(function(e) {
        e.preventDefault();
        
        var formData = new FormData(this);
        
        $.ajax({
            url: '{{ route("admin.profile.update") }}',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    showAlert('success', response.message);
                    // Update the profile image if changed
                    if (response.data && response.data.user.profile_image) {
                        $('.profile-image').attr('src', response.data.user.profile_image);
                    }
                } else {
                    showAlert('error', response.message || 'An error occurred');
                }
            },
            error: function(xhr) {
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    var errors = xhr.responseJSON.errors;
                    var errorMessages = '';
                    for (var field in errors) {
                        errorMessages += errors[field][0] + '<br>';
                    }
                    showAlert('error', errorMessages);
                } else {
                    showAlert('error', 'An error occurred while updating profile');
                }
            }
        });
    });
    
    // Password form submission
    $('#password-form').submit(function(e) {
        e.preventDefault();
        
        var formData = $(this).serialize();
        
        $.ajax({
            url: '{{ route("admin.profile.password") }}',
            method: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    showAlert('success', response.message);
                    $('#password-form')[0].reset();
                } else {
                    showAlert('error', response.message || 'An error occurred');
                }
            },
            error: function(xhr) {
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    var errors = xhr.responseJSON.errors;
                    var errorMessages = '';
                    for (var field in errors) {
                        errorMessages += errors[field][0] + '<br>';
                    }
                    showAlert('error', errorMessages);
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    showAlert('error', xhr.responseJSON.message);
                } else {
                    showAlert('error', 'An error occurred while changing password');
                }
            }
        });
    });
    
    // Disable 2FA
    $('#disable-2fa').click(function() {
        if (confirm('Are you sure you want to disable two-factor authentication?')) {
            $.post('{{ route("admin.api.2fa.disable") }}', {
                _token: '{{ csrf_token() }}'
            })
            .done(function(response) {
                if (response.success) {
                    showAlert('success', response.message);
                    location.reload();
                } else {
                    showAlert('error', response.message || 'Failed to disable 2FA');
                }
            })
            .fail(function() {
                showAlert('error', 'Failed to disable 2FA');
            });
        }
    });
});
</script>
@endpush
@endsection