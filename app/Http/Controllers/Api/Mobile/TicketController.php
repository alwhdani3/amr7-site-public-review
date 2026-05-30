<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Mobile\StoreTicket;
use App\Http\Requests\Api\Mobile\StoreTicketReply;
use App\Http\Resources\Api\Mobile\TicketReplyResource;
use App\Http\Resources\Api\Mobile\TicketResource;
use App\Models\Ticket;
use App\Models\TicketReply;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TicketController extends Controller
{
    /**
     * GET /api/mobile/tickets
     */
    public function index(Request $request): JsonResponse
    {
        $companyId = (int) $request->attributes->get('active_company_id');
        $user      = $request->user();

        $perPage = (int) $request->query('per_page', 20);
        $perPage = max(5, min($perPage, 50));

        $query = Ticket::query()
            ->where('company_id', $companyId)
            ->latest();

        if (! $user->hasPermissionTo('tickets.view_all')) {
            $query->where('user_id', $user->id);
        }

        return ApiResponse::paginated(
            $query->paginate($perPage),
            TicketResource::class
        );
    }

    /**
     * POST /api/mobile/tickets
     */
    public function store(StoreTicket $request): JsonResponse
    {
        $companyId = (int) $request->attributes->get('active_company_id');
        $user      = $request->user();

        if (Gate::denies('create', Ticket::class)) {
            return ApiResponse::error('forbidden', 'لا تملك صلاحية إنشاء تذكرة.', 403);
        }

        $data = $request->validated();

        $ticket = Ticket::create([
            'company_id'    => $companyId,
            'user_id'       => $user->id,
            'subject'       => $data['subject'],
            'description'   => $data['description'],
            'priority'      => $data['priority'] ?? 'medium',
            'department_id' => $data['department_id'] ?? null,
            'status'        => 'open',
            'source_type'   => 'mobile_api',
        ]);

        return ApiResponse::success(
            new TicketResource($ticket),
            'تم إنشاء التذكرة.',
            [],
            201,
        );
    }

    /**
     * GET /api/mobile/tickets/{id}
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $companyId = (int) $request->attributes->get('active_company_id');

        $ticket = Ticket::query()
            ->where('company_id', $companyId)
            ->with(['replies.user'])
            ->find($id);

        if (! $ticket) {
            return ApiResponse::error('not_found', 'التذكرة غير موجودة.', 404);
        }

        if (Gate::denies('view', $ticket)) {
            return ApiResponse::error('forbidden', 'لا تملك صلاحية عرض هذه التذكرة.', 403);
        }

        return ApiResponse::success([
            'ticket'  => (new TicketResource($ticket))->resolve(),
            'replies' => TicketReplyResource::collection($ticket->replies)->resolve(),
        ]);
    }

    /**
     * POST /api/mobile/tickets/{id}/replies
     */
    public function storeReply(StoreTicketReply $request, int $id): JsonResponse
    {
        $companyId = (int) $request->attributes->get('active_company_id');
        $user      = $request->user();

        $ticket = Ticket::query()
            ->where('company_id', $companyId)
            ->find($id);

        if (! $ticket) {
            return ApiResponse::error('not_found', 'التذكرة غير موجودة.', 404);
        }

        if (Gate::denies('reply', $ticket)) {
            return ApiResponse::error('forbidden', 'لا تملك صلاحية الرد على هذه التذكرة.', 403);
        }

        $reply = TicketReply::create([
            'ticket_id' => $ticket->id,
            'user_id'   => $user->id,
            'message'   => $request->validated()['message'],
        ]);

        $ticket->forceFill(['last_reply_at' => now()])->save();

        return ApiResponse::success(
            new TicketReplyResource($reply->loadMissing('user')),
            'تم إرسال الرد.',
            [],
            201,
        );
    }
}
