<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SecurityLog;
use Illuminate\Support\Facades\DB;

class GenerateTestSecurityEvents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-test-security-events {--count=10 : Number of events to generate}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate test security events for monitoring dashboard';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $count = $this->option('count');
        $this->info("Generating {$count} test security events...");
        
        $threatTypes = [
            'sql_injection',
            'xss',
            'command_injection',
            'path_traversal',
            'file_inclusion',
            'suspicious_user_agent'
        ];
        
        $actions = [
            'threat_detected',
            'login_failed',
            'login_successful'
        ];
        
        $ipAddresses = [
            '192.168.1.100',
            '10.0.0.50',
            '172.16.0.25',
            '203.0.113.45',
            '198.51.100.12'
        ];
        
        $userAgents = [
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            'sqlmap/1.5.10.20#dev (http://sqlmap.org)',
            'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36'
        ];
        
        for ($i = 0; $i < $count; $i++) {
            $threatType = $threatTypes[array_rand($threatTypes)];
            $action = $actions[array_rand($actions)];
            $ipAddress = $ipAddresses[array_rand($ipAddresses)];
            $userAgent = $userAgents[array_rand($userAgents)];
            
            $details = [
                'threat_type' => $threatType,
                'description' => "Test {$threatType} event",
                'test_event' => true,
                'generated_at' => now()->toISOString()
            ];
            
            if ($action === 'threat_detected') {
                $details['threat_details'] = [
                    'pattern' => '/test_pattern/',
                    'value' => 'test_value'
                ];
            }
            
            try {
                SecurityLog::create([
                    'user_id' => null,
                    'action' => $action,
                    'ip_address' => $ipAddress,
                    'user_agent' => $userAgent,
                    'location' => null,
                    'successful' => $action !== 'threat_detected' && $action !== 'login_failed',
                    'details' => json_encode($details),
                    'created_at' => now()->subMinutes(rand(0, 1440)), // Random time within last 24 hours
                    'updated_at' => now()
                ]);
            } catch (\Exception $e) {
                $this->error("Failed to create security log: " . $e->getMessage());
                return 1;
            }
        }
        
        $this->info("Successfully generated {$count} test security events!");
        return 0;
    }
}