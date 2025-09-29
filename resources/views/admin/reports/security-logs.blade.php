@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Security Logs Report</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <a href="{{ route('admin.reports.security-logs', ['period' => 'today', 'format' => 'json']) }}" 
                   class="btn btn-sm btn-outline-secondary">Today</a>
                <a href="{{ route('admin.reports.security-logs', ['period' => 'week', 'format' => 'json']) }}" 
                   class="btn btn-sm btn-outline-secondary">Week</a>
                <a href="{{ route('admin.reports.security-logs', ['period' => 'month', 'format' => 'json']) }}" 
                   class="btn btn-sm btn-outline-secondary">Month</a>
                <a href="{{ route('admin.reports.security-logs', ['period' => 'quarter', 'format' => 'json']) }}" 
                   class="btn btn-sm btn-outline-secondary">Quarter</a>
                <a href="{{ route('admin.reports.security-logs', ['period' => 'year', 'format' => 'json']) }}" 
                   class="btn btn-sm btn-outline-secondary">Year</a>
            </div>
        </div>
    </div>

    <!-- Security Overview Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card dashboard-card stat-card">
                <div class="card-body">
                    <h5 class="card-title">Total Logs</h5>
                    <h2 class="mb-0">{{ $data['overview']['total_logs'] ?? 0 }}</h2>
                    <small class="text-muted">All security events</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card dashboard-card stat-card revenue">
                <div class="card-body">
                    <h5 class="card-title">Threats Detected</h5>
                    <h2 class="mb-0">{{ $data['overview']['threat_logs'] ?? 0 }}</h2>
                    <small class="text-muted">{{ $data['overview']['threat_percentage'] ?? 0 }}% of all logs</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card dashboard-card stat-card users">
                <div class="card-body">
                    <h5 class="card-title">Failed Logins</h5>
                    <h2 class="mb-0">{{ $data['overview']['failed_logins'] ?? 0 }}</h2>
                    <small class="text-muted">Unsuccessful login attempts</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card dashboard-card stat-card ads">
                <div class="card-body">
                    <h5 class="card-title">Login Success Rate</h5>
                    <h2 class="mb-0">{{ $data['overview']['login_success_rate'] ?? 0 }}%</h2>
                    <small class="text-muted">Successful vs failed logins</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card dashboard-card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Threats Over Time</h5>
                </div>
                <div class="card-body">
                    <canvas id="threatsChart" height="300"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card dashboard-card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Top Threat Types</h5>
                </div>
                <div class="card-body">
                    <canvas id="threatTypesChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity Section -->
    <div class="row">
        <div class="col-md-6">
            <div class="card dashboard-card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Top Threats by Type</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-sm">
                            <thead>
                                <tr>
                                    <th>Threat Type</th>
                                    <th>Count</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($data['top_threats'] ?? [] as $type => $count)
                                    <tr>
                                        <td>{{ ucfirst(str_replace('_', ' ', $type)) }}</td>
                                        <td>{{ $count }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="text-center">No threat data available</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card dashboard-card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Suspicious IPs</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-sm">
                            <thead>
                                <tr>
                                    <th>IP Address</th>
                                    <th>Threat Count</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($data['suspicious_ips'] ?? [] as $ip)
                                    <tr>
                                        <td>{{ $ip['ip_address'] ?? 'N/A' }}</td>
                                        <td>{{ $ip['threat_count'] }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="text-center">No suspicious IPs detected</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Security Events -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card dashboard-card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Recent Security Events</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-sm">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Action</th>
                                    <th>IP Address</th>
                                    <th>User Agent</th>
                                    <th>Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($data['user_activity'] ?? [] as $event)
                                    <tr>
                                        <td>
                                            @if($event['user'])
                                                {{ $event['user']['name'] }} ({{ $event['user']['email'] }})
                                            @else
                                                Anonymous
                                            @endif
                                        </td>
                                        <td>
                                            @if($event['action'] === 'threat_detected')
                                                <span class="badge bg-danger">{{ ucfirst(str_replace('_', ' ', $event['action'])) }}</span>
                                            @elseif($event['action'] === 'login_failed')
                                                <span class="badge bg-warning">{{ ucfirst(str_replace('_', ' ', $event['action'])) }}</span>
                                            @elseif($event['action'] === 'login_successful')
                                                <span class="badge bg-success">{{ ucfirst(str_replace('_', ' ', $event['action'])) }}</span>
                                            @else
                                                <span class="badge bg-secondary">{{ ucfirst(str_replace('_', ' ', $event['action'])) }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $event['ip_address'] ?? 'N/A' }}</td>
                                        <td>{{ Str::limit($event['user_agent'] ?? 'N/A', 50) }}</td>
                                        <td>{{ \Carbon\Carbon::parse($event['created_at'])->diffForHumans() }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">No recent security events</td>
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

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Threats over time chart
        const threatsCtx = document.getElementById('threatsChart').getContext('2d');
        const threatsData = @json($data['threat_trends'] ?? []);
        
        if (threatsData.length > 0) {
            const labels = threatsData.map(item => item.date);
            const data = threatsData.map(item => item.count);
            
            new Chart(threatsCtx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Threats Detected',
                        data: data,
                        borderColor: 'rgb(255, 99, 132)',
                        backgroundColor: 'rgba(255, 99, 132, 0.2)',
                        tension: 0.1,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    }
                }
            });
        }
        
        // Threat types chart
        const threatTypesCtx = document.getElementById('threatTypesChart').getContext('2d');
        const threatTypesData = @json($data['top_threats'] ?? []);
        
        if (Object.keys(threatTypesData).length > 0) {
            const labels = Object.keys(threatTypesData).map(label => 
                label.charAt(0).toUpperCase() + label.slice(1).replace(/_/g, ' ')
            );
            const data = Object.values(threatTypesData);
            
            new Chart(threatTypesCtx, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: data,
                        backgroundColor: [
                            'rgb(255, 99, 132)',
                            'rgb(54, 162, 235)',
                            'rgb(255, 205, 86)',
                            'rgb(75, 192, 192)',
                            'rgb(153, 102, 255)',
                            'rgb(255, 159, 64)',
                            'rgb(199, 199, 199)',
                            'rgb(83, 102, 255)',
                            'rgb(255, 99, 255)',
                            'rgb(99, 255, 132)'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }
    });
</script>
@endsection