<?php

namespace App\Services;

use App\Models\BlockedIP;
use App\Models\SecurityLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class SecurityMonitoringService
{
    /**
     * Get security statistics for the dashboard
     */
    public function getSecurityStats(): array
    {
        $cacheKey = 'security_stats';
        $cacheDuration = 300; // 5 minutes
        
        return Cache::remember($cacheKey, $cacheDuration, function () {
            try {
                $totalLogs = SecurityLog::count();
                $threatLogs = SecurityLog::where('action', 'threat_detected')->count();
                $failedLogins = SecurityLog::where('action', 'login_failed')->count();
                $successfulLogins = SecurityLog::where('action', 'login_successful')->count();
                
                // Get recent threats
                $recentThreats = SecurityLog::where('action', 'threat_detected')
                    ->orderBy('created_at', 'desc')
                    ->limit(10)
                    ->get();
                
                // Get top threat types
                $topThreatTypes = SecurityLog::where('action', 'threat_detected')
                    ->select('details')
                    ->get()
                    ->map(function ($log) {
                        $details = json_decode($log->details, true);
                        return $details['threat_type'] ?? 'unknown';
                    })
                    ->countBy()
                    ->sortDesc()
                    ->take(5);
                
                // Get suspicious IPs
                $suspiciousIPs = SecurityLog::where('action', 'threat_detected')
                    ->select('ip_address', DB::raw('COUNT(*) as count'))
                    ->groupBy('ip_address')
                    ->orderBy('count', 'desc')
                    ->limit(10)
                    ->get();
                
                // Get recent login attempts
                $recentLogins = SecurityLog::whereIn('action', ['login_successful', 'login_failed'])
                    ->orderBy('created_at', 'desc')
                    ->limit(10)
                    ->get();
                
                // Get blocked IPs count
                $blockedIPsCount = BlockedIP::where('is_active', true)->count();
                
                return [
                    'total_logs' => $totalLogs,
                    'threat_logs' => $threatLogs,
                    'failed_logins' => $failedLogins,
                    'successful_logins' => $successfulLogins,
                    'blocked_ips' => $blockedIPsCount,
                    'threat_percentage' => $totalLogs > 0 ? round(($threatLogs / $totalLogs) * 100, 2) : 0,
                    'login_success_rate' => ($successfulLogins + $failedLogins) > 0 ? 
                        round(($successfulLogins / ($successfulLogins + $failedLogins)) * 100, 2) : 0,
                    'recent_threats' => $recentThreats,
                    'top_threat_types' => $topThreatTypes,
                    'suspicious_ips' => $suspiciousIPs,
                    'recent_logins' => $recentLogins,
                ];
            } catch (\Exception $e) {
                // Return default values if there's an error
                return [
                    'total_logs' => 0,
                    'threat_logs' => 0,
                    'failed_logins' => 0,
                    'successful_logins' => 0,
                    'blocked_ips' => 0,
                    'threat_percentage' => 0,
                    'login_success_rate' => 0,
                    'recent_threats' => collect(),
                    'top_threat_types' => collect(),
                    'suspicious_ips' => collect(),
                    'recent_logins' => collect(),
                ];
            }
        });
    }
    
    /**
     * Get security logs with filtering options
     */
    public function getSecurityLogs(array $filters = []): array
    {
        $query = SecurityLog::with('user:id,name,email');
        
        // Apply filters
        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }
        
        if (!empty($filters['action'])) {
            $query->where('action', $filters['action']);
        }
        
        if (!empty($filters['ip_address'])) {
            $query->where('ip_address', $filters['ip_address']);
        }
        
        if (!empty($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }
        
        if (!empty($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }
        
        if (!empty($filters['threat_type'])) {
            // Special filter for threat types
            $query->where('action', 'threat_detected')
                ->where('details', 'like', '%"threat_type":"' . $filters['threat_type'] . '"%');
        }
        
        // Get paginated results
        $perPage = $filters['per_page'] ?? 50;
        $logs = $query->orderBy('created_at', 'desc')
            ->paginate($perPage);
        
        return [
            'logs' => $logs,
            'total' => $logs->total(),
        ];
    }
    
    /**
     * Get threat analytics for charts
     */
    public function getThreatAnalytics(int $days = 30): array
    {
        $startDate = now()->subDays($days);
        
        // Get threat counts by day
        $threatsByDay = SecurityLog::where('action', 'threat_detected')
            ->where('created_at', '>=', $startDate)
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        
        // Fill in missing dates with 0 values
        $dates = [];
        $threatCounts = [];
        $currentDate = $startDate->copy();
        
        while ($currentDate <= now()) {
            $dateStr = $currentDate->format('Y-m-d');
            $dates[] = $currentDate->format('M d');
            
            $threat = $threatsByDay->firstWhere('date', $dateStr);
            $threatCounts[] = $threat ? $threat->count : 0;
            
            $currentDate->addDay();
        }
        
        // Get threat types distribution
        $threatTypes = SecurityLog::where('action', 'threat_detected')
            ->where('created_at', '>=', $startDate)
            ->select('details')
            ->get()
            ->map(function ($log) {
                $details = json_decode($log->details, true);
                return $details['threat_type'] ?? 'unknown';
            })
            ->countBy()
            ->toArray();
        
        return [
            'labels' => $dates,
            'threat_counts' => $threatCounts,
            'threat_types' => $threatTypes,
        ];
    }
    
    /**
     * Get IP analytics
     */
    public function getIPAnalytics(int $limit = 20): array
    {
        // Get top IPs by threat count
        $topThreatIPs = SecurityLog::where('action', 'threat_detected')
            ->select('ip_address', DB::raw('COUNT(*) as threat_count'))
            ->groupBy('ip_address')
            ->orderBy('threat_count', 'desc')
            ->limit($limit)
            ->get();
        
        // Get top IPs by total activity
        $topActivityIPs = SecurityLog::select('ip_address', DB::raw('COUNT(*) as activity_count'))
            ->groupBy('ip_address')
            ->orderBy('activity_count', 'desc')
            ->limit($limit)
            ->get();
        
        return [
            'top_threat_ips' => $topThreatIPs,
            'top_activity_ips' => $topActivityIPs,
        ];
    }
    
    /**
     * Get blocked IPs with filtering options
     */
    public function getBlockedIPs(array $filters = []): array
    {
        $query = BlockedIP::with(['blocker:id,name,email', 'unblocker:id,name,email']);
        
        // Apply filters
        if (!empty($filters['ip_address'])) {
            $query->where('ip_address', 'like', '%' . $filters['ip_address'] . '%');
        }
        
        if (!empty($filters['active_only']) && $filters['active_only']) {
            $query->where('is_active', true);
        }
        
        if (!empty($filters['blocked_by'])) {
            $query->where('blocked_by', $filters['blocked_by']);
        }
        
        if (!empty($filters['date_from'])) {
            $query->where('blocked_at', '>=', $filters['date_from']);
        }
        
        if (!empty($filters['date_to'])) {
            $query->where('blocked_at', '<=', $filters['date_to']);
        }
        
        // Get paginated results
        $perPage = $filters['per_page'] ?? 50;
        $blockedIPs = $query->orderBy('blocked_at', 'desc')
            ->paginate($perPage);
        
        return [
            'blocked_ips' => $blockedIPs,
            'total' => $blockedIPs->total(),
        ];
    }
    
    /**
     * Block an IP address
     */
    public function blockIP(string $ipAddress, ?string $reason = null, ?int $blockedBy = null): BlockedIP
    {
        $blockedIP = BlockedIP::updateOrCreate(
            ['ip_address' => $ipAddress],
            [
                'reason' => $reason,
                'blocked_by' => $blockedBy,
                'blocked_at' => now(),
                'is_active' => true,
                'unblocked_at' => null,
                'unblocked_by' => null,
                'unblock_reason' => null,
            ]
        );
        
        // Clear the cache for this IP
        Cache::forget("blocked_ip_{$ipAddress}");
        
        return $blockedIP;
    }
    
    /**
     * Unblock an IP address
     */
    public function unblockIP(int $id, ?string $reason = null, ?int $unblockedBy = null): bool
    {
        $blockedIP = BlockedIP::find($id);
        
        if (!$blockedIP || !$blockedIP->is_active) {
            return false;
        }
        
        $blockedIP->update([
            'is_active' => false,
            'unblocked_at' => now(),
            'unblocked_by' => $unblockedBy,
            'unblock_reason' => $reason,
        ]);
        
        // Clear the cache for this IP
        Cache::forget("blocked_ip_{$blockedIP->ip_address}");
        
        return true;
    }
    
    /**
     * Clear security cache
     */
    public function clearCache(): void
    {
        Cache::forget('security_stats');
    }
}