<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Mobile\LoginRequest;
use App\Http\Resources\Api\Mobile\UserResource;
use App\Models\User;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * POST /api/mobile/login
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $data = $request->validated();

        $user = User::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], (string) $user->password)) {
            return ApiResponse::error(
                'invalid_credentials',
                'بيانات الدخول غير صحيحة.',
                401
            );
        }

        if (! $user->is_active) {
            return ApiResponse::error(
                'account_inactive',
                'الحساب موقوف. تواصل مع الإدارة.',
                403
            );
        }

        $deviceName = (string) ($data['device_name'] ?? 'mobile');
        $token = $user->createToken($deviceName, ['mobile'])->plainTextToken;

        return ApiResponse::success(
            data: [
                'token' => $token,
                'user'  => (new UserResource($user->loadCount('companies')))->resolve(),
            ],
            message: 'تم تسجيل الدخول.',
        );
    }

    /**
     * POST /api/mobile/logout
     */
    public function logout(Request $request): JsonResponse
    {
        $token = $request->user()?->currentAccessToken();

        if ($token) {
            $token->delete();
        }

        return ApiResponse::success(null, 'تم تسجيل الخروج.');
    }

    /**
     * GET /api/mobile/me
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->loadMissing('roles')->loadCount('companies');

        return ApiResponse::success(new UserResource($user));
    }
}
