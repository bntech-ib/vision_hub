<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the Laravel 12 bootstrap configuration and all of 
| them will be assigned to the "api" middleware group with the prefix "api/v1".
|
*/

// Public API endpoints
Route::get('/info', [\App\Http\Controllers\ApiController::class, 'info']);
Route::get('/health', [\App\Http\Controllers\ApiController::class, 'health']);
Route::get('/file-types', [\App\Http\Controllers\ApiController::class, 'fileTypes']);
Route::get('/maintenance', [\App\Http\Controllers\ApiController::class, 'maintenance']);
Route::get('/documentation', [\App\Http\Controllers\DocumentationController::class, 'index']);
Route::get('/docs', [\App\Http\Controllers\DocumentationController::class, 'index']); // Alias
Route::get('/support-options', [\App\Http\Controllers\API\SupportOptionController::class, 'publicIndex']); // Support options
Route::get('/packages/available', [\App\Http\Controllers\API\PackageController::class, 'available']); // Available packages

// Temporary test route
Route::get('/test-support-options', function () {
    $supportOptions = \App\Models\SupportOption::active()
        ->orderBy('sort_order')
        ->get();

    $result = [];
    foreach ($supportOptions as $option) {
        // Manually generate the WhatsApp link
        $whatsappLink = null;
        if ($option->whatsapp_number && $option->whatsapp_message) {
            $number = preg_replace('/[^0-9]/', '', $option->whatsapp_number);
            $message = rawurlencode($option->whatsapp_message);
            $whatsappLink = "https://wa.me/{$number}?text={$message}";
        }
        
        $result[] = [
            'id' => $option->id,
            'title' => $option->title,
            'description' => $option->description,
            'avatar' => $option->avatar ? Storage::url($option->avatar) : null,
            'whatsapp_link' => $whatsappLink,
        ];
    }

    return response()->json([
        'success' => true,
        'data' => $result,
        'message' => 'Support options retrieved successfully'
    ]);
});

// Authentication routes (public) - with rate limiting
Route::middleware(['throttle:auth'])->group(function () {
    Route::apiResource('support-options', \App\Http\Controllers\API\SupportOptionController::class);
    Route::post('/auth/register', [\App\Http\Controllers\AuthController::class, 'register']);
    Route::post('/auth/login', [\App\Http\Controllers\AuthController::class, 'login']);
    Route::post('/auth/login/email', [\App\Http\Controllers\AuthController::class, 'loginWithEmail']);
});

