@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-shield-lock"></i> Security Settings</h2>
    </div>

    <div class="row">
        <div class="col-md-8">
            <!-- Two-Factor Authentication Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Two-Factor Authentication</h5>
                </div>
                <div class="card-body">
                    <div id="2fa-status">
                        <p>Two-factor authentication adds an extra layer of security to your account by requiring more than just a password to sign in.</p>
                        
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="2fa-toggle" {{ auth()->user()->two_factor_enabled ? 'checked' : '' }}>
                            <label class="form-check-label" for="2fa-toggle">Enable Two-Factor Authentication</label>
                        </div>
                        
                        <div id="2fa-setup" class="mt-4" style="display: {{ auth()->user()->two_factor_enabled ? 'block' : 'none' }};">
                            @if(auth()->user()->two_factor_enabled && auth()->user()->hasConfirmedTwoFactor())
                                <div class="alert alert-success">
                                    <i class="bi bi-check-circle"></i> Two-factor authentication is enabled and confirmed.
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Recovery Codes</label>
                                    <p class="text-muted">Store these recovery codes in a secure location. They can be used to access your account if you lose your authenticator device.</p>
                                    <button id="generate-recovery-codes" class="btn btn-outline-primary btn-sm">
                                        <i class="bi bi-arrow-repeat"></i> Generate New Recovery Codes
                                    </button>
                                    <div id="recovery-codes-display" class="mt-2"></div>
                                </div>
                                
                                <button id="disable-2fa" class="btn btn-danger">
                                    <i class="bi bi-shield-x"></i> Disable Two-Factor Authentication
                                </button>
                            @else
                                <div id="2fa-setup-steps">
                                    <h6>Setup Steps:</h6>
                                    <ol>
                                        <li>Install an authenticator app like Google Authenticator or Authy on your mobile device.</li>
                                        <li>Scan the QR code below with your authenticator app.</li>
                                        <li>Enter the 6-digit code from your authenticator app to confirm setup.</li>
                                    </ol>
                                    
                                    <div class="text-center mb-3">
                                        <div id="qr-code-container"></div>
                                        <div id="manual-secret" class="mt-2 text-muted"></div>
                                    </div>
                                    
                                    <form id="confirm-2fa-form">
                                        <div class="mb-3">
                                            <label for="2fa-code" class="form-label">Authentication Code</label>
                                            <input type="text" class="form-control" id="2fa-code" name="code" placeholder="Enter 6-digit code" maxlength="6" required>
                                        </div>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-check-circle"></i> Confirm and Enable
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Security Logs Card -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Security Logs</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped" id="security-logs-table">
                            <thead>
                                <tr>
                                    <th>Date & Time</th>
                                    <th>Action</th>
                                    <th>IP Address</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Security logs will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                    <div id="security-logs-pagination"></div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <!-- Recent Activity Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Recent Activity</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush" id="recent-activity">
                        <!-- Recent activity will be loaded here -->
                    </ul>
                </div>
            </div>
            
            <!-- Security Tips Card -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Security Tips</h5>
                </div>
                <div class="card-body">
                    <ul>
                        <li>Use a strong, unique password for your account</li>
                        <li>Enable two-factor authentication</li>
                        <li>Regularly review your security logs</li>
                        <li>Keep your recovery codes in a safe place</li>
                        <li>Log out of your account when using shared computers</li>
                        <li>Update your password regularly</li>
                        <li>Be cautious of phishing attempts</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Toggle 2FA
    $('#2fa-toggle').change(function() {
        if ($(this).is(':checked')) {
            enable2FA();
        } else {
            disable2FA();
        }
    });
    
    // Confirm 2FA form submission
    $('#confirm-2fa-form').submit(function(e) {
        e.preventDefault();
        confirm2FA();
    });
    
    // Generate recovery codes
    $('#generate-recovery-codes').click(function() {
        generateRecoveryCodes();
    });
    
    // Disable 2FA
    $('#disable-2fa').click(function() {
        if (confirm('Are you sure you want to disable two-factor authentication?')) {
            disable2FA();
        }
    });
    
    // Load security logs
    loadSecurityLogs();
    
    // Load recent activity
    loadRecentActivity();
    
    // Initialize 2FA status
    check2FAStatus();
});

// Check 2FA status
function check2FAStatus() {
    $.get('{{ route("admin.api.2fa.status") }}')
        .done(function(response) {
            if (response.success) {
                $('#2fa-toggle').prop('checked', response.data.enabled);
                if (response.data.enabled) {
                    $('#2fa-setup').show();
                }
            }
        })
        .fail(function() {
            showAlert('error', 'Failed to check 2FA status');
        });
}

