<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SecurityMonitoringService;
use Illuminate\Http\Request;

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
}