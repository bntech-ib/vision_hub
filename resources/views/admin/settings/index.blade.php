@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-gear"></i> System Settings</h2>
    </div>

    <!-- Settings Navigation Tabs -->
    <ul class="nav nav-tabs mb-4" id="settingsTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="general-tab" data-bs-toggle="tab" data-bs-target="#general" type="button" role="tab">
                <i class="bi bi-sliders"></i> General
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="email-tab" data-bs-toggle="tab" data-bs-target="#email" type="button" role="tab">
                <i class="bi bi-envelope"></i> Email
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="storage-tab" data-bs-toggle="tab" data-bs-target="#storage" type="button" role="tab">
                <i class="bi bi-hdd"></i> Storage
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="processing-tab" data-bs-toggle="tab" data-bs-target="#processing" type="button" role="tab">
                <i class="bi bi-cpu"></i> Processing
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="security-tab" data-bs-toggle="tab" data-bs-target="#security" type="button" role="tab">
                <i class="bi bi-shield-lock"></i> Security
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="notifications-tab" data-bs-toggle="tab" data-bs-target="#notifications" type="button" role="tab">
                <i class="bi bi-bell"></i> Notifications
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="financial-tab" data-bs-toggle="tab" data-bs-target="#financial" type="button" role="tab">
                <i class="bi bi-currency-dollar"></i> Financial
            </button>
        </li>
    </ul>

    <!-- Settings Content -->
    <div class="tab-content" id="settingsTabContent">
        <!-- General Settings -->
        <div class="tab-pane fade show active" id="general" role="tabpanel">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">General Settings</h5>
                </div>
                <div class="card-body">
                    <form id="general-settings-form" method="POST" action="{{ route('admin.settings.general.update') }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="app_name" class="form-label">Application Name</label>
                                    <input type="text" class="form-control" id="app_name" name="app_name" value="{{ $settings['general']['app_name'] ?? '' }}" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="app_description" class="form-label">Application Description</label>
                                    <textarea class="form-control" id="app_description" name="app_description" rows="3">{{ $settings['general']['app_description'] ?? '' }}</textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="app_logo" class="form-label">Application Logo</label>
                                    <input type="file" class="form-control" id="app_logo" name="app_logo" accept="image/*">
                                    @if(!empty($settings['general']['app_logo']))
                                        <div class="mt-2">
                                            <img src="{{ Storage::url($settings['general']['app_logo']) }}" alt="Logo" class="img-thumbnail" style="max-height: 100px;">
                                        </div>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="timezone" class="form-label">Timezone</label>
                                    <select class="form-control" id="timezone" name="timezone" required>
                                        <option value="UTC" {{ ($settings['general']['timezone'] ?? 'UTC') == 'UTC' ? 'selected' : '' }}>UTC</option>
                                        <option value="America/New_York" {{ ($settings['general']['timezone'] ?? 'UTC') == 'America/New_York' ? 'selected' : '' }}>America/New_York</option>
                                        <option value="Europe/London" {{ ($settings['general']['timezone'] ?? 'UTC') == 'Europe/London' ? 'selected' : '' }}>Europe/London</option>
                                        <option value="Asia/Tokyo" {{ ($settings['general']['timezone'] ?? 'UTC') == 'Asia/Tokyo' ? 'selected' : '' }}>Asia/Tokyo</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="date_format" class="form-label">Date Format</label>
                                    <input type="text" class="form-control" id="date_format" name="date_format" value="{{ $settings['general']['date_format'] ?? 'Y-m-d' }}" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="currency" class="form-label">Currency</label>
                                    <input type="text" class="form-control" id="currency" name="currency" value="{{ $settings['general']['currency'] ?? 'USD' }}" maxlength="3" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="maintenance_message" class="form-label">Maintenance Message</label>
                                    <textarea class="form-control" id="maintenance_message" name="maintenance_message" rows="2">{{ $settings['general']['maintenance_message'] ?? '' }}</textarea>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Save General Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Email Settings -->
        <div class="tab-pane fade" id="email" role="tabpanel">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Email Settings</h5>
                </div>
                <div class="card-body">
                    <form id="email-settings-form" method="POST" action="{{ route('admin.settings.email.update') }}">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="mail_driver" class="form-label">Mail Driver</label>
                                    <select class="form-control" id="mail_driver" name="mail_driver" required>
                                        <option value="smtp" {{ ($settings['email']['mail_driver'] ?? 'smtp') == 'smtp' ? 'selected' : '' }}>SMTP</option>
                                        <option value="mailgun" {{ ($settings['email']['mail_driver'] ?? 'smtp') == 'mailgun' ? 'selected' : '' }}>Mailgun</option>
                                        <option value="ses" {{ ($settings['email']['mail_driver'] ?? 'smtp') == 'ses' ? 'selected' : '' }}>SES</option>
                                        <option value="sendmail" {{ ($settings['email']['mail_driver'] ?? 'smtp') == 'sendmail' ? 'selected' : '' }}>Sendmail</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3 smtp-settings">
                                    <label for="mail_host" class="form-label">SMTP Host</label>
                                    <input type="text" class="form-control" id="mail_host" name="mail_host" value="{{ $settings['email']['mail_host'] ?? '' }}">
                                </div>
                                
                                <div class="mb-3 smtp-settings">
                                    <label for="mail_port" class="form-label">SMTP Port</label>
                                    <input type="number" class="form-control" id="mail_port" name="mail_port" value="{{ $settings['email']['mail_port'] ?? '' }}">
                                </div>
                                
                                <div class="mb-3 smtp-settings">
                                    <label for="mail_username" class="form-label">SMTP Username</label>
                                    <input type="text" class="form-control" id="mail_username" name="mail_username" value="{{ $settings['email']['mail_username'] ?? '' }}">
                                </div>
                                
                                <div class="mb-3 smtp-settings">
                                    <label for="mail_password" class="form-label">SMTP Password</label>
                                    <input type="password" class="form-control" id="mail_password" name="mail_password" value="">
                                </div>
                                
                                <div class="mb-3 smtp-settings">
                                    <label for="mail_encryption" class="form-label">Encryption</label>
                                    <select class="form-control" id="mail_encryption" name="mail_encryption">
                                        <option value="tls" {{ ($settings['email']['mail_encryption'] ?? 'tls') == 'tls' ? 'selected' : '' }}>TLS</option>
                                        <option value="ssl" {{ ($settings['email']['mail_encryption'] ?? 'tls') == 'ssl' ? 'selected' : '' }}>SSL</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="mail_from_address" class="form-label">From Address</label>
                                    <input type="email" class="form-control" id="mail_from_address" name="mail_from_address" value="{{ $settings['email']['mail_from_address'] ?? '' }}" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="mail_from_name" class="form-label">From Name</label>
                                    <input type="text" class="form-control" id="mail_from_name" name="mail_from_name" value="{{ $settings['email']['mail_from_name'] ?? '' }}" required>
                                </div>
                                
                                <div class="mb-3 mailgun-settings">
                                    <label for="mailgun_domain" class="form-label">Mailgun Domain</label>
                                    <input type="text" class="form-control" id="mailgun_domain" name="mailgun_domain" value="{{ $settings['email']['mailgun_domain'] ?? '' }}">
                                </div>
                                
                                <div class="mb-3 mailgun-settings">
                                    <label for="mailgun_secret" class="form-label">Mailgun Secret</label>
                                    <input type="password" class="form-control" id="mailgun_secret" name="mailgun_secret" value="">
                                </div>
                                
                                <div class="mt-4">
                                    <button type="button" class="btn btn-outline-secondary" id="test-email-btn">
                                        <i class="bi bi-envelope"></i> Test Email Configuration
                                    </button>
                                    <div id="test-email-result" class="mt-2"></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Save Email Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Storage Settings -->
        <div class="tab-pane fade" id="storage" role="tabpanel">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Storage Settings</h5>
                </div>
                <div class="card-body">
                    <form id="storage-settings-form" method="POST" action="{{ route('admin.settings.storage.update') }}">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="default_disk" class="form-label">Default Storage Disk</label>
                                    <select class="form-control" id="default_disk" name="default_disk" required>
                                        <option value="local" {{ ($settings['storage']['default_disk'] ?? 'local') == 'local' ? 'selected' : '' }}>Local</option>
                                        <option value="s3" {{ ($settings['storage']['default_disk'] ?? 'local') == 's3' ? 'selected' : '' }}>Amazon S3</option>
                                        <option value="gcs" {{ ($settings['storage']['default_disk'] ?? 'local') == 'gcs' ? 'selected' : '' }}>Google Cloud Storage</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="max_file_size" class="form-label">Max File Size (MB)</label>
                                    <input type="number" class="form-control" id="max_file_size" name="max_file_size" value="{{ $settings['storage']['max_file_size'] ?? 100 }}" min="1" max="1024">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="allowed_file_types" class="form-label">Allowed File Types</label>
                                    <input type="text" class="form-control" id="allowed_file_types" name="allowed_file_types" value="{{ implode(',', $settings['storage']['allowed_file_types'] ?? ['jpg', 'png', 'gif', 'bmp', 'webp']) }}" placeholder="jpg,png,gif,bmp,webp">
                                    <div class="form-text">Comma-separated list of allowed file extensions</div>
                                </div>
                                
                                <div class="mb-3 s3-settings">
                                    <label for="s3_key" class="form-label">S3 Access Key</label>
                                    <input type="text" class="form-control" id="s3_key" name="s3_key" value="{{ $settings['storage']['s3_key'] ?? '' }}">
                                </div>
                                
                                <div class="mb-3 s3-settings">
                                    <label for="s3_secret" class="form-label">S3 Secret Key</label>
                                    <input type="password" class="form-control" id="s3_secret" name="s3_secret" value="">
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3 s3-settings">
                                    <label for="s3_region" class="form-label">S3 Region</label>
                                    <input type="text" class="form-control" id="s3_region" name="s3_region" value="{{ $settings['storage']['s3_region'] ?? '' }}">
                                </div>
                                
                                <div class="mb-3 s3-settings">
                                    <label for="s3_bucket" class="form-label">S3 Bucket</label>
                                    <input type="text" class="form-control" id="s3_bucket" name="s3_bucket" value="{{ $settings['storage']['s3_bucket'] ?? '' }}">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="auto_delete_after_days" class="form-label">Auto Delete After (Days)</label>
                                    <input type="number" class="form-control" id="auto_delete_after_days" name="auto_delete_after_days" value="{{ $settings['storage']['auto_delete_after_days'] ?? '' }}" min="1">
                                    <div class="form-text">Leave empty to disable auto deletion</div>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="enable_compression" name="enable_compression" {{ ($settings['storage']['enable_compression'] ?? true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="enable_compression">
                                            Enable Image Compression
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="mt-4">
                                    <button type="button" class="btn btn-outline-secondary" id="test-storage-btn">
                                        <i class="bi bi-hdd"></i> Test Storage Configuration
                                    </button>
                                    <div id="test-storage-result" class="mt-2"></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Save Storage Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Processing Settings -->
        <div class="tab-pane fade" id="processing" role="tabpanel">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Processing Settings</h5>
                </div>
                <div class="card-body">
                    <form id="processing-settings-form" method="POST" action="{{ route('admin.settings.processing.update') }}">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="max_concurrent_jobs" class="form-label">Max Concurrent Jobs</label>
                                    <input type="number" class="form-control" id="max_concurrent_jobs" name="max_concurrent_jobs" value="{{ $settings['processing']['max_concurrent_jobs'] ?? 5 }}" min="1" max="50">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="job_timeout" class="form-label">Job Timeout (Seconds)</label>
                                    <input type="number" class="form-control" id="job_timeout" name="job_timeout" value="{{ $settings['processing']['job_timeout'] ?? 300 }}" min="30" max="3600">
                                </div>
                                
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="retry_failed_jobs" name="retry_failed_jobs" {{ ($settings['processing']['retry_failed_jobs'] ?? true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="retry_failed_jobs">
                                            Retry Failed Jobs
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="max_retries" class="form-label">Max Retries</label>
                                    <input type="number" class="form-control" id="max_retries" name="max_retries" value="{{ $settings['processing']['max_retries'] ?? 3 }}" min="0" max="10">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="cleanup_completed_jobs_after_days" class="form-label">Cleanup Completed Jobs After (Days)</label>
                                    <input type="number" class="form-control" id="cleanup_completed_jobs_after_days" name="cleanup_completed_jobs_after_days" value="{{ $settings['processing']['cleanup_completed_jobs_after_days'] ?? 30 }}" min="1" max="365">
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="enable_job_notifications" name="enable_job_notifications" {{ ($settings['processing']['enable_job_notifications'] ?? true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="enable_job_notifications">
                                            Enable Job Notifications
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="processing_quality" class="form-label">Processing Quality</label>
                                    <select class="form-control" id="processing_quality" name="processing_quality" required>
                                        <option value="low" {{ ($settings['processing']['processing_quality'] ?? 'medium') == 'low' ? 'selected' : '' }}>Low</option>
                                        <option value="medium" {{ ($settings['processing']['processing_quality'] ?? 'medium') == 'medium' ? 'selected' : '' }}>Medium</option>
                                        <option value="high" {{ ($settings['processing']['processing_quality'] ?? 'medium') == 'high' ? 'selected' : '' }}>High</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="enable_ai_enhancement" name="enable_ai_enhancement" {{ ($settings['processing']['enable_ai_enhancement'] ?? false) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="enable_ai_enhancement">
                                            Enable AI Enhancement
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Save Processing Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Security Settings -->
        <div class="tab-pane fade" id="security" role="tabpanel">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Security Settings</h5>
                </div>
                <div class="card-body">
                    <form id="security-settings-form" method="POST" action="{{ route('admin.settings.security.update') }}">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="session_lifetime" class="form-label">Session Lifetime (Minutes)</label>
                                    <input type="number" class="form-control" id="session_lifetime" name="session_lifetime" value="{{ $settings['security']['session_lifetime'] ?? 120 }}" min="15" max="1440">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="password_min_length" class="form-label">Minimum Password Length</label>
                                    <input type="number" class="form-control" id="password_min_length" name="password_min_length" value="{{ $settings['security']['password_min_length'] ?? 8 }}" min="6" max="20">
                                </div>
                                
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="require_email_verification" name="require_email_verification" {{ ($settings['security']['require_email_verification'] ?? true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="require_email_verification">
                                            Require Email Verification
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="enable_two_factor" name="enable_two_factor" {{ ($settings['security']['enable_two_factor'] ?? false) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="enable_two_factor">
                                            Enable Two-Factor Authentication
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="max_login_attempts" class="form-label">Max Login Attempts</label>
                                    <input type="number" class="form-control" id="max_login_attempts" name="max_login_attempts" value="{{ $settings['security']['max_login_attempts'] ?? 5 }}" min="3" max="10">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="lockout_duration" class="form-label">Lockout Duration (Minutes)</label>
                                    <input type="number" class="form-control" id="lockout_duration" name="lockout_duration" value="{{ $settings['security']['lockout_duration'] ?? 15 }}" min="1" max="60">
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="enable_captcha" name="enable_captcha" {{ ($settings['security']['enable_captcha'] ?? false) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="enable_captcha">
                                            Enable CAPTCHA
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="api_rate_limit" class="form-label">API Rate Limit (Requests/Minute)</label>
                                    <input type="number" class="form-control" id="api_rate_limit" name="api_rate_limit" value="{{ $settings['security']['api_rate_limit'] ?? 1000 }}" min="60" max="10000">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="allowed_ips" class="form-label">Allowed IPs</label>
                                    <textarea class="form-control" id="allowed_ips" name="allowed_ips" rows="3" placeholder="192.168.1.1&#10;10.0.0.0/8">{{ implode("\n", $settings['security']['allowed_ips'] ?? []) }}</textarea>
                                    <div class="form-text">One IP or CIDR block per line</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Save Security Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Notification Settings -->
        <div class="tab-pane fade" id="notifications" role="tabpanel">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Notification Settings</h5>
                </div>
                <div class="card-body">
                    <form id="notifications-settings-form" method="POST" action="{{ route('admin.settings.notifications.update') }}">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="admin_email_notifications" name="admin_email_notifications" {{ ($settings['notifications']['admin_email_notifications'] ?? true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="admin_email_notifications">
                                            Admin Email Notifications
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="user_registration_notification" name="user_registration_notification" {{ ($settings['notifications']['user_registration_notification'] ?? true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="user_registration_notification">
                                            User Registration Notification
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="failed_job_notification" name="failed_job_notification" {{ ($settings['notifications']['failed_job_notification'] ?? true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="failed_job_notification">
                                            Failed Job Notification
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="system_error_notification" name="system_error_notification" {{ ($settings['notifications']['system_error_notification'] ?? true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="system_error_notification">
                                            System Error Notification
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="daily_report_notification" name="daily_report_notification" {{ ($settings['notifications']['daily_report_notification'] ?? false) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="daily_report_notification">
                                            Daily Report Notification
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="notification_email" class="form-label">Notification Email</label>
                                    <input type="email" class="form-control" id="notification_email" name="notification_email" value="{{ $settings['notifications']['notification_email'] ?? 'admin@visionhub.com' }}" required>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="slack_webhook_url" class="form-label">Slack Webhook URL</label>
                                    <input type="url" class="form-control" id="slack_webhook_url" name="slack_webhook_url" value="{{ $settings['notifications']['slack_webhook_url'] ?? '' }}">
                                </div>
                                
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="enable_slack_notifications" name="enable_slack_notifications" {{ ($settings['notifications']['enable_slack_notifications'] ?? false) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="enable_slack_notifications">
                                            Enable Slack Notifications
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="enable_sms_notifications" name="enable_sms_notifications" {{ ($settings['notifications']['enable_sms_notifications'] ?? false) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="enable_sms_notifications">
                                            Enable SMS Notifications
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="mb-3 sms-settings">
                                    <label for="sms_provider" class="form-label">SMS Provider</label>
                                    <select class="form-control" id="sms_provider" name="sms_provider">
                                        <option value="twilio" {{ ($settings['notifications']['sms_provider'] ?? 'twilio') == 'twilio' ? 'selected' : '' }}>Twilio</option>
                                        <option value="nexmo" {{ ($settings['notifications']['sms_provider'] ?? 'twilio') == 'nexmo' ? 'selected' : '' }}>Nexmo</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Save Notification Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Financial Settings -->
        <div class="tab-pane fade" id="financial" role="tabpanel">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Financial Settings</h5>
                </div>
                <div class="card-body">
                    <form id="financial-settings-form" method="POST" action="{{ route('admin.settings.financial.update') }}">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-12">
                                <div class="card border-primary">
                                    <div class="card-header bg-primary text-white">
                                        <h6 class="mb-0">Withdrawal Portal Control</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="alert alert-info">
                                            <h5>Portal Status Control</h5>
                                            <p>Use this setting to globally enable or disable withdrawal requests for all users.</p>
                                            <ul>
                                                <li><strong>Enabled:</strong> Users can submit withdrawal requests</li>
                                                <li><strong>Disabled:</strong> Users cannot submit withdrawal requests (portal closed)</li>
                                            </ul>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <div class="form-check form-switch form-switch-lg d-flex align-items-center">
                                                <input class="form-check-input me-2" type="checkbox" id="withdrawal_enabled" name="withdrawal_enabled" {{ \App\Models\GlobalSetting::isWithdrawalEnabled() ? 'checked' : '' }} style="transform: scale(1.5);">
                                                <label class="form-check-label fw-bold" for="withdrawal_enabled">
                                                    <span class="withdrawal-status">
                                                        @if(\App\Models\GlobalSetting::isWithdrawalEnabled())
                                                            <span class="text-success"><i class="bi bi-unlock"></i> Withdrawals OPEN</span>
                                                        @else
                                                            <span class="text-danger"><i class="bi bi-lock"></i> Withdrawals CLOSED</span>
                                                        @endif
                                                    </span>
                                                </label>
                                            </div>
                                            <div class="form-text mt-2">
                                                When disabled, all users will be prevented from making withdrawal requests. The portal is currently 
                                                <strong class="portal-status">{{ \App\Models\GlobalSetting::isWithdrawalEnabled() ? 'OPEN' : 'CLOSED' }}</strong> for withdrawals.
                                            </div>
                                        </div>
                                        
                                        <div class="d-flex gap-2">
                                            @if(\App\Models\GlobalSetting::isWithdrawalEnabled())
                                                <button type="button" class="btn btn-danger btn-lg" id="toggle-withdrawal-btn" data-action="disable">
                                                    <i class="bi bi-x-circle"></i> Close Withdrawal Portal
                                                </button>
                                            @else
                                                <button type="button" class="btn btn-success btn-lg" id="toggle-withdrawal-btn" data-action="enable">
                                                    <i class="bi bi-check-circle"></i> Open Withdrawal Portal
                                                </button>
                                            @endif
                                        </div>
                                        
                                        <div class="mt-3">
                                            <small class="text-muted">
                                                <i class="bi bi-info-circle"></i> Note: Changes take effect immediately for all users.
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-end mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Save Financial Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        // Handle form submissions with AJAX
        $('form').on('submit', function(e) {
            e.preventDefault();
            var form = $(this);
            var submitBtn = form.find('button[type="submit"]');
            var originalText = submitBtn.html();
            
            submitBtn.prop('disabled', true).html('<i class="bi bi-arrow-repeat spinner-border spinner-border-sm"></i> Saving...');
            
            $.ajax({
                url: form.attr('action'),
                method: form.attr('method'),
                data: new FormData(this),
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        showAlert('success', response.message);
                    } else {
                        showAlert('error', response.message || 'An error occurred');
                    }
                },
                error: function(xhr) {
                    var errorMsg = 'An error occurred while saving settings';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    showAlert('error', errorMsg);
                },
                complete: function() {
                    submitBtn.prop('disabled', false).html(originalText);
                }
            });
        });
        
        // Test email configuration
        $('#test-email-btn').on('click', function() {
            var btn = $(this);
            var resultDiv = $('#test-email-result');
            var originalText = btn.html();
            
            btn.prop('disabled', true).html('<i class="bi bi-arrow-repeat spinner-border spinner-border-sm"></i> Testing...');
            resultDiv.html('');
            
            // Prompt for test email
            var testEmail = prompt('Enter email address to send test to:');
            if (!testEmail) {
                btn.prop('disabled', false).html(originalText);
                return;
            }
            
            $.ajax({
                url: '{{ route("admin.settings.test.email") }}',
                method: 'GET',
                data: { test_email: testEmail },
                success: function(response) {
                    if (response.success) {
                        resultDiv.html('<div class="alert alert-success">' + response.message + '</div>');
                    } else {
                        resultDiv.html('<div class="alert alert-danger">' + response.message + '</div>');
                    }
                },
                error: function(xhr) {
                    var errorMsg = 'An error occurred while testing email';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    resultDiv.html('<div class="alert alert-danger">' + errorMsg + '</div>');
                },
                complete: function() {
                    btn.prop('disabled', false).html(originalText);
                }
            });
        });
        
        // Test storage configuration
        $('#test-storage-btn').on('click', function() {
            var btn = $(this);
            var resultDiv = $('#test-storage-result');
            var originalText = btn.html();
            
            btn.prop('disabled', true).html('<i class="bi bi-arrow-repeat spinner-border spinner-border-sm"></i> Testing...');
            resultDiv.html('');
            
            $.ajax({
                url: '{{ route("admin.settings.test.storage") }}',
                method: 'GET',
                success: function(response) {
                    if (response.success) {
                        resultDiv.html('<div class="alert alert-success">' + response.message + '</div>');
                    } else {
                        resultDiv.html('<div class="alert alert-danger">' + response.message + '</div>');
                    }
                },
                error: function(xhr) {
                    var errorMsg = 'An error occurred while testing storage';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    resultDiv.html('<div class="alert alert-danger">' + errorMsg + '</div>');
                },
                complete: function() {
                    btn.prop('disabled', false).html(originalText);
                }
            });
        });
        
        // Show/hide driver-specific settings
        $('#mail_driver').on('change', function() {
            $('.smtp-settings, .mailgun-settings').hide();
            if ($(this).val() === 'smtp') {
                $('.smtp-settings').show();
            } else if ($(this).val() === 'mailgun') {
                $('.mailgun-settings').show();
            }
        }).trigger('change');
        
        // Handle withdrawal portal toggle
        $('#toggle-withdrawal-btn').on('click', function() {
            var btn = $(this);
            var action = btn.data('action');
            var originalText = btn.html();
            
            btn.prop('disabled', true).html('<i class="bi bi-arrow-repeat spinner-border spinner-border-sm"></i> Processing...');
            
            var url = action === 'enable' ? "{{ route('admin.settings.enable-withdrawal') }}" : "{{ route('admin.settings.disable-withdrawal') }}";
            
            $.ajax({
                url: url,
                method: 'PUT',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        // Toggle the button
                        if (action === 'enable') {
                            btn.removeClass('btn-success').addClass('btn-danger')
                               .html('<i class="bi bi-x-circle"></i> Close Withdrawal Portal')
                               .data('action', 'disable');
                            $('.withdrawal-status').html('<span class="text-success"><i class="bi bi-unlock"></i> Withdrawals OPEN</span>');
                            $('.portal-status').html('<strong>OPEN</strong>');
                        } else {
                            btn.removeClass('btn-danger').addClass('btn-success')
                               .html('<i class="bi bi-check-circle"></i> Open Withdrawal Portal')
                               .data('action', 'enable');
                            $('.withdrawal-status').html('<span class="text-danger"><i class="bi bi-lock"></i> Withdrawals CLOSED</span>');
                            $('.portal-status').html('<strong>CLOSED</strong>');
                        }
                        showAlert('success', response.message);
                    } else {
                        showAlert('error', response.message || 'An error occurred');
                    }
                },
                error: function(xhr) {
                    var errorMsg = 'An error occurred while processing request';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    showAlert('error', errorMsg);
                },
                complete: function() {
                    btn.prop('disabled', false);
                }
            });
        });
        
        $('#default_disk').on('change', function() {
            $('.s3-settings').hide();
            if ($(this).val() === 's3') {
                $('.s3-settings').show();
            }
        }).trigger('change');
        
        $('#enable_sms_notifications').on('change', function() {
            if ($(this).is(':checked')) {
                $('.sms-settings').show();
            } else {
                $('.sms-settings').hide();
            }
        }).trigger('change');
        
        // Helper function to show alerts
        function showAlert(type, message) {
            var alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
            var alertHtml = '<div class="alert ' + alertClass + ' alert-dismissible fade show" role="alert">' +
                message +
                '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
                '</div>';
            
            // Remove existing alerts
            $('.alert').remove();
            
            // Add new alert at the top of the content
            $('.content-wrapper').prepend(alertHtml);
            
            // Auto-hide after 5 seconds
            setTimeout(function() {
                $('.alert').fadeOut();
            }, 5000);
        }
    });
</script>
@endpush
@endsection