<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Mobile\CompanyDocumentResource;
use App\Models\CompanyDocument;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CompanyDocumentController extends Controller
{
    /**
     * GET /api/mobile/documents
     */
    public function index(Request $request): JsonResponse
    {
        $companyId = (int) $request->attributes->get('active_company_id');

        $perPage = (int) $request->query('per_page', 20);
        $perPage = max(5, min($perPage, 50));

        $paginator = CompanyDocument::query()
            ->where('company_id', $companyId)
            ->orderByRaw("CASE WHEN alert_stage = 'expired' THEN 0 WHEN alert_stage = '7d' THEN 1 WHEN alert_stage = '30d' THEN 2 WHEN alert_stage = '60d' THEN 3 ELSE 4 END")
            ->latest('expiry_date')
            ->paginate($perPage);

        return ApiResponse::paginated($paginator, CompanyDocumentResource::class);
    }

    /**
     * GET /api/mobile/documents/{id}
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $companyId = (int) $request->attributes->get('active_company_id');

        $document = CompanyDocument::query()
            ->where('company_id', $companyId)
            ->find($id);

        if (! $document) {
            return ApiResponse::error('not_found', 'الوثيقة غير موجودة.', 404);
        }

        if (Gate::denies('view', $document)) {
            return ApiResponse::error('forbidden', 'لا تملك صلاحية عرض هذه الوثيقة.', 403);
        }

        return ApiResponse::success(new CompanyDocumentResource($document));
    }
}
