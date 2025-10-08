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