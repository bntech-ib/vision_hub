<?php

namespace App\Http\Middleware;

use App\Models\BlockedIP;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class CheckBlockedIP
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $ipAddress = $request->ip();
        
        // Skip check for localhost
        if ($ipAddress === '127.0.0.1' || $ipAddress === '::1') {
            return $next($request);
        }
        
        // Check if IP is blocked using cache for performance
        $cacheKey = "blocked_ip_{$ipAddress}";
        $isBlocked = Cache::remember($cacheKey, 300, function () use ($ipAddress) {
            return BlockedIP::where('ip_address', $ipAddress)
                ->where('is_active', true)
                ->exists();
        });
        
        if ($isBlocked) {
            return response()->json([
                'success' => false,
                'message' => 'Your IP address has been blocked.',
            ], 403);
        }
        
        return $next($request);
    }
}