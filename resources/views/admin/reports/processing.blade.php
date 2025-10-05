@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Processing Reports</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <a href="{{ route('admin.reports.processing', ['period' => 'today']) }}" class="btn btn-sm btn-outline-secondary">Today</a>
                <a href="{{ route('admin.reports.processing', ['period' => 'week']) }}" class="btn btn-sm btn-outline-secondary">Week</a>
                <a href="{{ route('admin.reports.processing', ['period' => 'month']) }}" class="btn btn-sm btn-outline-secondary">Month</a>
                <a href="{{ route('admin.reports.processing', ['period' => 'quarter']) }}" class="btn btn-sm btn-outline-secondary">Quarter</a>
                <a href="{{ route('admin.reports.processing', ['period' => 'year']) }}" class="btn btn-sm btn-outline-secondary">Year</a>
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
                    <h5 class="card-title">Total Jobs</h5>
                    <h2 class="mb-0">{{ $data['overview']['total_jobs'] ?? 0 }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card dashboard-card stat-card">
                <div class="card-body">
                    <h5 class="card-title">Jobs Processed</h5>
                    <h2 class="mb-0">{{ $data['overview']['jobs_processed'] ?? 0 }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card dashboard-card stat-card revenue">
                <div class="card-body">
                    <h5 class="card-title">Successful Jobs</h5>
                    <h2 class="mb-0">{{ $data['overview']['successful_jobs'] ?? 0 }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card dashboard-card stat-card">
                <div class="card-body">
                    <h5 class="card-title">Failed Jobs</h5>
                    <h2 class="mb-0">{{ $data['overview']['failed_jobs'] ?? 0 }}</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card dashboard-card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Processing Success Rates</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h3>Success Rate: {{ $data['success_rates']['success_rate'] ?? 0 }}%</h3>
                            <p>Total Processed: {{ $data['success_rates']['total_processed'] ?? 0 }}</p>
                            <p>Successful: {{ $data['success_rates']['successful'] ?? 0 }}</p>
                            <p>Failed: {{ $data['success_rates']['failed'] ?? 0 }}</p>
                        </div>
                        <div class="col-md-6">
                            <canvas id="successRateChart" height="200"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card dashboard-card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Job Type Distribution</h5>
                </div>
                <div class="card-body">
                    <canvas id="jobTypeChart" height="300"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card dashboard-card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Common Processing Errors</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Error Message</th>
                                    <th>Count</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($data['error_analysis'] ?? [] as $error)
                                    <tr>
                                        <td>{{ Str::limit($error->error_message, 50) }}</td>
                                        <td>{{ $error->count }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="text-center">No errors found</td>
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
        // Success Rate Chart
        const successCtx = document.getElementById('successRateChart').getContext('2d');
        const successChart = new Chart(successCtx, {
            type: 'doughnut',
            data: {
                labels: ['Successful', 'Failed'],
                datasets: [{
                    data: [
                        <?php echo e($data['success_rates']['successful'] ?? 0); ?>,
                        <?php echo e($data['success_rates']['failed'] ?? 0); ?>
                    ],
                    backgroundColor: [
                        'rgb(75, 192, 192)',
                        'rgb(255, 99, 132)'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });

        // Job Type Chart
        const jobTypeCtx = document.getElementById('jobTypeChart').getContext('2d');
        const jobTypeChart = new Chart(jobTypeCtx, {
            type: 'pie',
            data: {
                labels: [], // Will be populated with job types
                datasets: [{
                    data: [], // Will be populated with counts
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