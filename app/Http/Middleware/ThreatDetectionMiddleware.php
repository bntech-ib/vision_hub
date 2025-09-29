<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\SecurityLog;
use App\Models\User;

class ThreatDetectionMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Check for potential threats
        $threats = $this->detectThreats($request);
        
        // If threats are detected, log them
        if (!empty($threats)) {
            $this->logThreats($request, $threats);
        }
        
        return $next($request);
    }
    
    /**
     * Detect potential security threats in the request
     */
    private function detectThreats(Request $request): array
    {
        $threats = [];
        
        // Check for SQL injection attempts in query parameters and POST data
        $sqlPatterns = [
            '/\b(union|select|insert|update|delete|drop|create|alter|exec|execute)\b/i',
            '/(\bconcat\b|\bgroup_concat\b|\bload_file\b|\bbenchmark\b)/i',
            '/(\'\s*or\s*\'|\s*=\s*\'|\bwaitfor\b\s+delay)/i',
            '/(\bunion\b\s*\bselect\b)/i'
        ];
        
        // Check query parameters
        foreach ($request->query() as $key => $value) {
            $threats = array_merge($threats, $this->checkForThreats($value, $sqlPatterns, 'sql_injection', "Potential SQL injection attempt in query parameter: {$key}"));
        }
        
        // Check POST data
        foreach ($request->post() as $key => $value) {
            $threats = array_merge($threats, $this->checkForThreats($value, $sqlPatterns, 'sql_injection', "Potential SQL injection attempt in POST parameter: {$key}"));
        }
        
        // Check for XSS attempts
        $xssPatterns = [
            '/<script\b/i',
            '/on\w+\s*=/i',
            '/javascript:/i',
            '/<iframe\b/i',
            '/<object\b/i',
            '/<embed\b/i',
            '/<meta\b/i',
            '/<link\b/i',
            '/vbscript:/i',
            '/data:/i'
        ];
        
        // Check query parameters for XSS
        foreach ($request->query() as $key => $value) {
            $threats = array_merge($threats, $this->checkForThreats($value, $xssPatterns, 'xss', "Potential XSS attempt in query parameter: {$key}"));
        }
        
        // Check POST data for XSS
        foreach ($request->post() as $key => $value) {
            $threats = array_merge($threats, $this->checkForThreats($value, $xssPatterns, 'xss', "Potential XSS attempt in POST parameter: {$key}"));
        }
        
        // Check for command injection attempts
        $cmdPatterns = [
            '/(\||&|;|\n|\r|\$\(|`|\${)/',
            '/\b(cmd|exec|system|passthru|shell_exec)\b/i',
            '/\b(eval|assert|exec|system|passthru|shell_exec|popen|proc_open)\b/i'
        ];
        
        // Check query parameters for command injection
        foreach ($request->query() as $key => $value) {
            $threats = array_merge($threats, $this->checkForThreats($value, $cmdPatterns, 'command_injection', "Potential command injection attempt in query parameter: {$key}"));
        }
        
        // Check POST data for command injection
        foreach ($request->post() as $key => $value) {
            $threats = array_merge($threats, $this->checkForThreats($value, $cmdPatterns, 'command_injection', "Potential command injection attempt in POST parameter: {$key}"));
        }
        
        // Check for path traversal attempts
        $pathTraversalPatterns = [
            '/(\.\.\/|\.\.\\\\)/',
            '/(%2e%2e%2f|%2e%2e%5c)/i',
            '/(\.\/|\.\\\\)/',
            '/(%2e%2f|%2e%5c)/i'
        ];
        
        // Check query parameters for path traversal
        foreach ($request->query() as $key => $value) {
            $threats = array_merge($threats, $this->checkForThreats($value, $pathTraversalPatterns, 'path_traversal', "Potential path traversal attempt in query parameter: {$key}"));
        }
        
        // Check POST data for path traversal
        foreach ($request->post() as $key => $value) {
            $threats = array_merge($threats, $this->checkForThreats($value, $pathTraversalPatterns, 'path_traversal', "Potential path traversal attempt in POST parameter: {$key}"));
        }
        
        // Check for file inclusion attempts
        $fileInclusionPatterns = [
            '/(\binclude\b|\brequire\b|\binclude_once\b|\brequire_once\b)/i',
            '/(\.php|\.inc|\.pl|\.cgi|\.asp|\.aspx)/i'
        ];
        
        // Check query parameters for file inclusion
        foreach ($request->query() as $key => $value) {
            $threats = array_merge($threats, $this->checkForThreats($value, $fileInclusionPatterns, 'file_inclusion', "Potential file inclusion attempt in query parameter: {$key}"));
        }
        
        // Check POST data for file inclusion
        foreach ($request->post() as $key => $value) {
            $threats = array_merge($threats, $this->checkForThreats($value, $fileInclusionPatterns, 'file_inclusion', "Potential file inclusion attempt in POST parameter: {$key}"));
        }
        
        // Check for excessive request size
        if ($request->getContent() && strlen($request->getContent()) > 1000000) { // 1MB
            $threats[] = [
                'type' => 'large_request',
                'description' => "Large request body detected",
                'size' => strlen($request->getContent())
            ];
        }
        
        // Check for suspicious user agents
        $suspiciousUserAgents = [
            '/sqlmap/i',
            '/nikto/i',
            '/nessus/i',
            '/burp/i',
            '/zaproxy/i',
            '/acunetix/i',
            '/netsparker/i',
            '/w3af/i'
        ];
        
        $userAgent = $request->userAgent();
        if ($userAgent) {
            foreach ($suspiciousUserAgents as $pattern) {
                if (preg_match($pattern, $userAgent)) {
                    $threats[] = [
                        'type' => 'suspicious_user_agent',
                        'description' => "Suspicious user agent detected",
                        'value' => $userAgent,
                        'pattern' => $pattern
                    ];
                }
            }
        }
        
        // Check for common attack patterns in headers
        $suspiciousHeaders = [
            'x-forwarded-for',
            'client-ip',
            'x-client-ip',
            'x-originating-ip'
        ];
        
        foreach ($suspiciousHeaders as $header) {
            $headerValue = $request->header($header);
            if ($headerValue) {
                $threats = array_merge($threats, $this->checkForThreats($headerValue, $sqlPatterns, 'header_sql_injection', "Potential SQL injection in header: {$header}"));
                $threats = array_merge($threats, $this->checkForThreats($headerValue, $xssPatterns, 'header_xss', "Potential XSS in header: {$header}"));
            }
        }
        
        // Add a test threat for debugging
        if ($request->is('admin/security-monitoring/test-threat')) {
            $threats[] = [
                'type' => 'test_threat',
                'description' => "Test threat for debugging purposes",
                'url' => $request->fullUrl()
            ];
        }
        
        return $threats;
    }
    
    /**
     * Check for threats in a value using provided patterns
     */
    private function checkForThreats($value, array $patterns, string $type, string $description): array
    {
        $threats = [];
        
        if (is_string($value) || is_numeric($value)) {
            $stringValue = (string) $value;
            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $stringValue)) {
                    $threats[] = [
                        'type' => $type,
                        'description' => $description,
                        'value' => $stringValue,
                        'pattern' => $pattern
                    ];
                }
            }
        } elseif (is_array($value)) {
            // Recursively check array values
            foreach ($value as $key => $subValue) {
                $threats = array_merge($threats, $this->checkForThreats($subValue, $patterns, $type, $description . " [array key: {$key}]"));
            }
        }
        
        return $threats;
    }
    
    /**
     * Log detected threats to security logs
     */
    private function logThreats(Request $request, array $threats): void
    {
        foreach ($threats as $threat) {
            // Get authenticated user or create anonymous log
            $userId = auth()->check() ? auth()->id() : null;
            
            try {
                SecurityLog::create([
                    'user_id' => $userId,
                    'action' => 'threat_detected',
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'location' => null,
                    'successful' => false, // Threats are unsuccessful attempts
                    'details' => json_encode([
                        'threat_type' => $threat['type'],
                        'description' => $threat['description'],
                        'threat_details' => array_diff_key($threat, ['type' => '', 'description' => '']),
                        'url' => $request->fullUrl(),
                        'method' => $request->method(),
                        'referer' => $request->header('referer')
                    ]),
                ]);
            } catch (\Exception $e) {
                // Log to Laravel logs if we can't save to security logs
                Log::warning('Failed to log security threat to database', [
                    'error' => $e->getMessage(),
                    'threat' => $threat,
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]);
            }
        }
    }
}