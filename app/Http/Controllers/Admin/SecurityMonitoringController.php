<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlockedIP;
use App\Models\User;
use App\Services\SecurityMonitoringService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SecurityMonitoringController extends Controller
{
    protected SecurityMonitoringService $securityService;
    
    public function __construct(SecurityMonitoringService $securityService)
    {
        $this->securityService = $securityService;
    }
    
    /**
     * Show the security monitoring dashboard
     */
    public function index()
    {
        $stats = $this->securityService->getSecurityStats();
        return view('admin.security.index', compact('stats'));
    }
    
    /**
     * Get security statistics (API endpoint)
     */
    public function getStats()
    {
        $stats = $this->securityService->getSecurityStats();
        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }
    
    /**
     * Get security logs with filtering
     */
    public function getLogs(Request $request)
    {
        $filters = [
            'user_id' => $request->get('user_id'),
            'action' => $request->get('action'),
            'ip_address' => $request->get('ip_address'),
            'date_from' => $request->get('date_from'),
            'date_to' => $request->get('date_to'),
            'threat_type' => $request->get('threat_type'),
            'per_page' => $request->get('per_page', 50),
        ];
        
        $result = $this->securityService->getSecurityLogs($filters);
        
        return response()->json([
            'success' => true,
            'data' => $result['logs'],
            'total' => $result['total'],
        ]);
    }
    
    /**
     * Get threat analytics for charts
     */
    public function getThreatAnalytics(Request $request)
    {
        $days = $request->get('days', 30);
        $analytics = $this->securityService->getThreatAnalytics($days);
        
        return response()->json([
            'success' => true,
            'data' => $analytics
        ]);
    }
    
    /**
     * Get IP analytics
     */
    public function getIPAnalytics()
    {
        $analytics = $this->securityService->getIPAnalytics();
        
        return response()->json([
            'success' => true,
            'data' => $analytics
        ]);
    }
    
    /**
     * Clear security monitoring cache
     */
    public function clearCache()
    {
        $this->securityService->clearCache();
        
        return response()->json([
            'success' => true,
            'message' => 'Security cache cleared successfully'
        ]);
    }
    
    /**
     * Block an IP address
     */
    public function blockIP(Request $request)
    {
        $request->validate([
            'ip_address' => 'required|ip',
            'reason' => 'nullable|string|max:500',
        ]);
        
        $existingBlock = BlockedIP::where('ip_address', $request->ip_address)->first();
        
        if ($existingBlock && $existingBlock->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'This IP address is already blocked',
            ], 422);
        }
        
        $blockedIP = BlockedIP::updateOrCreate(
            ['ip_address' => $request->ip_address],
            [
                'reason' => $request->reason,
                'blocked_by' => Auth::id(),
                'blocked_at' => now(),
                'is_active' => true,
                'unblocked_at' => null,
                'unblocked_by' => null,
                'unblock_reason' => null,
            ]
        );
        
        return response()->json([
            'success' => true,
            'message' => 'IP address blocked successfully',
            'data' => $blockedIP,
        ]);
    }
    
    /**
     * Unblock an IP address
     */
    public function unblockIP(Request $request, $id)
    {
        $blockedIP = BlockedIP::find($id);
        
        if (!$blockedIP) {
            return response()->json([
                'success' => false,
                'message' => 'Blocked IP record not found',
            ], 404);
        }
        
        if (!$blockedIP->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'This IP address is not currently blocked',
            ], 422);
        }
        
        $blockedIP->update([
            'is_active' => false,
            'unblocked_at' => now(),
            'unblocked_by' => Auth::id(),
            'unblock_reason' => $request->reason ?? 'Manual unblock',
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'IP address unblocked successfully',
            'data' => $blockedIP,
        ]);
    }
    
    /**
     * Get all blocked IPs
     */
    public function getBlockedIPs(Request $request)
    {
        $query = BlockedIP::with(['blocker:id,name,email', 'unblocker:id,name,email']);
        
        if ($request->get('active_only', true)) {
            $query->where('is_active', true);
        }
        
        $blockedIPs = $query->orderBy('created_at', 'desc')->paginate(50);
        
        return response()->json([
            'success' => true,
            'data' => $blockedIPs,
        ]);
    }
}