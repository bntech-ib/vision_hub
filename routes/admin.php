<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\AdController;
use App\Http\Controllers\Admin\ProjectController;
use App\Http\Controllers\Admin\ImageController;
use App\Http\Controllers\Admin\ProcessingJobController;
use App\Http\Controllers\Admin\TagController;
use App\Http\Controllers\Admin\PackageController;
use App\Http\Controllers\Admin\AccessKeyController; // Added
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\Admin\WithdrawalController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SystemController;
use App\Http\Controllers\Admin\CourseController; // Added
use App\Http\Controllers\Admin\ProductController; // Added
use App\Http\Controllers\Admin\BrainTeaserController; // Added
use App\Http\Controllers\Admin\SponsoredPostController; // Added for sponsored posts
use App\Http\Controllers\API\TwoFactorAuthenticationController; // Added
use App\Http\Controllers\Admin\TwoFactorAuthenticationController as AdminTwoFactorAuthenticationController; // Added for admin 2FA
use App\Http\Controllers\Admin\ProfileController; // Added for admin profile
use App\Http\Controllers\Admin\SecurityMonitoringController; // Added for security monitoring
use App\Http\Controllers\Admin\SupportController; // Added for support options
use App\Http\Controllers\Admin\VendorController; // Added for vendor management
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth; // Added for logout functionality

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/stats', [DashboardController::class, 'getStats'])->name('dashboard.stats');
    Route::get('/recent-activities', [DashboardController::class, 'recentActivities'])->name('dashboard.recent-activities');
    
    // Analytics Routes
    Route::get('/analytics/revenue', [DashboardController::class, 'getRevenueAnalytics'])->name('analytics.revenue');
    Route::get('/analytics/withdrawals-transactions', [DashboardController::class, 'getWithdrawalsTransactionsAnalytics'])->name('analytics.withdrawals-transactions');
    Route::get('/analytics/user-activity', [DashboardController::class, 'getUserActivityAnalytics'])->name('analytics.user-activity');
    
    // Users Management
    Route::resource('users', UserController::class);
    Route::put('users/{user}/suspend', [UserController::class, 'suspend'])->name('users.suspend');
    Route::put('users/{user}/activate', [UserController::class, 'activate'])->name('users.activate');
    Route::put('users/{user}/change-package', [UserController::class, 'changePackage'])->name('users.change-package');
    Route::post('users/{user}/add-credits', [UserController::class, 'addCredits'])->name('users.add-credits');
    Route::get('users/{user}/login-history', [UserController::class, 'loginHistory'])->name('users.login-history');
    Route::get('users/export', [UserController::class, 'export'])->name('users.export');
    Route::post('users/bulk-action', [UserController::class, 'bulkAction'])->name('users.bulk-action');
    Route::put('settings/enable-withdrawal', [UserController::class, 'enableWithdrawalGlobally'])->name('settings.enable-withdrawal');
    Route::put('settings/disable-withdrawal', [UserController::class, 'disableWithdrawalGlobally'])->name('settings.disable-withdrawal');

    // Projects Management
    Route::resource('projects', ProjectController::class);
    Route::put('projects/{project}/suspend', [ProjectController::class, 'suspend'])->name('projects.suspend');
    Route::put('projects/{project}/activate', [ProjectController::class, 'activate'])->name('projects.activate');
    
    // Images Management
    Route::resource('images', ImageController::class);
    Route::put('images/{image}/suspend', [ImageController::class, 'suspend'])->name('images.suspend');
    Route::put('images/{image}/activate', [ImageController::class, 'activate'])->name('images.activate');
    Route::post('images/bulk-action', [ImageController::class, 'bulkAction'])->name('images.bulk-action');
    
    // Processing Jobs Management
    Route::resource('processing-jobs', ProcessingJobController::class);
    Route::put('processing-jobs/{job}/suspend', [ProcessingJobController::class, 'suspend'])->name('processing-jobs.suspend');
    Route::put('processing-jobs/{job}/activate', [ProcessingJobController::class, 'activate'])->name('processing-jobs.activate');
    
    // Tags Management
    Route::resource('tags', TagController::class);
    
    // Packages Management
    Route::resource('packages', PackageController::class);
    Route::put('packages/{package}/activate', [PackageController::class, 'activate'])->name('packages.activate');
    Route::put('packages/{package}/deactivate', [PackageController::class, 'deactivate'])->name('packages.deactivate');
    
    // Access Keys Management
    Route::resource('access-keys', AccessKeyController::class);
    Route::put('access-keys/{accessKey}/activate', [AccessKeyController::class, 'activate'])->name('access-keys.activate');
    Route::put('access-keys/{accessKey}/deactivate', [AccessKeyController::class, 'deactivate'])->name('access-keys.deactivate');
    
    // Advertisements Management
    Route::resource('ads', AdController::class);
    Route::put('ads/{ad}/suspend', [AdController::class, 'suspend'])->name('ads.suspend');
    Route::put('ads/{ad}/activate', [AdController::class, 'activate'])->name('ads.activate');
    Route::put('ads/{ad}/approve', [AdController::class, 'approve'])->name('ads.approve');
    Route::put('ads/{ad}/reject', [AdController::class, 'reject'])->name('ads.reject');
    Route::put('ads/{ad}/pause', [AdController::class, 'pause'])->name('ads.pause');
    
    // Sponsored Posts Management
    Route::resource('sponsored-posts', SponsoredPostController::class);
    
    // Transactions Management
    Route::resource('transactions', TransactionController::class)->except(['create', 'store', 'edit', 'update']);
    Route::get('transactions/export', [TransactionController::class, 'export'])->name('transactions.export');
    
    // Withdrawals Management
    Route::resource('withdrawals', WithdrawalController::class)->except(['create', 'store', 'edit', 'update']);
    Route::put('withdrawals/{withdrawal}/approve', [WithdrawalController::class, 'approve'])->name('withdrawals.approve');
    Route::put('withdrawals/{withdrawal}/reject', [WithdrawalController::class, 'reject'])->name('withdrawals.reject');
    Route::get('withdrawals/pending', [WithdrawalController::class, 'pending'])->name('withdrawals.pending');
    Route::get('withdrawals/export', [WithdrawalController::class, 'export'])->name('withdrawals.export');
    
    // System Management
    Route::prefix('system')->name('system.')->controller(SystemController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/summary', function () { return view('admin.system.summary'); })->name('summary');
        Route::get('/logs', 'logs')->name('logs');
        Route::get('/cache', 'cache')->name('cache');
        Route::post('/cache/clear', 'clearCache')->name('cache.clear');
        Route::get('/queue', 'queue')->name('queue');
        Route::post('/queue/restart', 'restartQueue')->name('queue.restart');
        Route::get('/maintenance', 'maintenance')->name('maintenance');
        Route::post('/maintenance/enable', 'enableMaintenance')->name('maintenance.enable');
        Route::post('/maintenance/disable', 'disableMaintenance')->name('maintenance.disable');
        Route::get('/storage', 'storage')->name('storage');
        Route::post('/storage/cleanup', 'storageCleanup')->name('storage.cleanup');
        
        // Backup routes
        Route::get('/backup', 'backup')->name('backup');
        Route::post('/backup/create', 'createBackup')->name('backup.create');
        Route::get('/backup/download/{filename}', 'downloadBackup')->name('backup.download');
        Route::post('/backup/delete', 'deleteBackup')->name('backup.delete');
    });
    
    // System Status Page
    Route::get('/system-status', function () { return view('admin.system-status.index'); })->name('system-status');
    
    // Settings Management
    Route::prefix('settings')->name('settings.')->controller(SettingsController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::put('/general', 'updateGeneral')->name('general.update');
        Route::put('/email', 'updateEmail')->name('email.update');
        Route::put('/storage', 'updateStorage')->name('storage.update');
        Route::put('/processing', 'updateProcessing')->name('processing.update');
        Route::put('/security', 'updateSecurity')->name('security.update');
        Route::put('/financial', 'updateFinancial')->name('financial.update');
        Route::put('/notifications', 'updateNotifications')->name('notifications.update');
        Route::put('/integrations', 'updateIntegrations')->name('integrations.update');
        // Test routes
        Route::get('/test/email', 'testEmail')->name('test.email');
        Route::get('/test/storage', 'testStorage')->name('test.storage');
    });
    
    // Reports
    Route::prefix('reports')->name('reports.')->controller(ReportController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/users', 'users')->name('users');
        Route::get('/projects', 'projects')->name('projects');
        Route::get('/processing', 'processing')->name('processing');
        Route::get('/revenue', 'revenue')->name('revenue');
        Route::get('/storage', 'storage')->name('storage');
        Route::get('/activity', 'activity')->name('activity');
        Route::get('/performance', 'performance')->name('performance');
        Route::get('/security-logs', 'securityLogs')->name('security-logs');
        Route::get('/export/{type}', 'export')->name('export');
    });
    
    // Profile Management
    Route::prefix('profile')->name('profile.')->controller(ProfileController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::put('/update', 'update')->name('update');
        Route::put('/password', 'updatePassword')->name('password.update');
        Route::delete('/2fa', 'disable2FA')->name('2fa.disable');
    });
    
    // Two-Factor Authentication
    Route::prefix('2fa')->name('2fa.')->controller(AdminTwoFactorAuthenticationController::class)->group(function () {
        Route::get('/', 'show')->name('show');
        Route::post('/verify', 'verify')->name('verify');
    });
    
    // Support Options Management
    Route::resource('support', SupportController::class);
    
    // API Status
    Route::get('/status', [\App\Http\Controllers\ApiController::class, 'status'])->name('api.status');
    
    // Security Monitoring
    Route::prefix('security-monitoring')->name('security-monitoring.')->controller(SecurityMonitoringController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/logs', 'getLogs')->name('logs');
        Route::get('/stats', 'getStats')->name('stats');
        Route::get('/analytics/threats', 'getThreatAnalytics')->name('analytics.threats');
        Route::get('/analytics/ip', 'getIPAnalytics')->name('analytics.ip');
        Route::get('/blocked-ips', 'getBlockedIPs')->name('blocked-ips');
        Route::post('/clear-cache', 'clearCache')->name('clear-cache');
        Route::post('/block-ip', 'blockIP')->name('block-ip');
        Route::post('/unblock-ip/{id}', 'unblockIP')->name('unblock-ip');
    });
    
    // Courses Management
    Route::resource('courses', CourseController::class);
    Route::put('courses/{course}/suspend', [CourseController::class, 'suspend'])->name('courses.suspend');
    Route::put('courses/{course}/activate', [CourseController::class, 'activate'])->name('courses.activate');
    
    // Products Management
    Route::resource('products', ProductController::class);
    Route::put('products/{product}/suspend', [ProductController::class, 'suspend'])->name('products.suspend');
    Route::put('products/{product}/activate', [ProductController::class, 'activate'])->name('products.activate');
    
    // Brain Teasers Management
    Route::resource('brain-teasers', BrainTeaserController::class);
    Route::put('brain-teasers/{brainTeaser}/suspend', [BrainTeaserController::class, 'suspend'])->name('brain-teasers.suspend');
    Route::put('brain-teasers/{brainTeaser}/activate', [BrainTeaserController::class, 'activate'])->name('brain-teasers.activate');
    
    // Vendor Management
    Route::resource('vendors', VendorController::class);
    Route::post('vendors/{vendor}/generate-access-keys', [VendorController::class, 'generateAccessKeys'])->name('vendors.generate-access-keys');
    Route::get('vendors/{vendor}/access-keys', [VendorController::class, 'accessKeys'])->name('vendors.access-keys');
    
    // Logout Route
    Route::post('/logout', function (\Illuminate\Http\Request $request) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('admin.login');
    })->name('logout');
});