@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Security Monitoring</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <button type="button" class="btn btn-sm btn-outline-secondary me-2" id="refreshStats">
                <i class="bi bi-arrow-clockwise"></i> Refresh
            </button>
            <button type="button" class="btn btn-sm btn-outline-danger" id="clearCache">
                <i class="bi bi-trash"></i> Clear Cache
            </button>
        </div>
    </div>

    <!-- Security Overview Cards -->
    <div class="row mb-4">
        <div class="col-md-2">
            <div class="card dashboard-card stat-card">
                <div class="card-body">
                    <h5 class="card-title">Total Logs</h5>
                    <h2 class="mb-0" id="totalLogs">{{ $stats['total_logs'] ?? 0 }}</h2>
                    <small class="text-muted">All security events</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card dashboard-card stat-card revenue">
                <div class="card-body">
                    <h5 class="card-title">Threats Detected</h5>
                    <h2 class="mb-0" id="threatLogs">{{ $stats['threat_logs'] ?? 0 }}</h2>
                    <small class="text-muted">{{ $stats['threat_percentage'] ?? 0 }}% of all logs</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card dashboard-card stat-card users">
                <div class="card-body">
                    <h5 class="card-title">Failed Logins</h5>
                    <h2 class="mb-0" id="failedLogins">{{ $stats['failed_logins'] ?? 0 }}</h2>
                    <small class="text-muted">Unsuccessful login attempts</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card dashboard-card stat-card ads">
                <div class="card-body">
                    <h5 class="card-title">Login Success Rate</h5>
                    <h2 class="mb-0" id="loginSuccessRate">{{ $stats['login_success_rate'] ?? 0 }}%</h2>
                    <small class="text-muted">Successful vs failed logins</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card dashboard-card stat-card">
                <div class="card-body">
                    <h5 class="card-title">Blocked IPs</h5>
                    <h2 class="mb-0" id="blockedIPs">{{ $stats['blocked_ips'] ?? 0 }}</h2>
                    <small class="text-muted">Currently blocked</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card dashboard-card stat-card">
                <div class="card-body">
                    <h5 class="card-title">Active Users</h5>
                    <h2 class="mb-0" id="activeUsers">0</h2>
                    <small class="text-muted">Online now</small>
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

    <!-- Blocked IPs Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card dashboard-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Blocked IP Addresses</h5>
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#blockIPModal">
                        <i class="bi bi-plus-circle"></i> Block IP
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-sm">
                            <thead>
                                <tr>
                                    <th>IP Address</th>
                                    <th>Reason</th>
                                    <th>Blocked By</th>
                                    <th>Blocked At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="blockedIPsTable">
                                <!-- Blocked IPs will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-center mt-3">
                        <nav id="blockedIPsPagination">
                            <!-- Pagination will be loaded here -->
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity Section -->
    <div class="row">
        <div class="col-md-6">
            <div class="card dashboard-card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Recent Threats</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-sm">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>IP</th>
                                    <th>Description</th>
                                    <th>Time</th>
                                </tr>
                            </thead>
                            <tbody id="recentThreatsTable">
                                @forelse($stats['recent_threats'] ?? [] as $threat)
                                    <tr>
                                        <td>
                                            @php
                                                $details = json_decode($threat->details, true);
                                                $threatType = $details['threat_type'] ?? 'unknown';
                                            @endphp
                                            <span class="badge bg-danger">{{ ucfirst(str_replace('_', ' ', $threatType)) }}</span>
                                        </td>
                                        <td>{{ $threat->ip_address ?? 'N/A' }}</td>
                                        <td>{{ $details['description'] ?? 'N/A' }}</td>
                                        <td>{{ $threat->created_at->diffForHumans() }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center">No recent threats detected</td>
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
                                    <th>Last Seen</th>
                                </tr>
                            </thead>
                            <tbody id="suspiciousIPsTable">
                                @forelse($stats['suspicious_ips'] ?? [] as $ip)
                                    <tr>
                                        <td>{{ $ip->ip_address ?? 'N/A' }}</td>
                                        <td>{{ $ip->count }}</td>
                                        <td>{{ $ip->created_at ? $ip->created_at->diffForHumans() : 'N/A' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center">No suspicious IPs detected</td>
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

<!-- Block IP Modal -->
<div class="modal fade" id="blockIPModal" tabindex="-1" aria-labelledby="blockIPModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="blockIPModalLabel">Block IP Address</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="blockIPForm">
                    <div class="mb-3">
                        <label for="ipAddress" class="form-label">IP Address</label>
                        <input type="text" class="form-control" id="ipAddress" name="ip_address" required>
                        <div class="form-text">Enter the IP address you want to block</div>
                    </div>
                    <div class="mb-3">
                        <label for="blockReason" class="form-label">Reason (Optional)</label>
                        <textarea class="form-control" id="blockReason" name="reason" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="submitBlockIP">Block IP</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Initialize charts
    let threatsChart = null;
    let threatTypesChart = null;

    // Function to refresh stats
    function refreshStats() {
        fetch('{{ route("admin.security-monitoring.stats") }}')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update stats cards
                    document.getElementById('totalLogs').textContent = data.data.total_logs;
                    document.getElementById('threatLogs').textContent = data.data.threat_logs;
                    document.getElementById('failedLogins').textContent = data.data.failed_logins;
                    document.getElementById('loginSuccessRate').textContent = data.data.login_success_rate + '%';
                    document.getElementById('blockedIPs').textContent = data.data.blocked_ips;
                    
                    // Update recent threats table
                    updateRecentThreats(data.data.recent_threats);
                    
                    // Update suspicious IPs table
                    updateSuspiciousIPs(data.data.suspicious_ips);
                }
            })
            .catch(error => {
                console.error('Error fetching stats:', error);
                showAlert('error', 'Failed to refresh statistics');
            });
    }

    // Function to update recent threats table
    function updateRecentThreats(threats) {
        const tableBody = document.getElementById('recentThreatsTable');
        tableBody.innerHTML = '';
        
        if (!threats || threats.length === 0) {
            tableBody.innerHTML = '<tr><td colspan="4" class="text-center">No recent threats detected</td></tr>';
            return;
        }
        
        threats.forEach(threat => {
            try {
                const details = JSON.parse(threat.details);
                const threatType = details.threat_type || 'unknown';
                const description = details.description || 'N/A';
                const timeAgo = threat.created_at ? moment(threat.created_at).fromNow() : 'N/A';
                
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td><span class="badge bg-danger">${ucfirst(threatType.replace(/_/g, ' '))}</span></td>
                    <td>${threat.ip_address || 'N/A'}</td>
                    <td>${description}</td>
                    <td>${timeAgo}</td>
                `;
                tableBody.appendChild(row);
            } catch (e) {
                console.error('Error parsing threat details:', e);
            }
        });
    }

    // Function to update suspicious IPs table
    function updateSuspiciousIPs(ips) {
        const tableBody = document.getElementById('suspiciousIPsTable');
        tableBody.innerHTML = '';
        
        if (!ips || ips.length === 0) {
            tableBody.innerHTML = '<tr><td colspan="3" class="text-center">No suspicious IPs detected</td></tr>';
            return;
        }
        
        ips.forEach(ip => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${ip.ip_address || 'N/A'}</td>
                <td>${ip.count}</td>
                <td>${ip.created_at ? moment(ip.created_at).fromNow() : 'N/A'}</td>
            `;
            tableBody.appendChild(row);
        });
    }

    // Function to load blocked IPs
    function loadBlockedIPs(page = 1) {
        fetch(`{{ route("admin.security-monitoring.blocked-ips") }}?page=${page}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateBlockedIPsTable(data.data.data);
                    updateBlockedIPsPagination(data.data);
                }
            })
            .catch(error => {
                console.error('Error fetching blocked IPs:', error);
                showAlert('error', 'Failed to load blocked IPs');
            });
    }

    // Function to update blocked IPs table
    function updateBlockedIPsTable(blockedIPs) {
        const tableBody = document.getElementById('blockedIPsTable');
        tableBody.innerHTML = '';
        
        if (!blockedIPs || blockedIPs.length === 0) {
            tableBody.innerHTML = '<tr><td colspan="5" class="text-center">No blocked IP addresses</td></tr>';
            return;
        }
        
        blockedIPs.forEach(ip => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${ip.ip_address}</td>
                <td>${ip.reason || 'N/A'}</td>
                <td>${ip.blocker ? ip.blocker.name : 'System'}</td>
                <td>${ip.blocked_at ? moment(ip.blocked_at).format('YYYY-MM-DD HH:mm') : 'N/A'}</td>
                <td>
                    <button class="btn btn-sm btn-success unblock-btn" data-id="${ip.id}">
                        <i class="bi bi-check-circle"></i> Unblock
                    </button>
                </td>
            `;
            tableBody.appendChild(row);
        });
        
        // Add event listeners to unblock buttons
        document.querySelectorAll('.unblock-btn').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                unblockIP(id);
            });
        });
    }

    // Function to update blocked IPs pagination
    function updateBlockedIPsPagination(data) {
        const pagination = document.getElementById('blockedIPsPagination');
        pagination.innerHTML = '';
        
        if (data.last_page <= 1) {
            return;
        }
        
        let paginationHtml = '<ul class="pagination">';
        
        // Previous button
        if (data.current_page > 1) {
            paginationHtml += `<li class="page-item"><a class="page-link" href="#" data-page="${data.current_page - 1}">Previous</a></li>`;
        }
        
        // Page numbers
        for (let i = Math.max(1, data.current_page - 2); i <= Math.min(data.last_page, data.current_page + 2); i++) {
            if (i === data.current_page) {
                paginationHtml += `<li class="page-item active"><span class="page-link">${i}</span></li>`;
            } else {
                paginationHtml += `<li class="page-item"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`;
            }
        }
        
        // Next button
        if (data.current_page < data.last_page) {
            paginationHtml += `<li class="page-item"><a class="page-link" href="#" data-page="${data.current_page + 1}">Next</a></li>`;
        }
        
        paginationHtml += '</ul>';
        pagination.innerHTML = paginationHtml;
        
        // Add event listeners to pagination links
        pagination.querySelectorAll('.page-link').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const page = this.getAttribute('data-page');
                if (page) {
                    loadBlockedIPs(page);
                }
            });
        });
    }

    // Function to block an IP
    function blockIP() {
        const form = document.getElementById('blockIPForm');
        const formData = new FormData(form);
        
        fetch('{{ route("admin.security-monitoring.block-ip") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', data.message);
                document.getElementById('blockIPModal').querySelector('.btn-close').click();
                form.reset();
                loadBlockedIPs();
                refreshStats();
            } else {
                showAlert('error', data.message || 'Failed to block IP address');
            }
        })
        .catch(error => {
            console.error('Error blocking IP:', error);
            showAlert('error', 'Failed to block IP address');
        });
    }

    // Function to unblock an IP
    function unblockIP(id) {
        if (!confirm('Are you sure you want to unblock this IP address?')) {
            return;
        }
        
        fetch(`{{ url("admin/security-monitoring/unblock-ip") }}/${id}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                reason: 'Manual unblock by administrator'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', data.message);
                loadBlockedIPs();
                refreshStats();
            } else {
                showAlert('error', data.message || 'Failed to unblock IP address');
            }
        })
        .catch(error => {
            console.error('Error unblocking IP:', error);
            showAlert('error', 'Failed to unblock IP address');
        });
    }

    // Helper function to capitalize first letter
    function ucfirst(string) {
        return string.charAt(0).toUpperCase() + string.slice(1);
    }

    // Function to load threat analytics
    function loadThreatAnalytics() {
        fetch('{{ route("admin.security-monitoring.analytics.threats") }}?days=30')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update threats over time chart
                    updateThreatsChart(data.data.labels, data.data.threat_counts);
                    
                    // Update threat types chart
                    updateThreatTypesChart(data.data.threat_types);
                }
            })
            .catch(error => {
                console.error('Error fetching threat analytics:', error);
                showAlert('error', 'Failed to load threat analytics');
            });
    }

    // Function to update threats over time chart
    function updateThreatsChart(labels, data) {
        const ctx = document.getElementById('threatsChart').getContext('2d');
        
        if (threatsChart) {
            threatsChart.destroy();
        }
        
        threatsChart = new Chart(ctx, {
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

    // Function to update threat types chart
    function updateThreatTypesChart(data) {
        const ctx = document.getElementById('threatTypesChart').getContext('2d');
        const labels = Object.keys(data);
        const values = Object.values(data);
        
        if (threatTypesChart) {
            threatTypesChart.destroy();
        }
        
        threatTypesChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels.map(label => ucfirst(label.replace(/_/g, ' '))),
                datasets: [{
                    data: values,
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
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }

    // Event listeners
    document.addEventListener('DOMContentLoaded', function() {
        // Load initial analytics
        loadThreatAnalytics();
        
        // Load blocked IPs
        loadBlockedIPs();
        
        // Refresh button
        document.getElementById('refreshStats').addEventListener('click', function() {
            refreshStats();
            loadThreatAnalytics();
            loadBlockedIPs();
        });
        
        // Clear cache button
        document.getElementById('clearCache').addEventListener('click', function() {
            if (confirm('Are you sure you want to clear the security monitoring cache?')) {
                fetch('{{ route("admin.security-monitoring.clear-cache") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert('success', data.message);
                        refreshStats();
                        loadThreatAnalytics();
                        loadBlockedIPs();
                    } else {
                        showAlert('error', data.message || 'Failed to clear cache');
                    }
                })
                .catch(error => {
                    console.error('Error clearing cache:', error);
                    showAlert('error', 'Failed to clear cache');
                });
            }
        });
        
        // Block IP form submission
        document.getElementById('submitBlockIP').addEventListener('click', function() {
            blockIP();
        });
        
        // Enter key in IP address field
        document.getElementById('ipAddress').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                blockIP();
            }
        });
    });
</script>
@endsection