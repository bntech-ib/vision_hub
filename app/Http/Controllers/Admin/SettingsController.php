<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class SettingsController extends Controller
{
    /**
     * Display settings page
     */
    public function index(): View
    {
        $settings = [
            'general' => $this->getGeneralSettings(),
            'email' => $this->getEmailSettings(),
            'storage' => $this->getStorageSettings(),
            'processing' => $this->getProcessingSettings(),
            'security' => $this->getSecuritySettings(),
            'notifications' => $this->getNotificationSettings(),
            'financial' => $this->getFinancialSettings()
        ];

        return view('admin.settings.index', compact('settings'));
    }

    /**
     * Update general settings
     */
    public function updateGeneral(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'app_name' => 'required|string|max:255',
            'app_description' => 'nullable|string|max:500',
            'app_logo' => 'nullable|image|max:2048',
            'timezone' => 'required|string',
            'date_format' => 'required|string',
            'currency' => 'required|string|size:3',
            'maintenance_message' => 'nullable|string|max:255'
        ]);

        // Handle logo upload
        if ($request->hasFile('app_logo')) {
            $logoPath = $request->file('app_logo')->store('settings', 'public');
            $validated['app_logo'] = $logoPath;
        }

        // Save settings (this would typically be saved to database or config files)
        $this->saveSettings('general', $validated);

        return response()->json([
            'success' => true,
            'message' => 'General settings updated successfully'
        ]);
    }

    /**
     * Update email settings
     */
    public function updateEmail(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'mail_driver' => 'required|in:smtp,mailgun,ses,sendmail',
            'mail_host' => 'required_if:mail_driver,smtp|nullable|string',
            'mail_port' => 'required_if:mail_driver,smtp|nullable|integer',
            'mail_username' => 'nullable|string',
            'mail_password' => 'nullable|string',
            'mail_encryption' => 'nullable|in:tls,ssl',
            'mail_from_address' => 'required|email',
            'mail_from_name' => 'required|string|max:255',
            'mailgun_domain' => 'required_if:mail_driver,mailgun|nullable|string',
            'mailgun_secret' => 'required_if:mail_driver,mailgun|nullable|string'
        ]);

        $this->saveSettings('email', $validated);

        return response()->json([
            'success' => true,
            'message' => 'Email settings updated successfully'
        ]);
    }

    /**
     * Update storage settings
     */
    public function updateStorage(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'default_disk' => 'required|in:local,s3,gcs',
            'max_file_size' => 'required|integer|min:1|max:1024',
            'allowed_file_types' => 'required|array',
            'allowed_file_types.*' => 'string',
            's3_key' => 'required_if:default_disk,s3|nullable|string',
            's3_secret' => 'required_if:default_disk,s3|nullable|string',
            's3_region' => 'required_if:default_disk,s3|nullable|string',
            's3_bucket' => 'required_if:default_disk,s3|nullable|string',
            'auto_delete_after_days' => 'nullable|integer|min:1',
            'enable_compression' => 'boolean'
        ]);

        $this->saveSettings('storage', $validated);

        return response()->json([
            'success' => true,
            'message' => 'Storage settings updated successfully'
        ]);
    }

    /**
     * Update processing settings
     */
    public function updateProcessing(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'max_concurrent_jobs' => 'required|integer|min:1|max:50',
            'job_timeout' => 'required|integer|min:30|max:3600',
            'retry_failed_jobs' => 'boolean',
            'max_retries' => 'required|integer|min:0|max:10',
            'cleanup_completed_jobs_after_days' => 'required|integer|min:1|max:365',
            'enable_job_notifications' => 'boolean',
            'processing_quality' => 'required|in:low,medium,high',
            'enable_ai_enhancement' => 'boolean'
        ]);

        $this->saveSettings('processing', $validated);

        return response()->json([
            'success' => true,
            'message' => 'Processing settings updated successfully'
        ]);
    }

    /**
     * Update security settings
     */
    public function updateSecurity(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'session_lifetime' => 'required|integer|min:15|max:1440',
            'password_min_length' => 'required|integer|min:6|max:20',
            'require_email_verification' => 'boolean',
            'enable_two_factor' => 'boolean',
            'max_login_attempts' => 'required|integer|min:3|max:10',
            'lockout_duration' => 'required|integer|min:1|max:60',
            'enable_captcha' => 'boolean',
            'api_rate_limit' => 'required|integer|min:60|max:10000',
            'allowed_ips' => 'nullable|array',
            'allowed_ips.*' => 'ip'
        ]);

        $this->saveSettings('security', $validated);

        return response()->json([
            'success' => true,
            'message' => 'Security settings updated successfully'
        ]);
    }

    /**
     * Update notification settings
     */
    public function updateNotifications(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'admin_email_notifications' => 'boolean',
            'user_registration_notification' => 'boolean',
            'failed_job_notification' => 'boolean',
            'system_error_notification' => 'boolean',
            'daily_report_notification' => 'boolean',
            'notification_email' => 'required|email',
            'slack_webhook_url' => 'nullable|url',
            'enable_slack_notifications' => 'boolean',
            'enable_sms_notifications' => 'boolean',
            'sms_provider' => 'required_if:enable_sms_notifications,true|nullable|in:twilio,nexmo'
        ]);

        $this->saveSettings('notifications', $validated);

        return response()->json([
            'success' => true,
            'message' => 'Notification settings updated successfully'
        ]);
    }

    /**
     * Test email configuration
     */
    public function testEmail(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'test_email' => 'required|email'
        ]);

        try {
            Mail::raw('This is a test email from VisionHub Admin Panel.', function ($message) use ($validated) {
                $message->to($validated['test_email'])
                        ->subject('VisionHub - Test Email');
            });

            return response()->json([
                'success' => true,
                'message' => 'Test email sent successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send test email: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test storage configuration
     */
    public function testStorage(): JsonResponse
    {
        try {
            $testContent = 'This is a test file created at ' . now()->toDateTimeString();
            $testFile = 'test/storage_test_' . time() . '.txt';
            
            // Test write
            Storage::put($testFile, $testContent);
            
            // Test read
            $readContent = Storage::get($testFile);
            
            // Test delete
            Storage::delete($testFile);
            
            if ($readContent === $testContent) {
                return response()->json([
                    'success' => true,
                    'message' => 'Storage configuration is working correctly'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Storage read/write test failed'
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Storage test failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get general settings
     */
    private function getGeneralSettings(): array
    {
        return [
            'app_name' => config('app.name', 'VisionHub'),
            'app_description' => $this->getSetting('general.app_description', ''),
            'app_logo' => $this->getSetting('general.app_logo', ''),
            'timezone' => config('app.timezone', 'UTC'),
            'date_format' => $this->getSetting('general.date_format', 'Y-m-d'),
            'currency' => $this->getSetting('general.currency', 'USD'),
            'maintenance_message' => $this->getSetting('general.maintenance_message', '')
        ];
    }

    /**
     * Get email settings
     */
    private function getEmailSettings(): array
    {
        return [
            'mail_driver' => config('mail.default', 'smtp'),
            'mail_host' => config('mail.mailers.smtp.host', ''),
            'mail_port' => config('mail.mailers.smtp.port', 587),
            'mail_username' => config('mail.mailers.smtp.username', ''),
            'mail_encryption' => config('mail.mailers.smtp.encryption', 'tls'),
            'mail_from_address' => config('mail.from.address', ''),
            'mail_from_name' => config('mail.from.name', ''),
            'mailgun_domain' => config('services.mailgun.domain', ''),
            'mailgun_secret' => config('services.mailgun.secret', '')
        ];
    }

    /**
     * Get storage settings
     */
    private function getStorageSettings(): array
    {
        return [
            'default_disk' => config('filesystems.default', 'local'),
            'max_file_size' => $this->getSetting('storage.max_file_size', 100),
            'allowed_file_types' => $this->getSetting('storage.allowed_file_types', ['jpg', 'png', 'gif', 'bmp', 'webp']),
            's3_key' => config('filesystems.disks.s3.key', ''),
            's3_secret' => config('filesystems.disks.s3.secret', ''),
            's3_region' => config('filesystems.disks.s3.region', ''),
            's3_bucket' => config('filesystems.disks.s3.bucket', ''),
            'auto_delete_after_days' => $this->getSetting('storage.auto_delete_after_days', null),
            'enable_compression' => $this->getSetting('storage.enable_compression', true)
        ];
    }

    /**
     * Get processing settings
     */
    private function getProcessingSettings(): array
    {
        return [
            'max_concurrent_jobs' => $this->getSetting('processing.max_concurrent_jobs', 5),
            'job_timeout' => $this->getSetting('processing.job_timeout', 300),
            'retry_failed_jobs' => $this->getSetting('processing.retry_failed_jobs', true),
            'max_retries' => $this->getSetting('processing.max_retries', 3),
            'cleanup_completed_jobs_after_days' => $this->getSetting('processing.cleanup_completed_jobs_after_days', 30),
            'enable_job_notifications' => $this->getSetting('processing.enable_job_notifications', true),
            'processing_quality' => $this->getSetting('processing.processing_quality', 'medium'),
            'enable_ai_enhancement' => $this->getSetting('processing.enable_ai_enhancement', false)
        ];
    }

    /**
     * Get security settings
     */
    private function getSecuritySettings(): array
    {
        return [
            'session_lifetime' => config('session.lifetime', 120),
            'password_min_length' => $this->getSetting('security.password_min_length', 8),
            'require_email_verification' => $this->getSetting('security.require_email_verification', true),
            'enable_two_factor' => $this->getSetting('security.enable_two_factor', false),
            'max_login_attempts' => $this->getSetting('security.max_login_attempts', 5),
            'lockout_duration' => $this->getSetting('security.lockout_duration', 15),
            'enable_captcha' => $this->getSetting('security.enable_captcha', false),
            'api_rate_limit' => $this->getSetting('security.api_rate_limit', 1000),
            'allowed_ips' => $this->getSetting('security.allowed_ips', [])
        ];
    }

    /**
     * Get notification settings
     */
    private function getNotificationSettings(): array
    {
        return [
            'admin_email_notifications' => $this->getSetting('notifications.admin_email_notifications', true),
            'user_registration_notification' => $this->getSetting('notifications.user_registration_notification', true),
            'failed_job_notification' => $this->getSetting('notifications.failed_job_notification', true),
            'system_error_notification' => $this->getSetting('notifications.system_error_notification', true),
            'daily_report_notification' => $this->getSetting('notifications.daily_report_notification', false),
            'notification_email' => $this->getSetting('notifications.notification_email', 'admin@visionhub.com'),
            'slack_webhook_url' => $this->getSetting('notifications.slack_webhook_url', ''),
            'enable_slack_notifications' => $this->getSetting('notifications.enable_slack_notifications', false),
            'enable_sms_notifications' => $this->getSetting('notifications.enable_sms_notifications', false),
            'sms_provider' => $this->getSetting('notifications.sms_provider', 'twilio')
        ];
    }

    /**
     * Get a setting value
     */
    private function getSetting(string $key, $default = null)
    {
        // This would typically retrieve from a settings table in the database
        // For now, return the default value
        return $default;
    }

    /**
     * Save settings
     */
    private function saveSettings(string $category, array $settings): void
    {
        // This would typically save to a settings table in the database
        // For now, we'll just log the action
        Log::info("Settings updated for category: {$category}", $settings);
    }

    /**
     * Update financial settings
     */
    public function updateFinancial(Request $request)
    {
        $validated = $request->validate([
            'withdrawal_enabled' => 'required|boolean'
        ]);

        // Save withdrawal setting
        \App\Models\GlobalSetting::set('withdrawal_enabled', $validated['withdrawal_enabled']);

        // Check if this is an AJAX request
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Financial settings updated successfully'
            ]);
        }

        // For web requests, redirect back with success message
        return redirect()->back()->with('success', 'Financial settings updated successfully');
    }

    /**
     * Get financial settings
     */
    private function getFinancialSettings(): array
    {
        return [
            'withdrawal_enabled' => \App\Models\GlobalSetting::isWithdrawalEnabled()
        ];
    }
}