@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Storage Reports</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <a href="{{ route('admin.reports.storage', ['period' => 'today']) }}" class="btn btn-sm btn-outline-secondary">Today</a>
                <a href="{{ route('admin.reports.storage', ['period' => 'week']) }}" class="btn btn-sm btn-outline-secondary">Week</a>
                <a href="{{ route('admin.reports.storage', ['period' => 'month']) }}" class="btn btn-sm btn-outline-secondary">Month</a>
                <a href="{{ route('admin.reports.storage', ['period' => 'quarter']) }}" class="btn btn-sm btn-outline-secondary">Quarter</a>
                <a href="{{ route('admin.reports.storage', ['period' => 'year']) }}" class="btn btn-sm btn-outline-secondary">Year</a>
            </div>
            <a href="{{ route('admin.reports.index') }}" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-arrow-left"></i> Back to Reports
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3">
            <div class="card dashboard-card stat-card">
                <div class="card-body">
                    <h5 class="card-title">Total Storage Used</h5>
                    <h2 class="mb-0">{{ number_format($data['overview']['total_storage'] ?? 0, 2) }} MB</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card dashboard-card stat-card">
                <div class="card-body">
                    <h5 class="card-title">Files Stored</h5>
                    <h2 class="mb-0">{{ $data['overview']['total_files'] ?? 0 }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card dashboard-card stat-card revenue">
                <div class="card-body">
                    <h5 class="card-title">Avg. File Size</h5>
                    <h2 class="mb-0">{{ number_format($data['overview']['avg_file_size'] ?? 0, 2) }} MB</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card dashboard-card stat-card">
                <div class="card-body">
                    <h5 class="card-title">Storage Growth</h5>
                    <h2 class="mb-0">{{ number_format($data['overview']['storage_growth'] ?? 0, 2) }}%</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card dashboard-card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Storage Growth Trends</h5>
                </div>
                <div class="card-body">
                    <canvas id="growthTrendsChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card dashboard-card">
                <div class="card-header">
                    <h5 class="card-title mb-0">File Type Analysis</h5>
                </div>
                <div class="card-body">
                    <canvas id="fileTypeChart" height="300"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card dashboard-card">
                <div class="card-header">
                    <h5 class="card-title mb-0">User Storage Usage</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Files</th>
                                    <th>Storage Used (MB)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($data['user_storage_usage'] ?? [] as $usage)
                                    <tr>
                                        <td>{{ $usage->user->name ?? 'N/A' }}</td>
                                        <td>{{ $usage->file_count ?? 0 }}</td>
                                        <td>{{ number_format($usage->storage_used ?? 0, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center">No data available</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card dashboard-card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Storage Optimization Opportunities</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Opportunity</th>
                                    <th>Potential Savings</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Duplicate Files</td>
                                    <td>{{ number_format($data['optimization_opportunities']['duplicate_files_savings'] ?? 0, 2) }} MB</td>
                                    <td><button class="btn btn-sm btn-outline-primary">Analyze</button></td>
                                </tr>
                                <tr>
                                    <td>Old Files</td>
                                    <td>{{ number_format($data['optimization_opportunities']['old_files_savings'] ?? 0, 2) }} MB</td>
                                    <td><button class="btn btn-sm btn-outline-primary">Review</button></td>
                                </tr>
                                <tr>
                                    <td>Compressed Files</td>
                                    <td>{{ number_format($data['optimization_opportunities']['compression_savings'] ?? 0, 2) }} MB</td>
                                    <td><button class="btn btn-sm btn-outline-primary">Optimize</button></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Growth Trends Chart
        const growthCtx = document.getElementById('growthTrendsChart').getContext('2d');
        const growthChart = new Chart(growthCtx, {
            type: 'line',
            data: {
                labels: [], // Will be populated with dates
                datasets: [{
                    label: 'Storage Used (MB)',
                    data: [], // Will be populated with amounts
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });

        // File Type Chart
        const fileTypeCtx = document.getElementById('fileTypeChart').getContext('2d');
        const fileTypeChart = new Chart(fileTypeCtx, {
            type: 'pie',
            data: {
                labels: ['Images', 'Documents', 'Videos', 'Audio', 'Other'],
                datasets: [{
                    data: [
                        <?php echo e($data['file_type_analysis']['images'] ?? 0); ?>,
                        <?php echo e($data['file_type_analysis']['documents'] ?? 0); ?>,
                        <?php echo e($data['file_type_analysis']['videos'] ?? 0); ?>,
                        <?php echo e($data['file_type_analysis']['audio'] ?? 0); ?>,
                        <?php echo e($data['file_type_analysis']['other'] ?? 0); ?>
                    ],
                    backgroundColor: [
                        'rgb(255, 99, 132)',
                        'rgb(54, 162, 235)',
                        'rgb(255, 205, 86)',
                        'rgb(75, 192, 192)',
                        'rgb(153, 102, 255)'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    });
</script>
@endpush
@endsection