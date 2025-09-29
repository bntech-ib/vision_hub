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
            <div class="card {{ $status['database']['status'] === 'ok' ? 'bg-success' : 'bg-danger' }} text-white">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="flex-grow-1">
                            <p class="text-truncate font-size-14 mb-2">Database</p>
                            <h4 class="mb-0">{{ $status['database']['status'] === 'ok' ? 'Operational' : 'Error' }}</h4>
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
            <div class="card {{ $status['storage']['status'] === 'ok' ? 'bg-success' : 'bg-danger' }} text-white">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="flex-grow-1">
                            <p class="text-truncate font-size-14 mb-2">Storage</p>
                            <h4 class="mb-0">{{ $status['storage']['status'] === 'ok' ? 'Accessible' : 'Error' }}</h4>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-soft-light rounded-3">
                                <i class="fas fa-hdd font-size-24"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card {{ $status['cache']['status'] === 'ok' ? 'bg-success' : 'bg-danger' }} text-white">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="flex-grow-1">
                            <p class="text-truncate font-size-14 mb-2">Cache</p>
                            <h4 class="mb-0">{{ $status['cache']['status'] === 'ok' ? 'Operational' : 'Error' }}</h4>
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
            <div class="card {{ $status['queue']['status'] === 'ok' ? 'bg-success' : 'bg-warning' }} text-white">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="flex-grow-1">
                            <p class="text-truncate font-size-14 mb-2">Queue</p>
                            <h4 class="mb-0">{{ $status['queue']['status'] === 'ok' ? 'Operational' : 'Warning' }}</h4>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-soft-light rounded-3">
                                <i class="fas fa-tasks font-size-24"></i>
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
                    <h4 class="card-title mb-0">Service Details</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <td><strong>Database</strong></td>
                                    <td>
                                        @if($status['database']['status'] === 'ok')
                                            <span class="badge bg-success">Operational</span>
                                        @else
                                            <span class="badge bg-danger">Error</span>
                                        @endif
                                    </td>
                                    <td>{{ $status['database']['message'] }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Storage</strong></td>
                                    <td>
                                        @if($status['storage']['status'] === 'ok')
                                            <span class="badge bg-success">Operational</span>
                                        @else
                                            <span class="badge bg-danger">Error</span>
                                        @endif
                                    </td>
                                    <td>{{ $status['storage']['message'] }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Cache</strong></td>
                                    <td>
                                        @if($status['cache']['status'] === 'ok')
                                            <span class="badge bg-success">Operational</span>
                                        @else
                                            <span class="badge bg-danger">Error</span>
                                        @endif
                                    </td>
                                    <td>{{ $status['cache']['message'] }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Queue</strong></td>
                                    <td>
                                        @if($status['queue']['status'] === 'ok')
                                            <span class="badge bg-success">Operational</span>
                                        @else
                                            <span class="badge bg-warning">Warning</span>
                                        @endif
                                    </td>
                                    <td>{{ $status['queue']['message'] }}</td>
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
                                    <td><strong>Cache Driver</strong></td>
                                    <td>{{ config('cache.default') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Queue Driver</strong></td>
                                    <td>{{ config('queue.default') }}</td>
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
                            <i class="fas fa-cogs me-1"></i> Full System Management
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
                        setTimeout(() => {
                            location.reload();
                        }, 1000);
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