<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Mobile\SelectCompanyRequest;
use App\Http\Resources\Api\Mobile\CompanyResource;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CompanyController extends Controller
{
    /**
     * GET /api/mobile/companies
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $companies = $user->companies()
            ->select('companies.*')
            ->orderByDesc('company_user.is_active')
            ->orderByDesc('companies.created_at')
            ->get();

        return ApiResponse::success(
            CompanyResource::collection($companies)->resolve(),
            null,
            ['total' => $companies->count()],
        );
    }

    /**
     * POST /api/mobile/companies/select
     */
    public function select(SelectCompanyRequest $request): JsonResponse
    {
        $user      = $request->user();
        $companyId = (int) $request->validated()['company_id'];

        if (! $user->companies()->whereKey($companyId)->exists()) {
            return ApiResponse::error(
                'company_forbidden',
                'لا تنتمي إلى هذه المنشأة.',
                403
            );
        }

        DB::transaction(function () use ($user, $companyId) {
            DB::table('company_user')
                ->where('user_id', $user->id)
                ->update(['is_active' => false, 'updated_at' => now()]);

            $updated = DB::table('company_user')
                ->where('user_id', $user->id)
                ->where('company_id', $companyId)
                ->update(['is_active' => true, 'updated_at' => now()]);

            if ($updated === 0) {
                throw new \RuntimeException('تعذر تفعيل المنشأة المحددة.');
            }

            $user->forceFill(['active_company_id' => $companyId])->save();
        });

        return ApiResponse::success(
            ['active_company_id' => $companyId],
            'تم اختيار المنشأة.',
        );
    }
}
