<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Mobile\UpdateProfileRequest;
use App\Http\Resources\Api\Mobile\UserResource;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    /**
     * GET /api/mobile/profile
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user()->loadMissing('roles')->loadCount('companies');

        return ApiResponse::success(new UserResource($user));
    }

    /**
     * PATCH /api/mobile/profile
     * تحديث محدود — لا يشمل email/password/role.
     */
    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();

        $data = $request->validated();

        // حماية إضافية على مستوى Mass-Assignment.
        $allowed = ['name', 'mobile', 'bio', 'locale', 'job_title', 'signature'];
        $payload = array_intersect_key($data, array_flip($allowed));

        if (empty($payload)) {
            return ApiResponse::error('validation_failed', 'لا يوجد حقول للتحديث.', 422, [
                'errors' => ['_' => ['لم يُرسَل أي حقل مسموح بتحديثه.']],
            ]);
        }

        $user->fill($payload)->save();

        return ApiResponse::success(
            new UserResource($user->fresh()->loadMissing('roles')->loadCount('companies')),
            'تم تحديث الملف الشخصي.',
        );
    }
}
