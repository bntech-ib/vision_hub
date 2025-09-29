@extends('admin.layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Dashboard</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="refreshStats()">
                <i class="bi bi-arrow-clockwise"></i> Refresh
            </button>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card dashboard-card stat-card revenue">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">Total Revenue</div>
                        <div class="h5 mb-0 font-weight-bold" id="total-revenue">${{ number_format($stats['total_revenue'], 2) }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-currency-dollar fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card dashboard-card stat-card users">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">Total Users</div>
                        <div class="h5 mb-0 font-weight-bold" id="total-users">{{ number_format($stats['total_users']) }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-people fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card dashboard-card stat-card ads">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">Active Ads</div>
                        <div class="h5 mb-0 font-weight-bold" id="active-ads">{{ number_format($stats['active_ads']) }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-megaphone fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card dashboard-card stat-card">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">Total Courses</div>
                        <div class="h5 mb-0 font-weight-bold" id="total-courses">{{ number_format($stats['total_courses']) }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-book fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- New Statistics Cards -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card dashboard-card stat-card products">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">Total Products</div>
                        <div class="h5 mb-0 font-weight-bold" id="total-products">{{ number_format($stats['total_products']) }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-box-seam fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card dashboard-card stat-card sponsored-posts">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">Active Sponsored Posts</div>
                        <div class="h5 mb-0 font-weight-bold" id="active-sponsored-posts">{{ number_format($stats['active_sponsored_posts']) }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-megaphone-fill fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card dashboard-card stat-card transactions">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">Pending Transactions</div>
                        <div class="h5 mb-0 font-weight-bold" id="pending-transactions">{{ number_format($stats['pending_transactions']) }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-arrow-left-right fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card dashboard-card stat-card withdrawals">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">Completed Withdrawals</div>
                        <div class="h5 mb-0 font-weight-bold" id="completed-withdrawals">{{ number_format($stats['completed_withdrawals']) }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-cash-stack fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card dashboard-card stat-card brain-teasers">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">Active Brain Teasers</div>
                        <div class="h5 mb-0 font-weight-bold" id="active-brain-teasers">{{ number_format($stats['active_brain_teasers']) }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-puzzle-fill fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Section -->
<div class="row mb-4">
    <div class="col-xl-6 col-lg-6">
        <div class="card dashboard-card">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Revenue Overview</h6>
                <div class="dropdown no-arrow">
                    <select class="form-select form-select-sm" id="revenue-period" onchange="updateRevenueChart()">
                        <option value="7">Last 7 Days</option>
                        <option value="30" selected>Last 30 Days</option>
                        <option value="90">Last 90 Days</option>
                    </select>
                </div>
            </div>
            <div class="card-body">
                <div class="chart-area">
                    <canvas id="revenueChart" style="height: 320px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-6 col-lg-6">
        <div class="card dashboard-card">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Transactions & Withdrawals</h6>
                <div class="dropdown no-arrow">
                    <select class="form-select form-select-sm" id="wt-period" onchange="updateWTChart()">
                        <option value="7">Last 7 Days</option>
                        <option value="30" selected>Last 30 Days</option>
                        <option value="90">Last 90 Days</option>
                    </select>
                </div>
            </div>
            <div class="card-body">
                <div class="chart-bar">
                    <canvas id="wtChart" style="height: 320px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-12 col-lg-12">
        <div class="card dashboard-card">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">User Activity</h6>
            </div>
            <div class="card-body">
                <div class="chart-pie pt-4 pb-2">
                    <canvas id="userActivityChart" style="height: 245px;"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity -->
<div class="row">
    <div class="col-lg-6 mb-4">
        <div class="card dashboard-card">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Recent Transactions</h6>
                <a href="{{ route('admin.transactions.index') }}" class="btn btn-sm btn-primary">View All</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-borderless" id="recent-transactions">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Type</th>
                                <th>Amount</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentTransactions as $transaction)
                                <tr>
                                    <td>{{ $transaction->user->name }}</td>
                                    <td>
                                        <span class="badge bg-{{ $transaction->type === 'credit' ? 'success' : 'warning' }}">
                                            {{ ucfirst($transaction->type) }}
                                        </span>
                                    </td>
                                    <td>${{ number_format($transaction->amount, 2) }}</td>
                                    <td>{{ $transaction->created_at->format('M d, Y') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">No recent transactions</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6 mb-4">
        <div class="card dashboard-card">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">System Status</h6>
                <a href="{{ route('admin.system-status') }}" class="btn btn-sm btn-outline-secondary">Details</a>
            </div>
            <div class="card-body">
                <div id="system-status">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>Database Connection</span>
                        <span class="badge bg-success">Online</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>Cache System</span>
                        <span class="badge bg-success">Online</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>Storage Space</span>
                        <span class="badge bg-warning">78% Used</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span>Queue Processing</span>
                        <span class="badge bg-success">Active</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Revenue Chart
let revenueChart = null;
let wtChart = null;

function initializeRevenueChart(data) {
    const ctx = document.getElementById('revenueChart').getContext('2d');
    
    if (revenueChart) {
        revenueChart.destroy();
    }
    
    revenueChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.labels,
            datasets: [{
                label: 'Revenue',
                data: data.values,
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.1)',
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
                        callback: function(value) {
                            return '$' + value.toLocaleString();
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
}

// Transactions & Withdrawals Chart
function initializeWTChart(data) {
    const ctx = document.getElementById('wtChart').getContext('2d');
    
    if (wtChart) {
        wtChart.destroy();
    }
    
    wtChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.labels,
            datasets: [
                {
                    label: 'Transactions',
                    data: data.transactions,
                    backgroundColor: 'rgba(54, 162, 235, 0.7)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Withdrawals',
                    data: data.withdrawals,
                    backgroundColor: 'rgba(255, 99, 132, 0.7)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 1
                }
            ]
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
            },
            plugins: {
                legend: {
                    position: 'top',
                }
            }
        }
    });
}

// User Activity Chart
function initializeUserActivityChart(data) {
    const ctx = document.getElementById('userActivityChart').getContext('2d');
    
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: data.labels,
            datasets: [{
                data: data.values,
                backgroundColor: [
                    '#4e73df',
                    '#1cc88a',
                    '#36b9cc',
                    '#f6c23e'
                ],
                hoverBackgroundColor: [
                    '#2e59d9',
                    '#17a673',
                    '#2c9faf',
                    '#f4b619'
                ],
                hoverBorderColor: "rgba(234, 236, 244, 1)",
            }],
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

function updateRevenueChart() {
    const period = document.getElementById('revenue-period').value;
    
    fetch(`{{ route('admin.analytics.revenue') }}?period=${period}`)
        .then(response => response.json())
        .then(data => {
            initializeRevenueChart(data);
        })
        .catch(error => {
            console.error('Error fetching revenue data:', error);
        });
}

function updateWTChart() {
    const period = document.getElementById('wt-period').value;
    
    fetch(`{{ route('admin.analytics.withdrawals-transactions') }}?period=${period}`)
        .then(response => response.json())
        .then(data => {
            initializeWTChart(data);
        })
        .catch(error => {
            console.error('Error fetching withdrawals & transactions data:', error);
        });
}

function refreshStats() {
    fetch("{{ route('admin.dashboard.stats') }}")
        .then(response => response.json())
        .then(data => {
            document.getElementById('total-revenue').textContent = '$' + parseFloat(data.total_revenue).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            document.getElementById('total-users').textContent = parseInt(data.total_users).toLocaleString();
            document.getElementById('active-ads').textContent = parseInt(data.active_ads).toLocaleString();
            document.getElementById('total-courses').textContent = parseInt(data.total_courses).toLocaleString();
            // Update new stats
            document.getElementById('total-products').textContent = parseInt(data.total_products).toLocaleString();
            document.getElementById('active-sponsored-posts').textContent = parseInt(data.active_sponsored_posts).toLocaleString();
            document.getElementById('pending-transactions').textContent = parseInt(data.pending_transactions).toLocaleString();
            document.getElementById('completed-withdrawals').textContent = parseInt(data.completed_withdrawals).toLocaleString();
            document.getElementById('active-brain-teasers').textContent = parseInt(data.active_brain_teasers).toLocaleString();
        })
        .catch(error => {
            console.error('Error refreshing stats:', error);
        });
}

// Initialize charts on page load
document.addEventListener('DOMContentLoaded', function() {
    // Initialize revenue chart with default 30 days
    updateRevenueChart();
    
    // Initialize transactions & withdrawals chart with default 30 days
    updateWTChart();
    
    // Initialize user activity chart
    fetch("{{ route('admin.analytics.user-activity') }}")
        .then(response => response.json())
        .then(data => {
            initializeUserActivityChart(data);
        })
        .catch(error => {
            console.error('Error fetching user activity data:', error);
        });
});
</script>
@endpush