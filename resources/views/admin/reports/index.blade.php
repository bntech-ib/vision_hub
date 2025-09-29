@extends('admin.layouts.app')
@section('content')
<div class="container">
    <h1>Analytics & Reports</h1>
    <p>Comprehensive analytics and reporting dashboard</p>
    
    <div class="row">
        @foreach($reportTypes as $key => $report)
        <div class="col-md-4 mb-4">
            <div class="card dashboard-card">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="bi bi-{{ $report['icon'] }}"></i>
                        {{ $report['title'] }}
                    </h5>
                    <p class="card-text">{{ $report['description'] }}</p>
                    <a href="{{ route('admin.reports.' . $key) }}" class="btn btn-primary">View Report</a>
                </div>
            </div>
        </div>
        @endforeach
        
        <!-- Security Logs Report -->
        <div class="col-md-4 mb-4">
            <div class="card dashboard-card">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="bi bi-shield-lock"></i>
                        Security Logs
                    </h5>
                    <p class="card-text">Security threat detection, login attempts, and system security reports</p>
                    <a href="{{ route('admin.reports.security-logs') }}" class="btn btn-primary">View Report</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection