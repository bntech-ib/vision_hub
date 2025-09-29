@extends('admin.layouts.app')

@section('title', 'System Status')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="mb-0">System Status</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">System Status</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-3 col-md-6">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="flex-grow-1">
                            <p class="text-truncate font-size-14 mb-2">Application Status</p>
                            <h4 class="mb-0">Operational</h4>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-soft-light rounded-3">
                                <i class="fas fa-check-circle font-size-24"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="flex-grow-1">
                            <p class="text-truncate font-size-14 mb-2">Database</p>
                            <h4 class="mb-0">Connected</h4>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-soft-light rounded-3">
                                <i class="fas fa-database font-size-24"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="flex-grow-1">
                            <p class="text-truncate font-size-14 mb-2">Cache</p>
                            <h4 class="mb-0">{{ config('cache.default') }}</h4>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-soft-light rounded-3">
                                <i class="fas fa-memory font-size-24"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="flex-grow-1">
                            <p class="text-truncate font-size-14 mb-2">Maintenance</p>
                            <h4 class="mb-0">{{ app()->isDownForMaintenance() ? 'Enabled' : 'Disabled' }}</h4>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-soft-light rounded-3">
                                <i class="fas fa-tools font-size-24"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">System Information</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <td><strong>PHP Version</strong></td>
                                    <td>{{ phpversion() }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Laravel Version</strong></td>
                                    <td>{{ app()->version() }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Environment</strong></td>
                                    <td>{{ config('app.env') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Debug Mode</strong></td>
                                    <td>{{ config('app.debug') ? 'Enabled' : 'Disabled' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Timezone</strong></td>
                                    <td>{{ config('app.timezone') }}</td>
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
                    <h4 class="card-title mb-0">Services Status</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <td><strong>Database</strong></td>
                                    <td><span class="badge bg-success">Operational</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Cache</strong></td>
                                    <td><span class="badge bg-success">Operational</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Queue</strong></td>
                                    <td><span class="badge bg-success">Operational</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Mail</strong></td>
                                    <td><span class="badge bg-success">Operational</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Storage</strong></td>
                                    <td><span class="badge bg-success">Operational</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Quick Actions</h4>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2 d-md-flex justify-content-md-start">
                        <a href="{{ route('admin.system.index') }}" class="btn btn-primary">
                            <i class="fas fa-cogs me-1"></i> System Management
                        </a>
                        <a href="{{ route('admin.settings.index') }}" class="btn btn-info">
                            <i class="fas fa-cog me-1"></i> Settings
                        </a>
                        <button type="button" class="btn btn-warning" onclick="clearCache()">
                            <i class="fas fa-broom me-1"></i> Clear Cache
                        </button>
                        <button type="button" class="btn btn-danger" onclick="restartQueue()">
                            <i class="fas fa-sync-alt me-1"></i> Restart Queue
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function clearCache() {
        if (confirm('Are you sure you want to clear all cache?')) {
            $.ajax({
                url: '{{ route("admin.system.cache.clear") }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    types: ['config', 'route', 'view', 'cache', 'compiled']
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function(xhr) {
                    toastr.error('Failed to clear cache');
                }
            });
        }
    }
    
    function restartQueue() {
        if (confirm('Are you sure you want to restart queue workers?')) {
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
                        toastr.error(response.message);
                    }
                },
                error: function(xhr) {
                    toastr.error('Failed to restart queue workers');
                }
            });
        }
    }
</script>
@endsection
