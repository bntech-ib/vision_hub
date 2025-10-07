<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ApiController;
use App\Models\AccessKey;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Http\Controllers\Auth\ForgotPasswordController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the Laravel 12 bootstrap configuration and all of 
| them will be assigned to the "web" middleware group.
|
*/

Route::get('/', function () {
    if (Auth::check()) {
        return view('home');
    }
    return view('auth.login');
});

Route::get('/health', [ApiController::class, 'health']);

// Web Authentication Routes (for admin dashboard and user registration)
Route::middleware('guest')->group(function () {
    Route::get('/login', function () {
        return view('auth.login');
    })->name('login');
    
    Route::get('/register', function () {
        $accessKeys = AccessKey::where('is_used', false)->where('is_active', true)->get();
        return view('auth.register', compact('accessKeys'));
    })->name('register');

    Route::post('/register', function (Request $request) {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'access_key' => 'required|string|exists:access_keys,key',
            'country' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'referrer_code' => 'nullable|string|exists:users,referral_code',
        ]);

        // Verify access key
        $accessKey = AccessKey::where('key', $validated['access_key'])->first();
        
        if (!$accessKey || !$accessKey->canBeUsed()) {
            return back()->withErrors([
                'access_key' => 'The access key is invalid or has already been used.',
            ])->withInput();
        }

        // Find referrer if provided
        $referrer = null;
        if (!empty($validated['referrer_code'])) {
            $referrer = User::where('referral_code', $validated['referrer_code'])->first();
        }

        // Create user with package from access key
        /** @var User $user */
        $user = User::create([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'country' => $validated['country'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'referral_code' => strtoupper(Str::random(6)), // Generate unique referral code
            'current_package_id' => $accessKey->package_id,
            'package_expires_at' => $accessKey->package->duration_days ? 
                now()->addDays((int) $accessKey->package->duration_days) : null,
            'referred_by' => $referrer ? $referrer->id : null,
        ]);

        // Mark access key as used
        $accessKey->update([
            'is_used' => true,
            'used_by' => $user->id,
            'used_at' => now(),
        ]);

        // Award referral bonuses if applicable
        if ($referrer) {
            // Base referral amounts
            $baseLevel1Bonus = 100; // Amount in your currency
            $baseLevel2Bonus = 50;  // Amount in your currency
            $baseLevel3Bonus = 25;  // Amount in your currency
            
            // Calculate actual bonus amounts based on the package that was applied to the new user
            $level1Bonus = $baseLevel1Bonus;
            if ($accessKey->package && $accessKey->package->referral_earning_percentage > 0) {
                $level1Bonus = (float) $accessKey->package->referral_earning_percentage;
            }
            
            // Award direct referral bonus (Level 1) to referral earnings
            $referrer->addToReferralEarnings($level1Bonus);
            
            // Log the referral bonus
            \App\Models\ReferralBonus::create([
                'referrer_id' => $referrer->id,
                'referred_user_id' => $user->id,
                'level' => 1,
                'amount' => $level1Bonus,
                'description' => 'Direct referral bonus for ' . $user->username,
            ]);
            
            // Log the transaction for the referrer
            $referrer->transactions()->create([
                'amount' => $level1Bonus,
                'type' => 'referral_earning',
                'description' => 'Direct referral bonus for ' . $user->username,
                'status' => 'completed',
            ]);
            
            // Award indirect referral bonus (Level 2)
            if ($referrer->referredBy) {
                $level2Bonus = $baseLevel2Bonus;
                if ($accessKey->package && $accessKey->package->referral_earning_percentage > 0) {
                    $level2Bonus = (float) $accessKey->package->referral_earning_percentage;
                }
                
                $referrer->referredBy->addToReferralEarnings($level2Bonus);
                
                // Log the referral bonus
                \App\Models\ReferralBonus::create([
                    'referrer_id' => $referrer->referredBy->id,
                    'referred_user_id' => $user->id,
                    'level' => 2,
                    'amount' => $level2Bonus,
                    'description' => 'Indirect referral bonus for ' . $user->username,
                ]);
                
                // Log the transaction for the indirect referrer
                $referrer->referredBy->transactions()->create([
                    'amount' => $level2Bonus,
                    'type' => 'referral_earning',
                    'description' => 'Indirect referral bonus for ' . $user->username,
                    'status' => 'completed',
                ]);
                
                // Award indirect referral bonus (Level 3)
                if ($referrer->referredBy->referredBy) {
                    $level3Bonus = $baseLevel3Bonus;
                    if ($accessKey->package && $accessKey->package->referral_earning_percentage > 0) {
                        $level3Bonus = (float) $accessKey->package->referral_earning_percentage;
                    }
                    
                    $referrer->referredBy->referredBy->addToReferralEarnings($level3Bonus);
                    
                    // Log the referral bonus
                    \App\Models\ReferralBonus::create([
                        'referrer_id' => $referrer->referredBy->referredBy->id,
                        'referred_user_id' => $user->id,
                        'level' => 3,
                        'amount' => $level3Bonus,
                        'description' => 'Second indirect referral bonus for ' . $user->username,
                    ]);
                    
                    // Log the transaction for the second indirect referrer
                    $referrer->referredBy->referredBy->transactions()->create([
                        'amount' => $level3Bonus,
                        'type' => 'referral_earning',
                        'description' => 'Second indirect referral bonus for ' . $user->username,
                        'status' => 'completed',
                    ]);
                }
            }
        }
        
        // Log the user in
        Auth::login($user);
        
        return redirect()->intended('/');
    })->name('register.post');
    
    Route::get('/admin/login', function () {
        return view('auth.admin-login');
    })->name('admin.login');
    
    // Password Reset Routes
    Route::get('/password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/password/reset/{token}', [ForgotPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/password/reset', [ForgotPasswordController::class, 'reset'])->name('password.update');

    Route::post('/web-login', function (Request $request) {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);
        
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            
            /** @var User $user */
            $user = Auth::user();
            if ($user && method_exists($user, 'isAdmin') && $user->isAdmin()) {
                return redirect()->route('admin.dashboard');
            }
            
            return redirect()->intended('/');
        }
        
        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    })->name('login.post');

    Route::post('/admin/login', function (Request $request) {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);
        
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            
            /** @var User $user */
            $user = Auth::user();
            if ($user && method_exists($user, 'isAdmin') && $user->isAdmin()) {
                return redirect()->route('admin.dashboard');
            }
            
            Auth::logout();
            return back()->withErrors([
                'email' => 'Admin access required.',
            ]);
        }
        
        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    })->name('admin.login.post');
});

Route::middleware('auth')->group(function () {
    Route::get('/home', function () {
        return view('home');
    })->name('home');
    
    Route::post('/logout', function (Request $request) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    })->name('logout');
});

// Note: auth.php contains API routes with sanctum middleware
// require __DIR__.'/auth.php';