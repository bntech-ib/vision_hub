<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorAuthenticationController extends Controller
{
    protected Google2FA $google2fa;

    public function __construct(Google2FA $google2fa)
    {
        $this->google2fa = $google2fa;
    }

    /**
     * Enable 2FA for the authenticated user
     */
    public function enable(Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Enable 2FA for the user
        $user->enableTwoFactorAuthentication();
        
        // Generate QR code URL for Google Authenticator
        $qrCodeUrl = $this->google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $user->two_factor_secret
        );
        
        // Generate recovery codes
        $recoveryCodes = $user->generateRecoveryCodes();
        
        // Log security event
        $user->logSecurityEvent('2fa_enabled', [
            'method' => 'authenticator_app'
        ]);
        
        return response()->json([
            'success' => true,
            'data' => [
                'qr_code_url' => $qrCodeUrl,
                'recovery_codes' => $recoveryCodes,
                'secret' => $user->two_factor_secret,
            ],
            'message' => 'Two-factor authentication has been enabled. Please scan the QR code with your authenticator app.',
        ]);
    }

    /**
     * Confirm 2FA setup
     */
    public function confirm(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string',
        ]);
        
        $user = $request->user();
        
        // Verify the code
        $valid = $this->google2fa->verifyKey(
            $user->two_factor_secret,
            $request->code
        );
        
        if (!$valid) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid authentication code.',
            ], 422);
        }
        
        // Confirm 2FA
        $user->confirmTwoFactorAuthentication();
        
        // Log security event
        $user->logSecurityEvent('2fa_confirmed', [
            'method' => 'authenticator_app'
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Two-factor authentication has been confirmed.',
        ]);
    }

    /**
     * Disable 2FA for the authenticated user
     */
    public function disable(Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Disable 2FA
        $user->disableTwoFactorAuthentication();
        
        // Log security event
        $user->logSecurityEvent('2fa_disabled', [
            'method' => 'manual'
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Two-factor authentication has been disabled.',
        ]);
    }

    /**
     * Generate new recovery codes
     */
    public function generateRecoveryCodes(Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Check if 2FA is enabled
        if (!$user->two_factor_enabled || !$user->hasConfirmedTwoFactor()) {
            return response()->json([
                'success' => false,
                'message' => 'Two-factor authentication is not enabled or confirmed.',
            ], 400);
        }
        
        // Generate new recovery codes
        $recoveryCodes = $user->generateRecoveryCodes();
        
        // Log security event
        $user->logSecurityEvent('2fa_recovery_codes_regenerated');
        
        return response()->json([
            'success' => true,
            'data' => [
                'recovery_codes' => $recoveryCodes,
            ],
            'message' => 'New recovery codes have been generated.',
        ]);
    }

    /**
     * Get 2FA status
     */
    public function status(Request $request): JsonResponse
    {
        $user = $request->user();
        
        return response()->json([
            'success' => true,
            'data' => [
                'enabled' => $user->two_factor_enabled,
                'confirmed' => $user->hasConfirmedTwoFactor(),
            ],
        ]);
    }
}