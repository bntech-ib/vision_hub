<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

// Public API endpoints; this should be moved to admin
Route::get('/status', [\App\Http\Controllers\ApiController::class, 'status']);
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
            'icon' => $option->icon,
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
    
    // Token management this should be moved to admin
    Route::get('/auth/tokens', [\App\Http\Controllers\AuthController::class, 'tokens']);
    Route::post('/auth/tokens', [\App\Http\Controllers\AuthController::class, 'createToken']);
    Route::delete('/auth/tokens', [\App\Http\Controllers\AuthController::class, 'revokeToken']);
    
    // Project routes this should be moved to admin
    Route::apiResource('projects', \App\Http\Controllers\ProjectController::class);
    Route::get('/projects/{project}/stats', [\App\Http\Controllers\ProjectController::class, 'stats']);
    
    // Image routes (nested under projects) - with upload rate limiting; this should be moved to admin
    Route::middleware(['throttle:uploads'])->group(function () {
        Route::post('/projects/{project}/images', [\App\Http\Controllers\ImageController::class, 'store']);
    });
    
    Route::get('/projects/{project}/images', [\App\Http\Controllers\ImageController::class, 'index']);
    Route::get('/projects/{project}/images/{image}', [\App\Http\Controllers\ImageController::class, 'show']);
    Route::put('/projects/{project}/images/{image}', [\App\Http\Controllers\ImageController::class, 'update']);
    Route::delete('/projects/{project}/images/{image}', [\App\Http\Controllers\ImageController::class, 'destroy']);
    Route::get('/projects/{project}/images/{image}/download', [\App\Http\Controllers\ImageController::class, 'download']);
    
    // Processing job routes this should be moved to admin
    Route::get('/processing-jobs', [\App\Http\Controllers\ProcessingJobController::class, 'index']);
    Route::post('/processing-jobs', [\App\Http\Controllers\ProcessingJobController::class, 'store']);
    Route::get('/processing-jobs/{processingJob}', [\App\Http\Controllers\ProcessingJobController::class, 'show']);
    Route::post('/processing-jobs/{processingJob}/cancel', [\App\Http\Controllers\ProcessingJobController::class, 'cancel']);
    Route::get('/job-types', [\App\Http\Controllers\ProcessingJobController::class, 'jobTypes']);
    
    // Tag routesthis should be moved to admin
    Route::apiResource('tags', \App\Http\Controllers\TagController::class);
    Route::get('/tags-popular', [\App\Http\Controllers\TagController::class, 'popular']);
    Route::get('/tags-suggestions', [\App\Http\Controllers\TagController::class, 'suggestions']);
    Route::post('/tags-bulk', [\App\Http\Controllers\TagController::class, 'bulkCreate']);
    
    
    // VisionHub Platform API Routes
    
    // Dashboard routes
    Route::get('/dashboard/stats', [\App\Http\Controllers\API\DashboardController::class, 'index']);
    Route::get('/dashboard/earnings', [\App\Http\Controllers\API\DashboardController::class, 'earnings']);
    Route::get('/dashboard/notifications', [\App\Http\Controllers\API\DashboardController::class, 'notifications']);
    Route::get('/dashboard/system-stats', [\App\Http\Controllers\API\DashboardController::class, 'systemStats']);
    Route::get('/dashboard/available-ads', [\App\Http\Controllers\API\DashboardController::class, 'availableAds']);
    
    // Advertisement routes
    Route::get('/ads', [\App\Http\Controllers\API\AdController::class, 'index']);
    Route::post('/ads', [\App\Http\Controllers\API\AdController::class, 'store']);
    Route::get('/ads/{id}', [\App\Http\Controllers\API\AdController::class, 'show']);
    Route::put('/ads/{id}', [\App\Http\Controllers\API\AdController::class, 'update']);
    Route::delete('/ads/{id}', [\App\Http\Controllers\API\AdController::class, 'destroy']);
    Route::post('/ads/{id}/interact', [\App\Http\Controllers\API\AdController::class, 'interact']);
    Route::get('/ads/history/my-interactions', [\App\Http\Controllers\API\AdController::class, 'myInteractions']);
    
    // Marketplace routes
    Route::get('/products', [\App\Http\Controllers\API\MarketplaceController::class, 'index']);
    Route::get('/products/categories', [\App\Http\Controllers\API\MarketplaceController::class, 'categories']);
    Route::get('/products/{id}', [\App\Http\Controllers\API\MarketplaceController::class, 'show']);
    Route::post('/products/{id}/purchase', [\App\Http\Controllers\API\MarketplaceController::class, 'purchase']);
    Route::post('/products', [\App\Http\Controllers\API\MarketplaceController::class, 'store']);
    Route::get('/products/my-products', [\App\Http\Controllers\API\MarketplaceController::class, 'myProducts']);
    Route::put('/products/{id}', [\App\Http\Controllers\API\MarketplaceController::class, 'update']);
    Route::delete('/products/{id}', [\App\Http\Controllers\API\MarketplaceController::class, 'destroy']);
    Route::get('/products/purchase-history', [\App\Http\Controllers\API\MarketplaceController::class, 'purchaseHistory']);
    Route::get('/products/sales-history', [\App\Http\Controllers\API\MarketplaceController::class, 'salesHistory']);
    
    // Product routes (new)
    Route::get('/products-new', [\App\Http\Controllers\API\ProductController::class, 'index']);
    Route::get('/products-new/categories', [\App\Http\Controllers\API\ProductController::class, 'categories']);
    Route::get('/products-new/{id}', [\App\Http\Controllers\API\ProductController::class, 'show']);
    Route::post('/products-new', [\App\Http\Controllers\API\ProductController::class, 'store']);
    Route::get('/products-new/my-products', [\App\Http\Controllers\API\ProductController::class, 'myProducts']);
    Route::put('/products-new/{id}', [\App\Http\Controllers\API\ProductController::class, 'update']);
    Route::delete('/products-new/{id}', [\App\Http\Controllers\API\ProductController::class, 'destroy']);
    
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
});