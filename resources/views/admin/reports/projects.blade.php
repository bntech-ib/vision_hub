@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Project Reports</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <a href="{{ route('admin.reports.projects', ['period' => 'today']) }}" class="btn btn-sm btn-outline-secondary">Today</a>
                <a href="{{ route('admin.reports.projects', ['period' => 'week']) }}" class="btn btn-sm btn-outline-secondary">Week</a>
                <a href="{{ route('admin.reports.projects', ['period' => 'month']) }}" class="btn btn-sm btn-outline-secondary">Month</a>
                <a href="{{ route('admin.reports.projects', ['period' => 'quarter']) }}" class="btn btn-sm btn-outline-secondary">Quarter</a>
                <a href="{{ route('admin.reports.projects', ['period' => 'year']) }}" class="btn btn-sm btn-outline-secondary">Year</a>
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
                    <h5 class="card-title">Total Projects</h5>
                    <h2 class="mb-0">{{ $data['overview']['total_projects'] ?? 0 }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card dashboard-card stat-card">
                <div class="card-body">
                    <h5 class="card-title">New Projects</h5>
                    <h2 class="mb-0">{{ $data['overview']['new_projects'] ?? 0 }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card dashboard-card stat-card revenue">
                <div class="card-body">
                    <h5 class="card-title">Active Projects</h5>
                    <h2 class="mb-0">{{ $data['overview']['active_projects'] ?? 0 }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card dashboard-card stat-card">
                <div class="card-body">
                    <h5 class="card-title">Completed Projects</h5>
                    <h2 class="mb-0">{{ $data['overview']['completed_projects'] ?? 0 }}</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card dashboard-card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Project Creation Trends</h5>
                </div>
                <div class="card-body">
                    <canvas id="creationTrendsChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card dashboard-card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Project Size Distribution</h5>
                </div>
                <div class="card-body">
                    <canvas id="sizeDistributionChart" height="300"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card dashboard-card">
                <div class="card-header">
                    <h5 class="card-title mb-0">User Project Statistics</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <tbody>
                                <tr>
                                    <td>Average Projects Per User</td>
                                    <td>{{ number_format($data['user_project_stats']['average_projects_per_user'] ?? 0, 2) }}</td>
                                </tr>
                                <tr>
                                    <td>Users With Projects</td>
                                    <td>{{ $data['user_project_stats']['users_with_projects'] ?? 0 }}</td>
                                </tr>
                                <tr>
                                    <td>Users Without Projects</td>
                                    <td>{{ $data['user_project_stats']['users_without_projects'] ?? 0 }}</td>
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
        // Creation Trends Chart
        const trendsCtx = document.getElementById('creationTrendsChart').getContext('2d');
        const trendsChart = new Chart(trendsCtx, {
            type: 'line',
            data: {
                labels: [], // Will be populated with dates
                datasets: [{
                    label: 'Projects Created',
                    data: [], // Will be populated with counts
                    borderColor: 'rgb(75, 192, 192)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });

        // Size Distribution Chart
        const sizeCtx = document.getElementById('sizeDistributionChart').getContext('2d');
        const sizeChart = new Chart(sizeCtx, {
            type: 'doughnut',
            data: {
                labels: ['Small (≤10)', 'Medium (≤50)', 'Large (≤200)', 'Enterprise (>200)'],
                datasets: [{
                    data: [
                        <?php echo e($data['size_distribution']['small'] ?? 0); ?>,
                        <?php echo e($data['size_distribution']['medium'] ?? 0); ?>,
                        <?php echo e($data['size_distribution']['large'] ?? 0); ?>,
                        <?php echo e($data['size_distribution']['enterprise'] ?? 0); ?>
                    ],
                    backgroundColor: [
                        'rgb(255, 99, 132)',
                        'rgb(54, 162, 235)',
                        'rgb(255, 205, 86)',
                        'rgb(75, 192, 192)'
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