<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\OtpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class OtpController extends Controller
{
    public function __construct(protected OtpService $otp)
    {
    }

    public function showPhoneLogin()
    {
        return view('auth.phone-login', [
            'enabled' => $this->otp->isEnabled(),
        ]);
    }

    public function send(Request $request): JsonResponse
    {
        if (! $this->otp->isEnabled()) {
            return $this->disabledResponse();
        }

        $validated = $request->validate([
            'phone' => ['required', 'string', 'max:32'],
        ]);

        $result = $this->otp->mint(
            $validated['phone'],
            $request->ip(),
            (string) $request->userAgent()
        );

        if (! $result['ok']) {
            return response()->json([
                'ok' => false,
                'message' => $this->reasonMessage($result['reason'] ?? 'error'),
            ], 422);
        }

        return response()->json([
            'ok' => true,
            'phone' => $result['phone'],
            'expires_in_minutes' => $result['expires_in_minutes'],
            'message' => __('auth_otp_send_action'),
        ]);
    }

    public function verify(Request $request): JsonResponse
    {
        if (! $this->otp->isEnabled()) {
            return $this->disabledResponse();
        }

        $validated = $request->validate([
            'phone' => ['required', 'string', 'max:32'],
            'code' => ['required', 'string', 'min:4', 'max:8'],
        ]);

        $result = $this->otp->verify($validated['phone'], $validated['code']);

        if (! $result['ok']) {
            return response()->json([
                'ok' => false,
                'message' => $this->reasonMessage($result['reason'] ?? 'error'),
                'attempts_left' => $result['attempts_left'] ?? null,
            ], 422);
        }

        if ($result['user']) {
            Auth::login($result['user']);
            Session::regenerate();
        }

        return response()->json([
            'ok' => true,
            'authenticated' => (bool) $result['user'],
            'redirect' => $result['user']
                ? route('dashboard', absolute: false)
                : null,
            'message' => __('auth_otp_verify_action'),
        ]);
    }

    protected function disabledResponse(): JsonResponse
    {
        return response()->json([
            'ok' => false,
            'disabled' => true,
            'message' => __('auth_otp_service_disabled'),
        ], 410);
    }

    protected function reasonMessage(string $reason): string
    {
        return match ($reason) {
            'disabled' => __('auth_otp_service_disabled'),
            'cooldown', 'throttled' => __('auth_otp_throttled'),
            'mismatch' => __('auth_otp_mismatch'),
            'expired' => __('auth_otp_expired'),
            'locked' => __('auth_otp_locked'),
            'not_found' => __('auth_otp_expired'),
            'invalid_phone', 'invalid_input' => __('auth_otp_phone_label'),
            default => __('auth_otp_service_disabled'),
        };
    }
}