// Enable 2FA
function enable2FA() {
    $.post('{{ route("admin.api.2fa.enable") }}', {
        _token: '{{ csrf_token() }}'
    })
    .done(function(response) {
        if (response.success) {
            $('#qr-code-container').html('<img src="' + response.data.qr_code_url + '" alt="QR Code" class="img-fluid">');
            $('#manual-secret').text('Secret: ' + response.data.secret);
            $('#2fa-setup').show();
            showAlert('success', response.message);
        } else {
            $('#2fa-toggle').prop('checked', false);
            showAlert('error', response.message || 'Failed to enable 2FA');
        }
    })
    .fail(function() {
        $('#2fa-toggle').prop('checked', false);
        showAlert('error', 'Failed to enable 2FA');
    });
}

// Confirm 2FA
function confirm2FA() {
    const code = $('#2fa-code').val();
    
    $.post('{{ route("admin.api.2fa.confirm") }}', {
        _token: '{{ csrf_token() }}',
        code: code
    })
    .done(function(response) {
        if (response.success) {
            showAlert('success', response.message);
            location.reload();
        } else {
            showAlert('error', response.message || 'Invalid code');
        }
    })
    .fail(function() {
        showAlert('error', 'Failed to confirm 2FA');
    });
}

// Disable 2FA
function disable2FA() {
    $.post('{{ route("admin.api.2fa.disable") }}', {
        _token: '{{ csrf_token() }}'
    })
    .done(function(response) {
        if (response.success) {
            $('#2fa-setup').hide();
            $('#2fa-toggle').prop('checked', false);
            showAlert('success', response.message);
            location.reload();
        } else {
            $('#2fa-toggle').prop('checked', true);
            showAlert('error', response.message || 'Failed to disable 2FA');
        }
    })
    .fail(function() {
        $('#2fa-toggle').prop('checked', true);
        showAlert('error', 'Failed to disable 2FA');
    });
}

// Generate recovery codes
function generateRecoveryCodes() {
    $.post('{{ route("admin.api.2fa.generate-recovery-codes") }}', {
        _token: '{{ csrf_token() }}'
    })
    .done(function(response) {
        if (response.success) {
            let codesHtml = '<div class="alert alert-warning"><ul>';
            response.data.recovery_codes.forEach(function(code) {
                codesHtml += '<li>' + code + '</li>';
            });
            codesHtml += '</ul></div>';
            $('#recovery-codes-display').html(codesHtml);
            showAlert('success', response.message);
        } else {
            showAlert('error', response.message || 'Failed to generate recovery codes');
        }
    })
    .fail(function() {
        showAlert('error', 'Failed to generate recovery codes');
    });
}

// Load security logs
function loadSecurityLogs() {
    $.get('{{ route("admin.api.security-logs") }}')
        .done(function(response) {
            if (response.success) {
                let logsHtml = '';
                response.data.forEach(function(log) {
                    logsHtml += '<tr>';
                    logsHtml += '<td>' + log.created_at + '</td>';
                    logsHtml += '<td>' + log.action + '</td>';
                    logsHtml += '<td>' + log.ip_address + '</td>';
                    logsHtml += '<td>' + (log.successful ? '<span class="badge bg-success">Success</span>' : '<span class="badge bg-danger">Failed</span>') + '</td>';
                    logsHtml += '</tr>';
                });
                $('#security-logs-table tbody').html(logsHtml);
            }
        })
        .fail(function() {
            showAlert('error', 'Failed to load security logs');
        });
}

// Load recent activity
function loadRecentActivity() {
    $.get('{{ route("admin.api.security-logs") }}')
        .done(function(response) {
            if (response.success) {
                let activityHtml = '';
                response.data.slice(0, 5).forEach(function(log) {
                    activityHtml += '<li class="list-group-item">';
                    activityHtml += '<div class="d-flex justify-content-between">';
                    activityHtml += '<span>' + log.action + '</span>';
                    activityHtml += '<small class="text-muted">' + log.created_at + '</small>';
                    activityHtml += '</div>';
                    activityHtml += '<small class="text-muted">' + log.ip_address + '</small>';
                    activityHtml += '</li>';
                });
                $('#recent-activity').html(activityHtml);
            }
        })
        .fail(function() {
            showAlert('error', 'Failed to load recent activity');
        });
}
</script>
@endpush
@endsection