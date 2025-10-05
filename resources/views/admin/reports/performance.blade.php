@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Performance Reports</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <a href="{{ route('admin.reports.performance', ['period' => 'today']) }}" class="btn btn-sm btn-outline-secondary">Today</a>
                <a href="{{ route('admin.reports.performance', ['period' => 'week']) }}" class="btn btn-sm btn-outline-secondary">Week</a>
                <a href="{{ route('admin.reports.performance', ['period' => 'month']) }}" class="btn btn-sm btn-outline-secondary">Month</a>
                <a href="{{ route('admin.reports.performance', ['period' => 'quarter']) }}" class="btn btn-sm btn-outline-secondary">Quarter</a>
                <a href="{{ route('admin.reports.performance', ['period' => 'year']) }}" class="btn btn-sm btn-outline-secondary">Year</a>
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
                    <h5 class="card-title">Avg. Response Time</h5>
                    <h2 class="mb-0">{{ number_format($data['overview']['avg_response_time'] ?? 0, 2) }}ms</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card dashboard-card stat-card">
                <div class="card-body">
                    <h5 class="card-title">Uptime</h5>
                    <h2 class="mb-0">{{ number_format($data['overview']['uptime'] ?? 0, 2) }}%</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card dashboard-card stat-card revenue">
                <div class="card-body">
                    <h5 class="card-title">Error Rate</h5>
                    <h2 class="mb-0">{{ number_format($data['overview']['error_rate'] ?? 0, 2) }}%</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card dashboard-card stat-card">
                <div class="card-body">
                    <h5 class="card-title">Peak Load</h5>
                    <h2 class="mb-0">{{ $data['overview']['peak_load'] ?? 0 }} req/s</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card dashboard-card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Response Times Over Time</h5>
                </div>
                <div class="card-body">
                    <canvas id="responseTimesChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card dashboard-card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Error Rates</h5>
                </div>
                <div class="card-body">
                    <canvas id="errorRatesChart" height="300"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card dashboard-card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Resource Usage</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Resource</th>
                                    <th>Current Usage</th>
                                    <th>Peak Usage</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>CPU</td>
                                    <td>{{ number_format($data['resource_usage']['cpu_current'] ?? 0, 2) }}%</td>
                                    <td>{{ number_format($data['resource_usage']['cpu_peak'] ?? 0, 2) }}%</td>
                                </tr>
                                <tr>
                                    <td>Memory</td>
                                    <td>{{ number_format($data['resource_usage']['memory_current'] ?? 0, 2) }}%</td>
                                    <td>{{ number_format($data['resource_usage']['memory_peak'] ?? 0, 2) }}%</td>
                                </tr>
                                <tr>
                                    <td>Disk I/O</td>
                                    <td>{{ number_format($data['resource_usage']['disk_io_current'] ?? 0, 2) }} MB/s</td>
                                    <td>{{ number_format($data['resource_usage']['disk_io_peak'] ?? 0, 2) }} MB/s</td>
                                </tr>
                                <tr>
                                    <td>Network</td>
                                    <td>{{ number_format($data['resource_usage']['network_current'] ?? 0, 2) }} Mbps</td>
                                    <td>{{ number_format($data['resource_usage']['network_peak'] ?? 0, 2) }} Mbps</td>
                                </tr>
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
                    <h5 class="card-title mb-0">Performance Recommendations</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Recommendation</th>
                                    <th>Priority</th>
                                    <th>Estimated Impact</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($data['recommendations'] ?? [] as $recommendation)
                                    <tr>
                                        <td>{{ $recommendation['description'] ?? 'N/A' }}</td>
                                        <td>
                                            @if(($recommendation['priority'] ?? '') === 'high')
                                                <span class="badge bg-danger">High</span>
                                            @elseif(($recommendation['priority'] ?? '') === 'medium')
                                                <span class="badge bg-warning">Medium</span>
                                            @else
                                                <span class="badge bg-info">Low</span>
                                            @endif
                                        </td>
                                        <td>{{ $recommendation['impact'] ?? 'N/A' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center">No recommendations available</td>
                                    </tr>
                                @endforelse
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
        // Response Times Chart
        const responseCtx = document.getElementById('responseTimesChart').getContext('2d');
        const responseChart = new Chart(responseCtx, {
            type: 'line',
            data: {
                labels: [], // Will be populated with timestamps
                datasets: [{
                    label: 'Response Time (ms)',
                    data: [], // Will be populated with response times
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

        // Error Rates Chart
        const errorCtx = document.getElementById('errorRatesChart').getContext('2d');
        const errorChart = new Chart(errorCtx, {
            type: 'bar',
            data: {
                labels: ['4xx Errors', '5xx Errors', 'Timeouts', 'Connection Errors'],
                datasets: [{
                    label: 'Error Count',
                    data: [
                        <?php echo e($data['error_rates']['4xx_errors'] ?? 0); ?>,
                        <?php echo e($data['error_rates']['5xx_errors'] ?? 0); ?>,
                        <?php echo e($data['error_rates']['timeouts'] ?? 0); ?>,
                        <?php echo e($data['error_rates']['connection_errors'] ?? 0); ?>
                    ],
                    backgroundColor: [
                        'rgb(255, 99, 132)',
                        'rgb(255, 159, 64)',
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