<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Mobile\StoreServiceRequest;
use App\Http\Requests\Api\Mobile\StoreServiceRequestMessage;
use App\Http\Resources\Api\Mobile\ServiceRequestMessageResource;
use App\Http\Resources\Api\Mobile\ServiceRequestResource;
use App\Models\ServiceRequest;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ServiceRequestController extends Controller
{
    /**
     * GET /api/mobile/requests
     */
    public function index(Request $request): JsonResponse
    {
        $companyId = (int) $request->attributes->get('active_company_id');
        $user      = $request->user();

        $perPage = (int) $request->query('per_page', 20);
        $perPage = max(5, min($perPage, 50));

        $query = ServiceRequest::query()
            ->where('company_id', $companyId)
            ->with('service')
            ->latest();

        if (! $user->hasPermissionTo('service_requests.view_all')) {
            $query->where('user_id', $user->id);
        }

        return ApiResponse::paginated(
            $query->paginate($perPage),
            ServiceRequestResource::class
        );
    }

    /**
     * POST /api/mobile/requests
     */
    public function store(StoreServiceRequest $request): JsonResponse
    {
        $companyId = (int) $request->attributes->get('active_company_id');
        $user      = $request->user();

        if (Gate::denies('create', ServiceRequest::class)) {
            return ApiResponse::error('forbidden', 'لا تملك صلاحية إنشاء طلب خدمة.', 403);
        }

        $data = $request->validated();
        $data['user_id']        = $user->id;
        $data['company_id']     = $companyId;
        $data['status']         = 'pending';
        $data['applicant_type'] = $data['applicant_type'] ?? 'company';

        $serviceRequest = ServiceRequest::create($data);

        return ApiResponse::success(
            new ServiceRequestResource($serviceRequest->loadMissing('service')),
            'تم إنشاء الطلب.',
            [],
            201,
        );
    }

    /**
     * GET /api/mobile/requests/{id}
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $companyId = (int) $request->attributes->get('active_company_id');

        $serviceRequest = ServiceRequest::query()
            ->where('company_id', $companyId)
            ->find($id);

        if (! $serviceRequest) {
            return ApiResponse::error('not_found', 'الطلب غير موجود.', 404);
        }

        if (Gate::denies('view', $serviceRequest)) {
            return ApiResponse::error('forbidden', 'لا تملك صلاحية عرض هذا الطلب.', 403);
        }

        return ApiResponse::success(
            new ServiceRequestResource($serviceRequest->load(['service', 'messages.sender']))
        );
    }

    /**
     * POST /api/mobile/requests/{id}/messages
     */
    public function storeMessage(StoreServiceRequestMessage $request, int $id): JsonResponse
    {
        $companyId = (int) $request->attributes->get('active_company_id');
        $user      = $request->user();

        $serviceRequest = ServiceRequest::query()
            ->where('company_id', $companyId)
            ->find($id);

        if (! $serviceRequest) {
            return ApiResponse::error('not_found', 'الطلب غير موجود.', 404);
        }

        if (Gate::denies('view', $serviceRequest)) {
            return ApiResponse::error('forbidden', 'لا تملك صلاحية الرد على هذا الطلب.', 403);
        }

        $senderType = $user->hasPermissionTo('service_requests.update_status') ? 'admin' : 'client';

        $message = $serviceRequest->messages()->create([
            'sender_id'   => $user->id,
            'sender_type' => $senderType,
            'body'        => $request->validated()['body'],
        ]);

        return ApiResponse::success(
            new ServiceRequestMessageResource($message->loadMissing('sender')),
            'تم إرسال الرسالة.',
            [],
            201,
        );
    }
}