// Protected API endpoints
Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    // User routes
    Route::get('/auth/user', [\App\Http\Controllers\AuthController::class, 'me']);
    Route::post('/auth/logout', [\App\Http\Controllers\AuthController::class, 'logout']);
    Route::post('/auth/logout-all', [\App\Http\Controllers\AuthController::class, 'logoutAll']);
    Route::put('/auth/profile', [\App\Http\Controllers\AuthController::class, 'updateProfile']);
    Route::put('/auth/change-password', [\App\Http\Controllers\AuthController::class, 'changePassword']);
    
    // VisionHub Platform API Routes
    
    // Dashboard routes
    Route::get('/dashboard/stats', [\App\Http\Controllers\API\DashboardController::class, 'index']);
    Route::get('/dashboard/earnings', [\App\Http\Controllers\API\DashboardController::class, 'earnings']);
    Route::get('/dashboard/notifications', [\App\Http\Controllers\API\DashboardController::class, 'notifications']);
    Route::get('/dashboard/referral-stats', [\App\Http\Controllers\API\DashboardController::class, 'referralStats']);
    Route::get('/dashboard/system-stats', [\App\Http\Controllers\API\DashboardController::class, 'systemStats']);
    Route::get('/dashboard/available-ads', [\App\Http\Controllers\API\DashboardController::class, 'availableAds']);
    
    // Advertisement routes
    Route::get('/ads/stats', [\App\Http\Controllers\API\AdController::class, 'getStats']);
    Route::get('/ads/history/my-interactions', [\App\Http\Controllers\API\AdController::class, 'myInteractions']);
    Route::get('/ads', [\App\Http\Controllers\API\AdController::class, 'index']);
    Route::post('/ads', [\App\Http\Controllers\API\AdController::class, 'store']);
    Route::get('/ads/{id}', [\App\Http\Controllers\API\AdController::class, 'show']);
    Route::put('/ads/{id}', [\App\Http\Controllers\API\AdController::class, 'update']);
    Route::delete('/ads/{id}', [\App\Http\Controllers\API\AdController::class, 'destroy']);
    Route::post('/ads/{id}/interact', [\App\Http\Controllers\API\AdController::class, 'interact']);
    
    // Product routes
    Route::get('/products', [\App\Http\Controllers\API\ProductController::class, 'index']);
    Route::get('/products/categories', [\App\Http\Controllers\API\ProductController::class, 'categories']);
    Route::get('/products/{id}', [\App\Http\Controllers\API\ProductController::class, 'show']);
    Route::post('/products', [\App\Http\Controllers\API\ProductController::class, 'store']);
    Route::get('/products/my-products', [\App\Http\Controllers\API\ProductController::class, 'myProducts']);
    Route::put('/products/{id}', [\App\Http\Controllers\API\ProductController::class, 'update']);
    Route::delete('/products/{id}', [\App\Http\Controllers\API\ProductController::class, 'destroy']);
    
    // Course routes
    Route::get('/courses/my-enrollments', [\App\Http\Controllers\API\CourseController::class, 'myEnrollments']);
    Route::get('/courses/my-courses', [\App\Http\Controllers\API\CourseController::class, 'myCourses']);
    Route::get('/courses/categories', [\App\Http\Controllers\API\CourseController::class, 'categories']);
    Route::get('/courses', [\App\Http\Controllers\API\CourseController::class, 'index']);
    Route::get('/courses/{id}', [\App\Http\Controllers\API\CourseController::class, 'show']);
    Route::post('/courses/{id}/enroll', [\App\Http\Controllers\API\CourseController::class, 'enroll']);
    Route::post('/courses/{id}/progress', [\App\Http\Controllers\API\CourseController::class, 'updateProgress']);
    Route::post('/courses', [\App\Http\Controllers\API\CourseController::class, 'store']);
    Route::put('/courses/{id}', [\App\Http\Controllers\API\CourseController::class, 'update']);
    Route::delete('/courses/{id}', [\App\Http\Controllers\API\CourseController::class, 'destroy']);
    
    // Brain Teaser routes
    Route::get('/brain-teasers', [\App\Http\Controllers\API\BrainTeaserController::class, 'index']);
    Route::get('/brain-teasers/categories', [\App\Http\Controllers\API\BrainTeaserController::class, 'categories']);
    Route::get('/brain-teasers/daily', [\App\Http\Controllers\API\BrainTeaserController::class, 'dailyBrainTeaser']);
    Route::get('/brain-teasers/leaderboard', [\App\Http\Controllers\API\BrainTeaserController::class, 'leaderboard']);
    Route::get('/brain-teasers/{id}', [\App\Http\Controllers\API\BrainTeaserController::class, 'show']);
    Route::post('/brain-teasers/{id}/submit', [\App\Http\Controllers\API\BrainTeaserController::class, 'submitAnswer']);
    Route::get('/brain-teasers/my-attempts', [\App\Http\Controllers\API\BrainTeaserController::class, 'myAttempts']);
    Route::get('/brain-teasers/my-stats', [\App\Http\Controllers\API\BrainTeaserController::class, 'myStats']);
    Route::post('/brain-teasers', [\App\Http\Controllers\API\BrainTeaserController::class, 'store']); // Admin only
    
    // Add the missing attempt route as an alias to submit
    Route::post('/brain-teasers/{id}/attempt', [\App\Http\Controllers\API\BrainTeaserController::class, 'submitAnswer']);
    
    // Transaction & Wallet routes
    Route::get('/transactions', [\App\Http\Controllers\API\TransactionController::class, 'index']);
    Route::get('/transactions/{id}', [\App\Http\Controllers\API\TransactionController::class, 'show']);
    Route::get('/wallet/summary', [\App\Http\Controllers\API\TransactionController::class, 'walletSummary']);
    Route::post('/wallet/withdraw', [\App\Http\Controllers\API\TransactionController::class, 'requestWithdrawal']);
    Route::get('/wallet/withdrawals', [\App\Http\Controllers\API\TransactionController::class, 'withdrawalRequests']);
    Route::post('/wallet/withdrawals/{id}/cancel', [\App\Http\Controllers\API\TransactionController::class, 'cancelWithdrawal']);
    Route::post('/wallet/add-funds', [\App\Http\Controllers\API\TransactionController::class, 'addFunds']);
    Route::get('/transactions/statistics', [\App\Http\Controllers\API\TransactionController::class, 'statistics']);
    Route::post('/transactions/export', [\App\Http\Controllers\API\TransactionController::class, 'export']);
    
    // Support options routes (admin only)
    Route::get('/system/support-options', [\App\Http\Controllers\API\SupportOptionController::class, 'index']);
    Route::post('/system/support-options', [\App\Http\Controllers\API\SupportOptionController::class, 'store']);
    Route::get('/system/support-options/{support_option}', [\App\Http\Controllers\API\SupportOptionController::class, 'show']);
    Route::put('/system/support-options/{support_option}', [\App\Http\Controllers\API\SupportOptionController::class, 'update']);
    Route::delete('/system/support-options/{support_option}', [\App\Http\Controllers\API\SupportOptionController::class, 'destroy']);
    
    // User profile routes
    Route::get('/user/profile', [\App\Http\Controllers\API\UserProfileController::class, 'index']);
    Route::get('/user/withdrawal-status', [\App\Http\Controllers\API\UserProfileController::class, 'getWithdrawalStatus']);
    Route::put('/user/profile', [\App\Http\Controllers\API\UserProfileController::class, 'updateProfile']);
    Route::post('/user/bank-account/bind', [\App\Http\Controllers\API\UserProfileController::class, 'bindBankAccount']);
    Route::post('/user/username-by-referral', [\App\Http\Controllers\API\UserProfileController::class, 'getUsernameByReferralCode']);
    Route::post('/user/package/upgrade', [\App\Http\Controllers\API\UserProfileController::class, 'upgradePackage']);
    
    // Vendor routes
    Route::get('/vendor/access-keys', [\App\Http\Controllers\API\VendorController::class, 'accessKeys']);
    Route::get('/vendor/statistics', [\App\Http\Controllers\API\VendorController::class, 'statistics']);
});