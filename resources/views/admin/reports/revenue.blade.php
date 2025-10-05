@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Revenue Reports</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <a href="{{ route('admin.reports.revenue', ['period' => 'today']) }}" class="btn btn-sm btn-outline-secondary">Today</a>
                <a href="{{ route('admin.reports.revenue', ['period' => 'week']) }}" class="btn btn-sm btn-outline-secondary">Week</a>
                <a href="{{ route('admin.reports.revenue', ['period' => 'month']) }}" class="btn btn-sm btn-outline-secondary">Month</a>
                <a href="{{ route('admin.reports.revenue', ['period' => 'quarter']) }}" class="btn btn-sm btn-outline-secondary">Quarter</a>
                <a href="{{ route('admin.reports.revenue', ['period' => 'year']) }}" class="btn btn-sm btn-outline-secondary">Year</a>
            </div>
            <a href="{{ route('admin.reports.index') }}" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-arrow-left"></i> Back to Reports
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3">
            <div class="card dashboard-card stat-card revenue">
                <div class="card-body">
                    <h5 class="card-title">Total Revenue</h5>
                    <h2 class="mb-0">₦{{ number_format($data['overview']['total_revenue'] ?? 0, 2) }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card dashboard-card stat-card">
                <div class="card-body">
                    <h5 class="card-title">Transactions</h5>
                    <h2 class="mb-0">{{ $data['overview']['total_transactions'] ?? 0 }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card dashboard-card stat-card">
                <div class="card-body">
                    <h5 class="card-title">Avg. Transaction</h5>
                    <h2 class="mb-0">₦{{ number_format($data['overview']['avg_transaction'] ?? 0, 2) }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card dashboard-card stat-card">
                <div class="card-body">
                    <h5 class="card-title">Refunds</h5>
                    <h2 class="mb-0">₦{{ number_format($data['overview']['total_refunds'] ?? 0, 2) }}</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card dashboard-card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Revenue Trends</h5>
                </div>
                <div class="card-body">
                    <canvas id="revenueTrendsChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card dashboard-card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Payment Methods</h5>
                </div>
                <div class="card-body">
                    <canvas id="paymentMethodsChart" height="300"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card dashboard-card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Customer Segments</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Segment</th>
                                    <th>Revenue</th>
                                    <th>Transactions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Premium Users</td>
                                    <td>₦{{ number_format($data['customer_segments']['premium_revenue'] ?? 0, 2) }}</td>
                                    <td>{{ $data['customer_segments']['premium_transactions'] ?? 0 }}</td>
                                </tr>
                                <tr>
                                    <td>Standard Users</td>
                                    <td>₦{{ number_format($data['customer_segments']['standard_revenue'] ?? 0, 2) }}</td>
                                    <td>{{ $data['customer_segments']['standard_transactions'] ?? 0 }}</td>
                                </tr>
                                <tr>
                                    <td>Free Users</td>
                                    <td>₦{{ number_format($data['customer_segments']['free_revenue'] ?? 0, 2) }}</td>
                                    <td>{{ $data['customer_segments']['free_transactions'] ?? 0 }}</td>
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
        // Revenue Trends Chart
        const trendsCtx = document.getElementById('revenueTrendsChart').getContext('2d');
        const trendsChart = new Chart(trendsCtx, {
            type: 'line',
            data: {
                labels: [], // Will be populated with dates
                datasets: [{
                    label: 'Revenue',
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

        // Payment Methods Chart
        const paymentCtx = document.getElementById('paymentMethodsChart').getContext('2d');
        const paymentChart = new Chart(paymentCtx, {
            type: 'pie',
            data: {
                labels: ['Credit Card', 'Bank Transfer', 'PayPal', 'Crypto'],
                datasets: [{
                    data: [
                        <?php echo e($data['payment_methods']['credit_card'] ?? 0); ?>,
                        <?php echo e($data['payment_methods']['bank_transfer'] ?? 0); ?>,
                        <?php echo e($data['payment_methods']['paypal'] ?? 0); ?>,
                        <?php echo e($data['payment_methods']['crypto'] ?? 0); ?>
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