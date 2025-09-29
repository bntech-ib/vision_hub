@extends('admin.layouts.app')

@section('title', 'System Management')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="mb-0">System Management</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">System Management</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <ul class="nav nav-tabs nav-tabs-custom nav-justified" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#overview" role="tab">
                                <span class="d-block d-sm-none"><i class="fas fa-home"></i></span>
                                <span class="d-none d-sm-block">System Overview</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#maintenance" role="tab">
                                <span class="d-block d-sm-none"><i class="fas fa-cog"></i></span>
                                <span class="d-none d-sm-block">Maintenance</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#cache" role="tab">
                                <span class="d-block d-sm-none"><i class="fas fa-database"></i></span>
                                <span class="d-none d-sm-block">Cache Management</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#storage" role="tab">
                                <span class="d-block d-sm-none"><i class="fas fa-hdd"></i></span>
                                <span class="d-none d-sm-block">Storage</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#backup" role="tab">
                                <span class="d-block d-sm-none"><i class="fas fa-save"></i></span>
                                <span class="d-none d-sm-block">Backup</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#logs" role="tab">
                                <span class="d-block d-sm-none"><i class="fas fa-file-alt"></i></span>
                                <span class="d-none d-sm-block">Logs</span>
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content p-3">
                        <!-- System Overview Tab -->
                        <div class="tab-pane active" id="overview" role="tabpanel">
                            <div class="row">
                                <div class="col-md-6 col-xl-3">
                                    <div class="card bg-primary text-white">
                                        <div class="card-body">
                                            <div class="d-flex">
                                                <div class="flex-grow-1">
                                                    <p class="text-truncate font-size-14 mb-2">PHP Version</p>
                                                    <h4 class="mb-0">{{ phpversion() }}</h4>
                                                </div>
                                                <div class="avatar-sm">
                                                    <span class="avatar-title bg-soft-light rounded-3">
                                                        <i class="fas fa-code font-size-24"></i>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6 col-xl-3">
                                    <div class="card bg-success text-white">
                                        <div class="card-body">
                                            <div class="d-flex">
                                                <div class="flex-grow-1">
                                                    <p class="text-truncate font-size-14 mb-2">Laravel Version</p>
                                                    <h4 class="mb-0">{{ app()->version() }}</h4>
                                                </div>
                                                <div class="avatar-sm">
                                                    <span class="avatar-title bg-soft-light rounded-3">
                                                        <i class="fas fa-leaf font-size-24"></i>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6 col-xl-3">
                                    <div class="card bg-info text-white">
                                        <div class="card-body">
                                            <div class="d-flex">
                                                <div class="flex-grow-1">
                                                    <p class="text-truncate font-size-14 mb-2">Environment</p>
                                                    <h4 class="mb-0">{{ config('app.env') }}</h4>
                                                </div>
                                                <div class="avatar-sm">
                                                    <span class="avatar-title bg-soft-light rounded-3">
                                                        <i class="fas fa-server font-size-24"></i>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6 col-xl-3">
                                    <div class="card bg-warning text-white">
                                        <div class="card-body">
                                            <div class="d-flex">
                                                <div class="flex-grow-1">
                                                    <p class="text-truncate font-size-14 mb-2">Debug Mode</p>
                                                    <h4 class="mb-0">{{ config('app.debug') ? 'Enabled' : 'Disabled' }}</h4>
                                                </div>
                                                <div class="avatar-sm">
                                                    <span class="avatar-title bg-soft-light rounded-3">
                                                        <i class="fas fa-bug font-size-24"></i>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-xl-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h4 class="card-title mb-0">System Information</h4>
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table class="table table-bordered">
                                                    <tbody>
                                                        <tr>
                                                            <td><strong>Server Time</strong></td>
                                                            <td id="server-time">{{ now()->toDateTimeString() }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Timezone</strong></td>
                                                            <td>{{ config('app.timezone') }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Memory Usage</strong></td>
                                                            <td id="memory-usage">Loading...</td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Disk Usage</strong></td>
                                                            <td id="disk-usage">Loading...</td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Uptime</strong></td>
                                                            <td id="system-uptime">Loading...</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-xl-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h4 class="card-title mb-0">Application Configuration</h4>
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table class="table table-bordered">
                                                    <tbody>
                                                        <tr>
                                                            <td><strong>Cache Driver</strong></td>
                                                            <td>{{ config('cache.default') }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Session Driver</strong></td>
                                                            <td>{{ config('session.driver') }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Queue Driver</strong></td>
                                                            <td>{{ config('queue.default') }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Mail Driver</strong></td>
                                                            <td>{{ config('mail.default') }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Filesystem Driver</strong></td>
                                                            <td>{{ config('filesystems.default') }}</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Maintenance Tab -->
                        <div class="tab-pane" id="maintenance" role="tabpanel">
                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h4 class="card-title mb-0">Maintenance Mode</h4>
                                        </div>
                                        <div class="card-body">
                                            <div class="alert alert-info">
                                                <p>Maintenance mode allows you to disable your application for maintenance.</p>
                                                <p>When maintenance mode is enabled, a custom message will be displayed to users.</p>
                                            </div>
                                            
                                            @if(app()->isDownForMaintenance())
                                                <div class="alert alert-warning">
                                                    <h5>Maintenance Mode is Currently Enabled</h5>
                                                    <p>Your application is currently in maintenance mode.</p>
                                                </div>
                                                
                                                <form method="POST" action="{{ route('admin.system.maintenance.disable') }}">
                                                    @csrf
                                                    <button type="submit" class="btn btn-danger">Disable Maintenance Mode</button>
                                                </form>
                                            @else
                                                <form method="POST" action="{{ route('admin.system.maintenance.enable') }}">
                                                    @csrf
                                                    <div class="form-group mb-3">
                                                        <label for="message">Custom Message</label>
                                                        <input type="text" class="form-control" id="message" name="message" placeholder="We're currently performing maintenance. Please check back soon.">
                                                    </div>
                                                    
                                                    <div class="form-group mb-3">
                                                        <label for="allow">Allow IPs (comma separated)</label>
                                                        <input type="text" class="form-control" id="allow" name="allow" placeholder="192.168.1.1,10.0.0.1">
                                                    </div>
                                                    
                                                    <button type="submit" class="btn btn-primary">Enable Maintenance Mode</button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-lg-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h4 class="card-title mb-0">System Actions</h4>
                                        </div>
                                        <div class="card-body">
                                            <div class="d-grid gap-2">
                                                <button type="button" class="btn btn-warning" id="clear-cache-all">
                                                    <i class="fas fa-broom me-1"></i> Clear All Cache
                                                </button>
                                                <button type="button" class="btn btn-info" id="clear-config-cache">
                                                    <i class="fas fa-cog me-1"></i> Clear Config Cache
                                                </button>
                                                <button type="button" class="btn btn-info" id="clear-route-cache">
                                                    <i class="fas fa-route me-1"></i> Clear Route Cache
                                                </button>
                                                <button type="button" class="btn btn-info" id="clear-view-cache">
                                                    <i class="fas fa-eye me-1"></i> Clear View Cache
                                                </button>
                                                <button type="button" class="btn btn-secondary" id="restart-queue">
                                                    <i class="fas fa-sync-alt me-1"></i> Restart Queue Workers
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Cache Management Tab -->
                        <div class="tab-pane" id="cache" role="tabpanel">
                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h4 class="card-title mb-0">Cache Information</h4>
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table class="table table-bordered">
                                                    <tbody>
                                                        <tr>
                                                            <td><strong>Cache Driver</strong></td>
                                                            <td id="cache-driver">{{ config('cache.default') }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Cache Size</strong></td>
                                                            <td id="cache-size">Loading...</td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Cache Keys</strong></td>
                                                            <td id="cache-keys">Loading...</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-lg-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h4 class="card-title mb-0">Clear Cache</h4>
                                        </div>
                                        <div class="card-body">
                                            <div class="form-check mb-2">
                                                <input class="form-check-input cache-option" type="checkbox" id="cache-config" value="config" checked>
                                                <label class="form-check-label" for="cache-config">
                                                    Configuration Cache
                                                </label>
                                            </div>
                                            <div class="form-check mb-2">
                                                <input class="form-check-input cache-option" type="checkbox" id="cache-route" value="route" checked>
                                                <label class="form-check-label" for="cache-route">
                                                    Route Cache
                                                </label>
                                            </div>
                                            <div class="form-check mb-2">
                                                <input class="form-check-input cache-option" type="checkbox" id="cache-view" value="view" checked>
                                                <label class="form-check-label" for="cache-view">
                                                    View Cache
                                                </label>
                                            </div>
                                            <div class="form-check mb-2">
                                                <input class="form-check-input cache-option" type="checkbox" id="cache-app" value="cache" checked>
                                                <label class="form-check-label" for="cache-app">
                                                    Application Cache
                                                </label>
                                            </div>
                                            <div class="form-check mb-3">
                                                <input class="form-check-input cache-option" type="checkbox" id="cache-compiled" value="compiled" checked>
                                                <label class="form-check-label" for="cache-compiled">
                                                    Compiled Files
                                                </label>
                                            </div>
                                            <button type="button" class="btn btn-danger" id="clear-selected-cache">
                                                <i class="fas fa-broom me-1"></i> Clear Selected Cache
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Storage Tab -->
                        <div class="tab-pane" id="storage" role="tabpanel">
                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h4 class="card-title mb-0">Storage Information</h4>
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table class="table table-bordered">
                                                    <tbody>
                                                        <tr>
                                                            <td><strong>Default Disk</strong></td>
                                                            <td id="default-disk">{{ config('filesystems.default') }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Temp Files</strong></td>
                                                            <td id="temp-files">Loading...</td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Log Files Size</strong></td>
                                                            <td id="log-files-size">Loading...</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-lg-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h4 class="card-title mb-0">Storage Cleanup</h4>
                                        </div>
                                        <div class="card-body">
                                            <div class="form-check mb-2">
                                                <input class="form-check-input cleanup-option" type="checkbox" id="cleanup-temp" value="temp" checked>
                                                <label class="form-check-label" for="cleanup-temp">
                                                    Temporary Files
                                                </label>
                                            </div>
                                            <div class="form-check mb-2">
                                                <input class="form-check-input cleanup-option" type="checkbox" id="cleanup-logs" value="logs" checked>
                                                <label class="form-check-label" for="cleanup-logs">
                                                    Old Log Files
                                                </label>
                                            </div>
                                            <div class="form-check mb-2">
                                                <input class="form-check-input cleanup-option" type="checkbox" id="cleanup-cache" value="cache" checked>
                                                <label class="form-check-label" for="cleanup-cache">
                                                    Cache Files
                                                </label>
                                            </div>
                                            <div class="form-check mb-3">
                                                <input class="form-check-input cleanup-option" type="checkbox" id="cleanup-backups" value="old_backups" checked>
                                                <label class="form-check-label" for="cleanup-backups">
                                                    Old Backups
                                                </label>
                                            </div>
                                            
                                            <div class="form-group mb-3">
                                                <label for="older-than-days">Older Than (Days)</label>
                                                <input type="number" class="form-control" id="older-than-days" min="1" max="365" value="7">
                                            </div>
                                            
                                            <button type="button" class="btn btn-warning" id="cleanup-storage">
                                                <i class="fas fa-trash-alt me-1"></i> Cleanup Storage
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Backup Tab -->
                        <div class="tab-pane" id="backup" role="tabpanel">
                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h4 class="card-title mb-0">Create Backup</h4>
                                        </div>
                                        <div class="card-body">
                                            <div class="form-group mb-3">
                                                <label>Backup Type</label>
                                                <select class="form-control" id="backup-type">
                                                    <option value="full">Full Backup (Database + Files)</option>
                                                    <option value="database">Database Only</option>
                                                    <option value="files">Files Only</option>
                                                </select>
                                            </div>
                                            
                                            <button type="button" class="btn btn-success" id="create-backup">
                                                <i class="fas fa-save me-1"></i> Create Backup
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-lg-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h4 class="card-title mb-0">Available Backups</h4>
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table class="table table-centered table-nowrap mb-0">
                                                    <thead>
                                                        <tr>
                                                            <th>Backup ID</th>
                                                            <th>Date</th>
                                                            <th>Type</th>
                                                            <th>Size</th>
                                                            <th>Actions</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="backups-list">
                                                        <tr>
                                                            <td colspan="5" class="text-center">Loading backups...</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Logs Tab -->
                        <div class="tab-pane" id="logs" role="tabpanel">
                            <div class="row">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-header">
                                            <h4 class="card-title mb-0">System Logs</h4>
                                        </div>
                                        <div class="card-body">
                                            <div class="row mb-3">
                                                <div class="col-md-3">
                                                    <select class="form-control" id="log-level">
                                                        <option value="">All Levels</option>
                                                        <option value="error">Error</option>
                                                        <option value="warning">Warning</option>
                                                        <option value="info">Info</option>
                                                        <option value="debug">Debug</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-3">
                                                    <input type="date" class="form-control" id="log-date">
                                                </div>
                                                <div class="col-md-3">
                                                    <input type="number" class="form-control" id="log-limit" min="10" max="1000" value="100" placeholder="Limit">
                                                </div>
                                                <div class="col-md-3">
                                                    <button type="button" class="btn btn-primary" id="refresh-logs">
                                                        <i class="fas fa-sync-alt me-1"></i> Refresh
                                                    </button>
                                                </div>
                                            </div>
                                            
                                            <div class="table-responsive">
                                                <table class="table table-centered table-nowrap mb-0">
                                                    <thead>
                                                        <tr>
                                                            <th>Timestamp</th>
                                                            <th>Level</th>
                                                            <th>Message</th>
                                                            <th>Context</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="logs-list">
                                                        <tr>
                                                            <td colspan="4" class="text-center">Loading logs...</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Load system information
        loadSystemInfo();
        
        // Load cache information
        loadCacheInfo();
        
        // Load storage information
        loadStorageInfo();
        
        // Load backups
        loadBackups();
        
        // Load logs
        loadLogs();
        
        // Clear selected cache
        $('#clear-selected-cache').click(function() {
            const types = [];
            $('.cache-option:checked').each(function() {
                types.push($(this).val());
            });
            
            if (types.length === 0) {
                toastr.warning('Please select at least one cache type to clear');
                return;
            }
            
            $.ajax({
                url: '{{ route("admin.system.cache.clear") }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    types: types
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        loadCacheInfo();
                    } else {
                        toastr.error(response.message || 'Failed to clear cache');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', status, error);
                    console.error('Response:', xhr.responseText);
                    toastr.error('Failed to clear cache: ' + (xhr.responseJSON?.message || error));
                }
            });
        });
        
        // Clear all cache
        $('#clear-cache-all').click(function() {
            const types = ['config', 'route', 'view', 'cache', 'compiled'];
            
            $.ajax({
                url: '{{ route("admin.system.cache.clear") }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    types: types
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        loadCacheInfo();
                    } else {
                        toastr.error(response.message || 'Failed to clear cache');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', status, error);
                    console.error('Response:', xhr.responseText);
                    toastr.error('Failed to clear cache: ' + (xhr.responseJSON?.message || error));
                }
            });
        });
        
        // Clear specific caches
        $('#clear-config-cache').click(function() {
            clearCacheType('config');
        });
        
        $('#clear-route-cache').click(function() {
            clearCacheType('route');
        });
        
        $('#clear-view-cache').click(function() {
            clearCacheType('view');
        });
        
        // Restart queue workers
        $('#restart-queue').click(function() {
            $.ajax({
                url: '{{ route("admin.system.queue.restart") }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                    } else {
                        toastr.error(response.message || 'Failed to restart queue workers');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', status, error);
                    console.error('Response:', xhr.responseText);
                    toastr.error('Failed to restart queue workers: ' + (xhr.responseJSON?.message || error));
                }
            });
        });
        
        // Cleanup storage
        $('#cleanup-storage').click(function() {
            const types = [];
            $('.cleanup-option:checked').each(function() {
                types.push($(this).val());
            });
            
            if (types.length === 0) {
                toastr.warning('Please select at least one cleanup type');
                return;
            }
            
            const olderThanDays = $('#older-than-days').val();
            
            $.ajax({
                url: '{{ route("admin.system.storage.cleanup") }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    types: types,
                    older_than_days: olderThanDays
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        loadStorageInfo();
                    } else {
                        toastr.error(response.message || 'Failed to cleanup storage');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', status, error);
                    console.error('Response:', xhr.responseText);
                    toastr.error('Failed to cleanup storage: ' + (xhr.responseJSON?.message || error));
                }
            });
        });
        
        // Create backup
        $('#create-backup').click(function() {
            const type = $('#backup-type').val();
            
            // Show loading indicator
            const btn = $(this);
            const originalText = btn.html();
            btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Creating...').prop('disabled', true);
            
            $.ajax({
                url: '{{ route("admin.system.backup.create") }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    type: type
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        loadBackups();
                    } else {
                        toastr.error(response.message || 'Failed to create backup');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', status, error);
                    console.error('Response:', xhr.responseText);
                    toastr.error('Failed to create backup: ' + (xhr.responseJSON?.message || error));
                },
                complete: function() {
                    // Restore button
                    btn.html(originalText).prop('disabled', false);
                }
            });
        });
        
        // Refresh logs
        $('#refresh-logs').click(function() {
            loadLogs();
        });
    });
    
    function clearCacheType(type) {
        $.ajax({
            url: '{{ route("admin.system.cache.clear") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                types: [type]
            },
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    loadCacheInfo();
                } else {
                    toastr.error(response.message || 'Failed to clear cache');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', status, error);
                console.error('Response:', xhr.responseText);
                toastr.error('Failed to clear cache: ' + (xhr.responseJSON?.message || error));
            }
        });
    }
    
    function loadSystemInfo() {
        // This would be loaded via AJAX in a real implementation
        $('#memory-usage').text('Loading...');
        $('#disk-usage').text('Loading...');
        $('#system-uptime').text('Loading...');
        
        // Simulate loading
        setTimeout(function() {
            $('#memory-usage').text('512 MB');
            $('#disk-usage').text('10 GB / 100 GB (10%)');
            $('#system-uptime').text('7 days');
        }, 1000);
    }
    
    function loadCacheInfo() {
        $.ajax({
            url: '{{ route("admin.system.cache") }}',
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    $('#cache-size').text(response.data.cache_size || 'Unknown');
                    $('#cache-keys').text(response.data.cache_keys_count || '0');
                } else {
                    $('#cache-size').text('Error loading');
                    $('#cache-keys').text('Error loading');
                    toastr.error(response.message || 'Failed to load cache information');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', status, error);
                console.error('Response:', xhr.responseText);
                $('#cache-size').text('Error loading');
                $('#cache-keys').text('Error loading');
                toastr.error('Failed to load cache information: ' + (xhr.responseJSON?.message || error));
            }
        });
    }
    
    function loadStorageInfo() {
        $.ajax({
            url: '{{ route("admin.system.storage") }}',
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    $('#temp-files').text(response.data.temp_files || '0');
                    $('#log-files-size').text(response.data.log_files_size || '0 B');
                } else {
                    $('#temp-files').text('Error loading');
                    $('#log-files-size').text('Error loading');
                    toastr.error(response.message || 'Failed to load storage information');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', status, error);
                console.error('Response:', xhr.responseText);
                $('#temp-files').text('Error loading');
                $('#log-files-size').text('Error loading');
                toastr.error('Failed to load storage information: ' + (xhr.responseJSON?.message || error));
            }
        });
    }
    
    function loadBackups() {
        $.ajax({
            url: '{{ route("admin.system.backup") }}',
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    let html = '';
                    if (response.data && response.data.length > 0) {
                        response.data.forEach(function(backup) {
                            html += '<tr><td>' + backup.name + '</td><td>' + backup.date + '</td><td>' + backup.type + '</td><td>' + backup.size + '</td><td><button class="btn btn-sm btn-primary me-1" onclick="downloadBackup(\'' + backup.name + '\')">Download</button><button class="btn btn-sm btn-danger" onclick="deleteBackup(\'' + backup.name + '\')">Delete</button></td></tr>';
                        });
                    } else {
                        html = '<tr><td colspan="5" class="text-center">No backups found</td></tr>';
                    }
                    $('#backups-list').html(html);
                } else {
                    $('#backups-list').html('<tr><td colspan="5" class="text-center">Error loading backups</td></tr>');
                    toastr.error(response.message || 'Failed to load backups');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', status, error);
                console.error('Response:', xhr.responseText);
                $('#backups-list').html('<tr><td colspan="5" class="text-center">Error loading backups</td></tr>');
                toastr.error('Failed to load backups: ' + (xhr.responseJSON?.message || error));
            }
        });
    }
    
    function downloadBackup(filename) {
        window.location.href = '{{ route("admin.system.backup.download", ["filename" => "__FILENAME__"]) }}'.replace('__FILENAME__', filename);
    }
    
    function deleteBackup(filename) {
        if (confirm('Are you sure you want to delete this backup?')) {
            $.ajax({
                url: '{{ route("admin.system.backup.delete") }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    filename: filename
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        loadBackups();
                    } else {
                        toastr.error(response.message || 'Failed to delete backup');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', status, error);
                    console.error('Response:', xhr.responseText);
                    toastr.error('Failed to delete backup: ' + (xhr.responseJSON?.message || error));
                }
            });
        }
    }
    
    function loadLogs() {
        const level = $('#log-level').val();
        const date = $('#log-date').val();
        const limit = $('#log-limit').val();
        
        // Show loading indicator
        $('#logs-list').html('<tr><td colspan="4" class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading logs...</td></tr>');
        
        $.ajax({
            url: '{{ route("admin.system.logs") }}',
            method: 'GET',
            data: {
                level: level,
                date: date,
                limit: limit
            },
            success: function(response) {
                if (response.success) {
                    let html = '';
                    if (response.data && response.data.length > 0) {
                        response.data.forEach(function(log) {
                            // Add styling based on log level
                            let levelClass = '';
                            switch (log.level.toLowerCase()) {
                                case 'error':
                                case 'emergency':
                                case 'alert':
                                case 'critical':
                                    levelClass = 'table-danger';
                                    break;
                                case 'warning':
                                    levelClass = 'table-warning';
                                    break;
                                case 'info':
                                case 'notice':
                                    levelClass = 'table-info';
                                    break;
                                case 'debug':
                                    levelClass = 'table-secondary';
                                    break;
                            }
                            
                            html += '<tr class="' + levelClass + '"><td>' + log.timestamp + '</td><td>' + log.level + '</td><td><pre class="mb-0" style="white-space: pre-wrap; word-wrap: break-word;">' + log.message + '</pre></td><td>' + (log.context || '-') + '</td></tr>';
                        });
                    } else {
                        html = '<tr><td colspan="4" class="text-center">No logs found</td></tr>';
                    }
                    $('#logs-list').html(html);
                } else {
                    $('#logs-list').html('<tr><td colspan="4" class="text-center">Error loading logs</td></tr>');
                    toastr.error(response.message || 'Failed to load logs');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', status, error);
                console.error('Response:', xhr.responseText);
                $('#logs-list').html('<tr><td colspan="4" class="text-center">Error loading logs</td></tr>');
                toastr.error('Failed to load logs: ' + (xhr.responseJSON?.message || error));
            }
        });
    }
</script>
@endsection
