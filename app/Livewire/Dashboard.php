<?php

namespace App\Livewire;

use App\Mail\Operations\NewTicketAdminMail;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\Models\Attachment;
use App\Models\Company;
use App\Models\CompanyFile;
use App\Models\CompanyInvitation;
use App\Models\Customer;
use App\Models\Service;
use App\Models\ServiceRequest;
use App\Models\Ticket;
use App\Models\TicketReply;
use App\Models\CompanyDocument;
use App\Models\User;

class Dashboard extends Component
{
    use WithFileUploads;

    public $activeCompanyId;

    // Portal Shell: sidebar deep-link مزامَن مع URL (?section=home|profile|...).
    // الزيارة المباشرة من /dashboard?section=tickets أو navigate من sidebar تعمل بدون state loss.
    #[Url(as: 'section', keep: false)]
    public string $section = 'home';

    #[Url(as: 'service', keep: false)]
    public ?int $serviceRequestServiceId = null;

    // Company Info
    public $name;
    public $unified_number;
    public $tax_number;
    public $city;
    public $address;

    // Users
    public $newUserEmail;
    public $newUserRole = 'employee'; // P1.2: توحيد company_user.role إلى admin/employee

    // Phase A: team invitations
    public string $inviteEmail = '';
    public string $inviteRole = 'employee';
    public ?string $lastInvitationLink = null; // plain link surfaced once after creation

    // Phase B (UI): permissions matrix editor — opens via openPermissionsManager.
    // permissionsMatrix is the *in-memory* draft; nothing is written to the pivot
    // until saveUserPermissions() runs. permissionsUserId is the target row, scoped
    // to the active company so a tampered Livewire payload can never surface a
    // user from a different tenant.
    public ?int $permissionsUserId = null;
    public array $permissionsMatrix = [];

    // Compliance / documents
    public $docType = 'cr';
    public $docNumber;
    public $docIssuer;
    public $docIssueDate;
    public $docExpiry;
    public $docNotes;
    public $docFile;

    // Files
    public $newFile;
    public string $newFileTitle = '';
    public string $newFileCategory = 'general';

    // Tickets
    public $selectedTicketId = null;
    public $newTicketSubject = '';
    public $newTicketMessage = '';
    public $newTicketAttachments = [];
    public $replyMessage = '';
    public $replyAttachments = [];
    public $search = ''; // تم إضافة متغير البحث
    public string $ticketStatusFilter = 'all'; // Polish: UI state — لا منطق أعمال جديد
    public string $fileTypeFilter = 'all'; // Polish: فلتر mime للملفات — UI state فقط

    // Service requests
    public string $serviceRequestNotes = '';
    // Phase 3: optional file uploads attached to the service request wizard
    // (matches Etmam Step 2 PDF/DOCX/JPG attach UX).
    public array $serviceRequestAttachments = [];
    // Catalog filters (Wizard Step 1)
    public string $serviceSearch = '';
    public string $servicePlatformFilter = 'all';
    // Wizard state (1=catalog, 2=details, 3=review, 4=success)
    public int $serviceRequestStep = 1;
    public ?int $createdServiceRequestId = null;
    // Request-history filters + drill-in
    public string $requestHistorySearch = '';
    public string $requestHistoryStatusFilter = 'all';
    public ?int $selectedServiceRequestId = null;
    // Phase 4: client reply draft for the messages thread of the
    // currently-open request in the details panel.
    public string $serviceRequestReplyBody = '';

    public function mount()
    {
        $this->activeCompanyId = session('active_company_id') ?? Auth::user()?->primaryCompany()?->id;

        if (! $this->activeCompanyId) {
            return redirect()->route('company.select');
        }

        if (request()->has('ticket_id')) {
            $this->section = 'tickets';
            $this->selectedTicketId = request()->ticket_id;
        }

        if (request()->has('section')) {
            $reqSection = request()->section;
            // Polish: 'home' للوحة الترحيبية + 'ai-review' لـ backoffice
            $allowedSections = ['home', 'profile', 'users', 'files', 'compliance', 'tickets', 'financial', 'requests', 'request-history', 'invoices', 'ai-review'];

            if (in_array($reqSection, $allowedSections)) {
                // Phase B: server-side permissions enforcement. admin/owner always
                // pass; employees fall through to the matrix stored on
                // company_user.permissions (with EMPLOYEE_DEFAULTS for null rows).
                // `ai-review` keeps its own backoffice check — it is not part of
                // the per-tenant matrix.
                $user = Auth::user();
                if ($reqSection === 'ai-review') {
                    $sectionAllowed = (bool) ($user
                        && method_exists($user, 'hasBackofficeAccess')
                        && $user->hasBackofficeAccess());
                } else {
                    $sectionAllowed = (bool) ($user
                        && method_exists($user, 'canAccessCompanySection')
                        && $user->canAccessCompanySection($reqSection, (int) $this->activeCompanyId));
                }
                // Forbidden → fall back to `home`. Home is reachable as long as
                // dashboard.view is granted (it is in EMPLOYEE_DEFAULTS). The
                // home section itself doesn't crash if even that has been
                // explicitly revoked — the user just sees the empty welcome.
                $this->section = $sectionAllowed ? $reqSection : 'home';
            } else {
                $this->section = 'home';
            }
        }

        if (request()->has('service')) {
            $this->section = 'requests';
            $this->serviceRequestServiceId = (int) request()->query('service');
            // Deep-link enters the wizard at Step 2 (details) — service already chosen.
            $this->serviceRequestStep = 2;
        }

        if (request()->has('request_id')) {
            $this->section = 'request-history';
            $this->selectedServiceRequestId = (int) request()->query('request_id');
        }

        $this->loadCompanyData();
        $this->setNoIndex();
    }

    private function loadCompanyData()
    {
        if ($this->activeCompany) {
            $this->name = $this->activeCompany->name;
            $this->unified_number = $this->activeCompany->unified_number;
            $this->tax_number = $this->activeCompany->tax_number;
            $this->city = $this->activeCompany->city;
            $this->address = $this->activeCompany->address;
        }
    }

    #[Computed]
    public function activeCompany()
    {
        return Company::find($this->activeCompanyId);
    }

    private function assertCompanyAdmin(): void
    {
        $user = Auth::user();

        $isCompanyAdmin = $user
            && $user->companies()
                ->whereKey((int) $this->activeCompanyId)
                ->wherePivotIn('role', ['admin', 'owner'])
                ->wherePivot('is_active', true)
                ->exists();

        abort_unless($isCompanyAdmin, 403);
    }

    private function assertCompanyMember(): void
    {
        $user = Auth::user();

        $isMember = $user
            && $user->companies()
                ->whereKey((int) $this->activeCompanyId)
                ->wherePivot('is_active', true)
                ->exists();

        abort_unless($isMember, 403);
    }

    private function assertCanManageComplianceDocuments(): void
    {
        $user = Auth::user();

        $isBackoffice = $user
            && method_exists($user, 'hasBackofficeAccess')
            && $user->hasBackofficeAccess();

        $isCompanyManager = $user
            && $user->companies()
                ->whereKey((int) $this->activeCompanyId)
                ->wherePivotIn('role', ['admin', 'owner'])
                ->wherePivot('is_active', true)
                ->exists();

        if ($isBackoffice || $isCompanyManager) {
            return;
        }

        $this->dispatch('notify', type: 'error', message: 'لا تملك صلاحية حذف وثائق الامتثال.');
        abort(403, 'لا تملك صلاحية حذف وثائق الامتثال.');
    }

    #[Computed]
    public function isCompanyAdmin(): bool
    {
        $user = Auth::user();

        return (bool) ($user
            && $user->companies()
                ->whereKey((int) $this->activeCompanyId)
                ->wherePivotIn('role', ['admin', 'owner'])
                ->wherePivot('is_active', true)
                ->exists());
    }

    // Capped at 100 most-recent tickets — protects the page from OOM on
    // tenants with very large ticket histories. The dashboard is a
    // glance-view, not an archive; older tickets are reachable via the
    // dedicated tickets page.
    public function getTicketsProperty()
    {
        return Ticket::where('company_id', $this->activeCompanyId)
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('subject', 'like', '%' . $this->search . '%')
                      ->orWhere('ticket_number', 'like', '%' . $this->search . '%')
                      ->orWhere('id', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->ticketStatusFilter && $this->ticketStatusFilter !== 'all', function ($query) {
                $query->where('status', $this->ticketStatusFilter);
            })
            ->latest()
            ->limit(100)
            ->get();
    }

    // تم إضافة هذه الدالة لجلب عدد التذاكر المفتوحة (تُستخدم في الواجهة)
    public function getOpenCountProperty()
    {
        return Ticket::where('company_id', $this->activeCompanyId)
            ->where('status', 'open')
            ->count();
    }

    public function getClosedCountProperty()
    {
        return Ticket::where('company_id', $this->activeCompanyId)
            ->where('status', 'closed')
            ->count();
    }

    public function getSelectedTicketProperty()
    {
        if (! $this->selectedTicketId) return null;

        return Ticket::with(['user', 'replies.user', 'replies.attachments', 'attachments'])
            ->where('company_id', $this->activeCompanyId)
            ->find($this->selectedTicketId);
    }

    #[Computed]
    public function stats(): array
    {
        $companyId = $this->activeCompanyId;
        if (! $companyId) return ['users' => 0, 'tickets' => 0, 'files' => 0, 'compliance' => 0, 'fs_requests' => 0];

        $totalDocs = 5; 
        $validDocs = CompanyDocument::where('company_id', $companyId)->whereDate('expiry_date', '>', now())->count();
        $complianceScore = $totalDocs > 0 ? round(($validDocs / $totalDocs) * 100) : 0;

        return [
            'users'       => $this->activeCompany->users()->count(),
            'tickets'     => Ticket::where('company_id', $companyId)->where('status', '!=', 'closed')->count(),
            'files'       => CompanyFile::where('company_id', $companyId)->count(),
            'compliance'  => $complianceScore,
            'fs_requests' => Auth::user()?->financialStatementRequests()->count() ?? 0,
        ];
    }

    public function getCompanyFilesProperty()
    {
        return CompanyFile::where('company_id', $this->activeCompanyId)
            ->when($this->fileTypeFilter === 'pdf', fn ($q) => $q->where('mime', 'like', '%pdf%'))
            ->when($this->fileTypeFilter === 'image', fn ($q) => $q->where('mime', 'like', 'image/%'))
            ->when($this->fileTypeFilter === 'other', fn ($q) => $q->where(function ($qq) {
                $qq->where('mime', 'not like', '%pdf%')
                   ->where('mime', 'not like', 'image/%');
            }))
            ->latest()
            ->limit(100)
            ->get();
    }

    /**
     * Polish: ترجمة نوع ملف من mime لـ label عربي.
     */
    public function fileMimeLabel(?string $mime): string
    {
        $m = strtolower((string) $mime);
        return match (true) {
            str_contains($m, 'pdf')   => 'PDF',
            str_starts_with($m, 'image/') => 'صورة',
            str_contains($m, 'word') || str_contains($m, 'msword') || str_contains($m, 'officedocument.wordprocessingml') => 'Word',
            str_contains($m, 'excel') || str_contains($m, 'spreadsheetml') => 'Excel',
            str_contains($m, 'zip') || str_contains($m, 'rar')           => 'مضغوط',
            default => 'ملف',
        };
    }

    /**
     * Polish: حجم ملف بصيغة مقروءة بدل bytes.
     */
    public function fileSizeHuman(?int $bytes): string
    {
        $b = (int) ($bytes ?? 0);
        if ($b <= 0) return '—';
        if ($b >= 1048576) return round($b / 1048576, 1) . ' MB';
        if ($b >= 1024)    return round($b / 1024, 1) . ' KB';
        return $b . ' B';
    }

    public function getComplianceDocumentsProperty()
    {
        return CompanyDocument::where('company_id', $this->activeCompanyId)
            ->latest()
            ->limit(100)
            ->get();
    }

    public function getFinancialStatementRequestsProperty()
    {
        return Auth::user()
            ?->financialStatementRequests()
            ->latest()
            ->limit(100)
            ->get() ?? collect();
    }

    public function getServiceRequestsProperty()
    {
        return ServiceRequest::query()
            ->with('service')
            ->where('company_id', $this->activeCompanyId)
            ->where('user_id', Auth::id())
            ->when($this->requestHistoryStatusFilter !== 'all', function ($q) {
                $q->where('status', $this->requestHistoryStatusFilter);
            })
            ->when(trim($this->requestHistorySearch) !== '', function ($q) {
                $term = trim($this->requestHistorySearch);
                $q->where(function ($qq) use ($term) {
                    $qq->where('id', $term)
                       ->orWhere('notes', 'like', '%' . $term . '%')
                       ->orWhereHas('service', function ($sq) use ($term) {
                           $sq->where('title_ar', 'like', '%' . $term . '%')
                              ->orWhere('title_en', 'like', '%' . $term . '%')
                              ->orWhere('slug', 'like', '%' . $term . '%');
                       });
                });
            })
            ->latest()
            ->limit(100)
            ->get();
    }

    public function getAvailableServicesProperty()
    {
        return Service::query()
            ->with('platform')
            ->select(['id', 'service_platform_id', 'title_ar', 'title_en', 'slug', 'excerpt_ar', 'excerpt_en', 'duration', 'price'])
            ->where('is_active', true)
            ->when(trim($this->serviceSearch) !== '', function ($query) {
                $term = '%' . trim($this->serviceSearch) . '%';
                $query->where(function ($q) use ($term) {
                    $q->where('title_ar', 'like', $term)
                      ->orWhere('title_en', 'like', $term)
                      ->orWhere('slug', 'like', $term)
                      ->orWhere('excerpt_ar', 'like', $term)
                      ->orWhere('excerpt_en', 'like', $term);
                });
            })
            ->when($this->servicePlatformFilter !== 'all', function ($query) {
                $known = array_keys($this->servicePlatformFilters());

                if ($this->servicePlatformFilter === 'other') {
                    $query->whereDoesntHave('platform', fn ($q) => $q->whereIn('slug', array_filter($known, fn ($key) => $key !== 'all' && $key !== 'other')));
                    return;
                }

                $query->whereHas('platform', fn ($q) => $q->where('slug', $this->servicePlatformFilter));
            })
            ->orderBy('id')
            ->get();
    }

    public function servicePlatformFilters(): array
    {
        return [
            'all' => 'الكل',
            'ministry-of-commerce' => 'وزارة التجارة',
            'ministry-of-investment' => 'وزارة الاستثمار',
            'ministry-of-foreign-affairs' => 'وزارة الخارجية',
            'ministry-of-human-resources' => 'وزارة الموارد البشرية',
            'ministry-of-media' => 'وزارة الإعلام',
            'iqama-visas' => 'الإقامة والتأشيرات',
            'other' => 'أخرى',
        ];
    }

    public function getSelectedServiceProperty(): ?Service
    {
        if (! $this->serviceRequestServiceId) {
            return null;
        }

        return Service::query()
            ->with('platform')
            ->whereKey((int) $this->serviceRequestServiceId)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Wizard Step 1 → Step 2. Marks the chosen service and advances the wizard.
     */
    public function selectServiceForRequest(int $serviceId): void
    {
        $service = Service::query()
            ->whereKey($serviceId)
            ->where('is_active', true)
            ->first();

        if (! $service) {
            $this->dispatch('notify', type: 'error', message: 'الخدمة المختارة غير متاحة.');
            return;
        }

        $this->serviceRequestServiceId = $service->id;
        $this->serviceRequestStep = 2;
        $this->createdServiceRequestId = null;
        $this->section = 'requests';
        $this->resetValidation(['serviceRequestServiceId', 'serviceRequestNotes']);
    }

    /**
     * Wizard Step 2 → Step 1. User decided to swap the chosen service.
     */
    public function changeServiceRequestService(): void
    {
        $this->serviceRequestStep = 1;
        $this->createdServiceRequestId = null;
        $this->serviceRequestAttachments = [];
        $this->resetValidation(['serviceRequestServiceId', 'serviceRequestNotes', 'serviceRequestAttachments']);
    }

    /**
     * Wizard Step 2 → Step 3. Validates the notes textarea + optional
     * attachments and advances.
     */
    public function reviewServiceRequest(): void
    {
        $this->assertCompanyMember();

        $this->validate([
            'serviceRequestServiceId'     => 'required|integer|exists:services,id',
            'serviceRequestNotes'         => 'required|string|min:5|max:5000',
            // Phase 3: optional attachments, mime/size whitelist matches the
            // existing ticket attachments rule for consistency.
            'serviceRequestAttachments.*' => 'nullable|file|max:10240|mimes:pdf,jpg,jpeg,png,doc,docx',
        ], [
            'serviceRequestServiceId.required' => 'يرجى اختيار الخدمة.',
            'serviceRequestServiceId.exists'   => 'الخدمة المختارة غير متاحة.',
            'serviceRequestNotes.required'     => 'يرجى كتابة تفاصيل الطلب.',
            'serviceRequestNotes.min'          => 'تفاصيل الطلب قصيرة جدًا.',
            'serviceRequestAttachments.*.mimes' => 'يجب أن تكون المرفقات من نوع PDF أو صورة أو مستند Word.',
            'serviceRequestAttachments.*.max'   => 'حجم أحد المرفقات يتجاوز الحد المسموح (10 ميجابايت).',
        ]);

        if (! $this->selectedService) {
            $this->addError('serviceRequestServiceId', 'الخدمة المختارة غير متاحة.');
            return;
        }

        $this->serviceRequestStep = 3;
    }

    /**
     * Drop a queued attachment from Step 2 before submission. Indices reflect
     * the current order of $serviceRequestAttachments.
     */
    public function removeServiceRequestAttachment(int $index): void
    {
        if (! array_key_exists($index, $this->serviceRequestAttachments)) {
            return;
        }

        unset($this->serviceRequestAttachments[$index]);
        $this->serviceRequestAttachments = array_values($this->serviceRequestAttachments);
        $this->resetValidation('serviceRequestAttachments');
    }

    /**
     * Wizard Step 3 → Step 2. Back from review to edit details.
     */
    public function backToServiceRequestDetails(): void
    {
        $this->serviceRequestStep = 2;
    }

    /**
     * Wizard Step 4 (success) → reset to Step 1 for a new request.
     */
    public function startNewServiceRequest(): void
    {
        $this->reset(['serviceRequestNotes', 'serviceRequestServiceId', 'createdServiceRequestId', 'serviceRequestAttachments']);
        $this->serviceRequestStep = 1;
        $this->resetValidation();
    }

    /**
     * Open a single request's details panel from the history table.
     */
    public function viewServiceRequest(int $requestId): void
    {
        // Ownership enforced by the company-scoped query in
        // selectedServiceRequest accessor — only the active company's
        // requests are reachable.
        $this->selectedServiceRequestId = $requestId;
        // Phase 4: clear any draft reply when switching requests so the
        // textarea on the next request starts empty.
        $this->serviceRequestReplyBody = '';
        $this->resetValidation('serviceRequestReplyBody');
    }

    public function closeServiceRequestDetails(): void
    {
        $this->selectedServiceRequestId = null;
        $this->serviceRequestReplyBody = '';
        $this->resetValidation('serviceRequestReplyBody');
    }

    /**
     * Phase 4: persist a customer reply on the service-request thread.
     * Sender type is hard-coded to `client` — backoffice replies live in
     * Filament and use a different sender_type. Ownership is enforced by
     * scoping the lookup to the active company + the authenticated user
     * (matches the rest of the request-history accessors).
     */
    public function submitServiceRequestReply(): void
    {
        $this->assertCompanyMember();

        if (! $this->selectedServiceRequestId) {
            return;
        }

        $this->validate([
            'serviceRequestReplyBody' => 'required|string|min:2|max:5000',
        ], [
            'serviceRequestReplyBody.required' => 'يرجى كتابة محتوى الرد.',
            'serviceRequestReplyBody.min'      => 'الرد قصير جدًا.',
            'serviceRequestReplyBody.max'      => 'الرد يتجاوز الحد المسموح (5000 حرف).',
        ]);

        $request = ServiceRequest::query()
            ->where('company_id', $this->activeCompanyId)
            ->where('user_id', Auth::id())
            ->whereKey((int) $this->selectedServiceRequestId)
            ->first();

        if (! $request) {
            $this->dispatch('notify', type: 'error', message: 'لم يعد الطلب متاحًا.');
            return;
        }

        try {
            \App\Models\ServiceRequestMessage::create([
                'service_request_id' => $request->id,
                'sender_id'          => Auth::id(),
                'sender_type'        => 'client',
                'body'               => trim($this->serviceRequestReplyBody),
            ]);
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('notify', type: 'error', message: 'تعذر إرسال الرد. حاول مرة أخرى.');
            return;
        }

        $this->serviceRequestReplyBody = '';
        $this->resetValidation('serviceRequestReplyBody');
        $this->dispatch('notify', type: 'success', message: 'تم إرسال الرد بنجاح.');
    }

    /**
     * Phase 4: messages belonging to the request currently opened in the
     * request-history details panel. Sorted oldest-first so the newest
     * reply lands at the bottom of the thread.
     */
    public function getSelectedServiceRequestMessagesProperty()
    {
        if (! $this->selectedServiceRequestId || ! $this->activeCompanyId) {
            return collect();
        }

        // Defence in depth: re-scope by company + user so a tampered
        // selectedServiceRequestId can never surface another tenant's
        // thread, even if the ownership check above is bypassed.
        $owns = ServiceRequest::query()
            ->where('company_id', $this->activeCompanyId)
            ->where('user_id', Auth::id())
            ->whereKey((int) $this->selectedServiceRequestId)
            ->exists();

        if (! $owns) {
            return collect();
        }

        return \App\Models\ServiceRequestMessage::query()
            ->with('sender:id,name,email')
            ->where('service_request_id', (int) $this->selectedServiceRequestId)
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();
    }

    public function getSelectedServiceRequestProperty(): ?ServiceRequest
    {
        if (! $this->selectedServiceRequestId) {
            return null;
        }

        return ServiceRequest::query()
            ->with('service')
            ->where('company_id', $this->activeCompanyId)
            ->where('user_id', Auth::id())
            ->whereKey((int) $this->selectedServiceRequestId)
            ->first();
    }

    /**
     * Status filter chips for the request-history view. Keys are the actual
     * status values stored in service_requests.status; labels are Arabic.
     */
    public function requestHistoryStatusFilters(): array
    {
        return [
            'all'              => 'الكل',
            'new'              => 'جديد',
            'in_review'        => 'قيد المراجعة',
            'pending_customer' => 'بانتظار العميل',
            'completed'        => 'مكتمل',
        ];
    }

    public function getRequestHistoryStatsProperty(): array
    {
        $base = ServiceRequest::query()
            ->where('company_id', $this->activeCompanyId)
            ->where('user_id', Auth::id());

        return [
            'all'              => (clone $base)->count(),
            'new'              => (clone $base)->where('status', 'new')->count(),
            'in_review'        => (clone $base)->where('status', 'in_review')->count(),
            'pending_customer' => (clone $base)->where('status', 'pending_customer')->count(),
            'completed'        => (clone $base)->where('status', 'completed')->count(),
        ];
    }

    /* =============================================================
       Polish — Home Overview helpers (presentation فقط، لا منطق أعمال)
    ============================================================= */

    /**
     * عدد الوثائق المنتهية لمنشأة المستخدم النشطة.
     */
    public function getExpiredDocumentsCountProperty(): int
    {
        if (! $this->activeCompanyId) return 0;
        return (int) CompanyDocument::where('company_id', $this->activeCompanyId)
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '<=', now())
            ->count();
    }

    /**
     * عدد الوثائق القريبة من الانتهاء (خلال 30 يومًا).
     */
    public function getExpiringDocumentsCountProperty(): int
    {
        if (! $this->activeCompanyId) return 0;
        return (int) CompanyDocument::where('company_id', $this->activeCompanyId)
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '>', now())
            ->whereDate('expiry_date', '<=', now()->addDays(30))
            ->count();
    }

    /**
     * Top 5 documents that need attention on the home screen:
     * expired + expiring within 30 days, ordered by expiry_date ascending
     * (most-urgent first). Documents without an expiry date are excluded.
     */
    public function getDocumentExpiryAlertsProperty()
    {
        if (! $this->activeCompanyId) {
            return collect();
        }

        return CompanyDocument::where('company_id', $this->activeCompanyId)
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '<=', now()->addDays(30))
            ->orderBy('expiry_date')
            ->take(5)
            ->get();
    }

    /**
     * Read-only list of invoices for the active company, latest-first.
     * Returns an empty collection when the billing tables aren't migrated
     * yet so this section degrades to a friendly empty state on prod
     * before `php artisan migrate` runs.
     */
    public function getInvoicesProperty()
    {
        if (! $this->activeCompanyId || ! \Illuminate\Support\Facades\Schema::hasTable('invoices')) {
            return collect();
        }

        return \App\Models\Invoice::where('company_id', $this->activeCompanyId)
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();
    }

    public function getInvoiceStatusLabel(string $status): string
    {
        return match ($status) {
            \App\Models\Invoice::STATUS_DRAFT     => 'مسودة',
            \App\Models\Invoice::STATUS_ISSUED    => 'صادرة',
            \App\Models\Invoice::STATUS_PAID      => 'مدفوعة',
            \App\Models\Invoice::STATUS_OVERDUE   => 'متأخرة',
            \App\Models\Invoice::STATUS_CANCELLED => 'ملغاة',
            default                                => $status,
        };
    }

    public function getInvoiceStatusClasses(string $status): string
    {
        return match ($status) {
            \App\Models\Invoice::STATUS_PAID      => 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100',
            \App\Models\Invoice::STATUS_ISSUED    => 'bg-sky-50 text-sky-700 ring-1 ring-sky-100',
            \App\Models\Invoice::STATUS_OVERDUE   => 'bg-amber-50 text-amber-700 ring-1 ring-amber-100',
            \App\Models\Invoice::STATUS_CANCELLED => 'bg-red-50 text-red-700 ring-1 ring-red-100',
            default                                => 'bg-slate-50 text-slate-700 ring-1 ring-slate-100',
        };
    }

    /**
     * عدد التذاكر المفتوحة لمنشأة المستخدم النشطة.
     */
    public function getOpenTicketsCountProperty(): int
    {
        if (! $this->activeCompanyId) return 0;
        return (int) Ticket::where('company_id', $this->activeCompanyId)
            ->where('status', '!=', 'closed')
            ->count();
    }

    /**
     * عدد استخراجات AI بانتظار المراجعة (backoffice فقط).
     */
    public function getPendingAiReviewsCountProperty(): int
    {
        $user = Auth::user();
        $isBackoffice = $user && method_exists($user, 'hasBackofficeAccess') && $user->hasBackofficeAccess();
        if (! $isBackoffice) return 0;

        // لا نعتمد على Model AiDocumentExtraction مباشرة هنا — إن لم تكن migration شُغّلت، الجدول قد لا يوجد.
        // نستخدم try/catch ليرجع 0 بأمان حال غياب الجدول.
        try {
            if (! class_exists(\App\Models\AiDocumentExtraction::class)) return 0;
            return (int) \App\Models\AiDocumentExtraction::query()
                ->when($this->activeCompanyId, fn ($q) => $q->where('company_id', $this->activeCompanyId))
                ->where('status', 'ready_for_review')
                ->count();
        } catch (\Throwable $e) {
            return 0;
        }
    }

    /**
     * يرجع قائمة Required Actions كصفوف منظمة للعرض.
     * كل عنصر: ['key', 'label', 'count', 'severity', 'cta_section', 'cta_label']
     */
    public function getRequiredActionsProperty(): array
    {
        $actions = [];
        $company = $this->activeCompany;

        // 1) نواقص ملف المنشأة
        if ($company) {
            $completion = (int) ($company->profile_completion_percent ?? 0);
            if ($completion < 100) {
                $missing = method_exists($company, 'profileMissingFields')
                    ? $company->profileMissingFields()
                    : [];
                $actions[] = [
                    'key'         => 'profile_incomplete',
                    'label'       => 'أكمل بيانات ملف المنشأة',
                    'count'       => count($missing),
                    'severity'    => $completion < 50 ? 'high' : 'medium',
                    'cta_section' => 'profile',
                    'cta_label'   => 'إكمال البيانات',
                ];
            }
        }

        // 2) وثائق منتهية
        $expired = $this->expiredDocumentsCount;
        if ($expired > 0) {
            $actions[] = [
                'key'         => 'expired_documents',
                'label'       => 'وثائق منتهية الصلاحية',
                'count'       => $expired,
                'severity'    => 'high',
                'cta_section' => 'compliance',
                'cta_label'   => 'مراجعة الوثائق',
            ];
        }

        // 3) وثائق قريبة من الانتهاء
        $expiring = $this->expiringDocumentsCount;
        if ($expiring > 0) {
            $actions[] = [
                'key'         => 'expiring_documents',
                'label'       => 'وثائق قريبة من الانتهاء',
                'count'       => $expiring,
                'severity'    => 'medium',
                'cta_section' => 'compliance',
                'cta_label'   => 'مراجعة الوثائق',
            ];
        }

        // 4) تذاكر مفتوحة
        $openTickets = $this->openTicketsCount;
        if ($openTickets > 0) {
            $actions[] = [
                'key'         => 'open_tickets',
                'label'       => 'تذاكر دعم مفتوحة',
                'count'       => $openTickets,
                'severity'    => 'low',
                'cta_section' => 'tickets',
                'cta_label'   => 'الانتقال للتذاكر',
            ];
        }

        // 5) AI reviews — backoffice فقط
        $pendingAi = $this->pendingAiReviewsCount;
        if ($pendingAi > 0) {
            $actions[] = [
                'key'         => 'pending_ai_reviews',
                'label'       => 'استخراجات بانتظار المراجعة',
                'count'       => $pendingAi,
                'severity'    => 'medium',
                'cta_section' => 'ai-review',
                'cta_label'   => 'مراجعة الاستخراجات',
            ];
        }

        return $actions;
    }

    /* =============================================================
       Stage 1 — Translation helpers (آمنة: ترجع نص للمستخدم دائمًا)
    ============================================================= */

    /**
     * يترجم status لقيمة عربية واضحة. لا يُسرّب المفتاح الخام أبدًا.
     */
    public function translateStatus(?string $status): string
    {
        $key = strtolower(trim((string) $status));

        // Phase 8B: توسيع map التذاكر — pending_client / pending_staff /
        // waiting_customer / waiting_team / resolved + fallback آمن لا يكشف raw key.
        $map = [
            // Compliance/document statuses
            'expired'           => 'منتهية',
            'valid'             => 'سارية',
            'no_expiry'         => 'بدون تاريخ انتهاء',
            'warning'           => 'تنتهي قريبًا',
            'active'            => 'فعّالة',
            'pending'           => 'قيد المراجعة',
            'new'               => 'جديد',
            'awaiting_documents' => 'بانتظار المستندات',
            'waiting_documents'  => 'بانتظار المستندات',
            'ready_for_review'   => 'جاهز للمراجعة',
            'in_review'          => 'قيد مراجعة الفريق',
            'draft'             => 'مسودة',
            'warning'           => 'قريبة من الانتهاء',
            'completed'         => 'مكتمل',
            'approved'          => 'معتمد',
            'closed'            => 'مغلق',
            'rejected'          => 'مرفوض',
            // Ticket statuses (Phase 8B)
            'open'              => 'مفتوحة',
            'resolved'          => 'مكتملة',
            'pending_client'    => 'بانتظار العميل',
            'pending_customer'  => 'بانتظار العميل',
            'pending_staff'     => 'بانتظار الموظف',
            'pending_agent'     => 'بانتظار الموظف',
            'waiting_customer'  => 'بانتظار العميل',
            'waiting_team'      => 'بانتظار فريق العمل',
        ];

        if (isset($map[$key])) {
            return $map[$key];
        }

        // محاولة الترجمة من lang/*.json بمفتاح status_*
        $translated = __('status_' . $key);
        if ($translated !== 'status_' . $key) {
            return $translated;
        }

        // Phase 8B: fallback آمن — لا تكشف raw key للمستخدم.
        return __('status_processing') ?: 'قيد المعالجة';
    }

    /**
     * يترجم نوع الوثيقة لاسم عربي مقروء.
     */
    public function translateDocumentType(?string $type): string
    {
        $key = strtolower(trim((string) $type));
        $map = [
            'cr'                      => 'سجل تجاري',
            'commercial_register'     => 'سجل تجاري',
            'commercial_register_certificate' => 'سجل تجاري',
            'tax'                     => 'زكاة وضريبة',
            'gosi'                    => 'شهادة التأمينات',
            'medical_insurance'       => 'وثيقة التأمين الطبي',
            'national_address'        => 'العنوان الوطني',
            'articles_of_association' => 'عقد التأسيس',
            'saudization'             => 'شهادة سعودة',
            'invoice'                 => 'فاتورة',
            'contract'                => 'عقد',
            'general'                 => 'عام',
            'other'                   => 'أخرى',
        ];

        return $map[$key] ?? ($key ? __($key) : 'أخرى');
    }

    /**
     * يحدّد حالة الوثيقة من expiry_date (لا يعتمد على عمود status فقط).
     * يرجع مفتاح canonical: expired | warning | valid.
     */
    public function documentStatusKey($document): string
    {
        $expiry = data_get($document, 'expiry_date');
        if (! $expiry) {
            // Documents the client explicitly marked as "no expiry"
            // (founding contracts, invoices, etc.) get a distinct status
            // — they should not be coloured the same as a date-validated
            // "سارية" document.
            return 'no_expiry';
        }

        $expiry = $expiry instanceof \Carbon\CarbonInterface
            ? $expiry
            : \Carbon\Carbon::parse($expiry);

        if ($expiry->isPast()) {
            return 'expired';
        }

        $days = (int) now()->diffInDays($expiry, false);
        if ($days <= 30) {
            return 'warning';
        }

        return 'valid';
    }

    public function documentStatusLabel($document): string
    {
        return $this->translateStatus($this->documentStatusKey($document));
    }

    /**
     * Tailwind classes للـ badge حسب حالة الوثيقة.
     */
    public function documentStatusBadgeClass($document): string
    {
        // Phase 3: ألوان أهدأ — bg-50 بدل bg-100 يطابق تعليمات "ألوان هادئة" في design system.
        return match ($this->documentStatusKey($document)) {
            'expired'   => 'bg-rose-50 text-rose-700 ring-1 ring-rose-100',
            'warning'   => 'bg-amber-50 text-amber-700 ring-1 ring-amber-100',
            'no_expiry' => 'bg-slate-50 text-slate-600 ring-1 ring-slate-100',
            default     => 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100',
        };
    }

    /**
     * تنسيق تاريخ بالعربي مع كلمة "تنتهي في"/"انتهت في".
     */
    public function documentExpiryHuman($document): string
    {
        $expiry = data_get($document, 'expiry_date');
        if (! $expiry) {
            return 'لا تنتهي';
        }

        $expiry = $expiry instanceof \Carbon\CarbonInterface
            ? $expiry
            : \Carbon\Carbon::parse($expiry);

        $months = ['يناير','فبراير','مارس','أبريل','مايو','يونيو','يوليو','أغسطس','سبتمبر','أكتوبر','نوفمبر','ديسمبر'];
        $formatted = $expiry->day . ' ' . $months[$expiry->month - 1] . ' ' . $expiry->year;
        $prefix = $expiry->isPast() ? 'انتهت في' : 'تنتهي في';

        return $prefix . ': ' . $formatted;
    }

    public function switchCompany($companyId)
    {
        $user = Auth::user();
        if (! $user->companies()->whereKey((int) $companyId)->exists()) return;

        // Keep the company_user pivot's is_active state in sync with the chosen
        // company, mirroring CompanySelectionController::activateCompanyInDb so
        // that membership checks gated on wherePivot('is_active', true) keep
        // working after a Dashboard-initiated switch.
        DB::transaction(function () use ($user, $companyId) {
            DB::table('company_user')
                ->where('user_id', $user->id)
                ->update([
                    'is_active'  => false,
                    'updated_at' => now(),
                ]);

            $updated = DB::table('company_user')
                ->where('user_id', $user->id)
                ->where('company_id', (int) $companyId)
                ->update([
                    'is_active'  => true,
                    'updated_at' => now(),
                ]);

            if ($updated === 0) {
                throw new \RuntimeException('تعذر تفعيل المنشأة المحددة لهذا المستخدم.');
            }
        });

        session(['active_company_id' => (int) $companyId]);
        $this->activeCompanyId = (int) $companyId;

        $this->reset(['selectedTicketId', 'section', 'search']);
        $this->section = 'files';

        $this->loadCompanyData();

        $this->dispatch('notify', type: 'success', message: __('msg_saved'));
    }

    public function saveCompanyInfo()
    {
        $this->assertCompanyAdmin();

        $this->validate([
            'name' => 'required|string|max:255',
            'unified_number' => ['nullable', 'string', 'max:50', 'regex:/^[0-9]+$/'],
            'tax_number' => 'nullable|string|max:50',
            'city' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:255',
        ], [
            'name.required' => 'اسم المنشأة مطلوب.',
            'unified_number.regex' => 'الرقم الموحد يجب أن يحتوي على أرقام فقط.',
            'unified_number.max' => 'الرقم الموحد لا يجب أن يتجاوز 50 رقمًا.',
        ]);

        $this->activeCompany->update([
            'name' => $this->name,
            'unified_number' => $this->unified_number,
            'tax_number' => $this->tax_number,
            'city' => $this->city,
            'address' => $this->address,
        ]);

        $this->dispatch('close-modal', 'edit-company');
        $this->dispatch('notify', type: 'success', message: __('msg_saved'));
    }

    public function addUserToCompany()
    {
        $this->assertCompanyAdmin();

        $this->validate([
            'newUserEmail' => 'required|email|exists:users,email',
            'newUserRole' => 'required|in:admin,employee', // P1.2: توحيد الأدوار
        ], [
            'newUserEmail.required' => 'يرجى إدخال بريد المستخدم.',
            'newUserEmail.email' => 'يرجى إدخال بريد إلكتروني صحيح.',
            'newUserEmail.exists' => 'هذا البريد غير مسجل بعد. أضف المستخدم من مسار الدعوات عند تفعيله.',
            'newUserRole.required' => 'يرجى اختيار صلاحية المستخدم.',
        ]);

        $user = User::where('email', $this->newUserEmail)->first();

        if ($this->activeCompany->users()->where('user_id', $user->id)->exists()) {
            $this->addError('newUserEmail', __('User already exists in this company'));
            return;
        }

        $this->activeCompany->users()->attach($user->id, ['role' => $this->newUserRole]);

        $this->reset(['newUserEmail', 'newUserRole']);
        $this->newUserRole = 'employee'; // P1.2: توحيد الأدوار

        $this->dispatch('close-modal', 'add-user');
        $this->dispatch('notify', type: 'success', message: __('msg_saved'));
    }

    public function removeUser($userId)
    {
        $this->assertCompanyAdmin();

        if (Auth::id() == $userId) {
            return;
        }

        // Prevent leaving the company with no active admin (lockout protection)
        $targetIsActiveAdmin = $this->activeCompany->users()
            ->where('users.id', (int) $userId)
            ->wherePivot('role', 'admin')
            ->wherePivot('is_active', true)
            ->exists();

        if ($targetIsActiveAdmin) {
            $activeAdminCount = $this->activeCompany->users()
                ->wherePivot('role', 'admin')
                ->wherePivot('is_active', true)
                ->count();

            if ($activeAdminCount <= 1) {
                $this->dispatch(
                    'notify',
                    type: 'error',
                    message: 'لا يمكن إزالة آخر مسؤول نشط للشركة.'
                );
                return;
            }
        }

        $this->activeCompany->users()->detach($userId);
        $this->dispatch('notify', type: 'success', message: __('msg_saved'));
    }

    /* =========================================================
       Phase A — team invitations
    ========================================================= */

    /**
     * Pending invitations for the active company. Used by the users
     * section to render the "دعوات معلقة" list.
     */
    public function getPendingCompanyInvitationsProperty()
    {
        if (! $this->activeCompanyId
            || ! \Illuminate\Support\Facades\Schema::hasTable('company_invitations')) {
            return collect();
        }

        return CompanyInvitation::query()
            ->with('inviter:id,name,email')
            ->where('company_id', $this->activeCompanyId)
            ->whereNull('accepted_at')
            ->whereNull('revoked_at')
            ->orderByDesc('id')
            ->limit(50)
            ->get();
    }

    /**
     * Create a new invitation for the given email. Returns a plain
     * acceptance URL via $this->lastInvitationLink so the admin can
     * copy it. Idempotency: a pending row for the same (company, email)
     * is blocked; the admin must revoke first.
     */
    public function sendCompanyInvitation(): void
    {
        $this->assertCompanyAdmin();

        $this->validate([
            'inviteEmail' => 'required|email|max:191',
            'inviteRole'  => 'required|in:admin,employee',
        ], [
            'inviteEmail.required' => 'يرجى إدخال البريد الإلكتروني.',
            'inviteEmail.email'    => 'يرجى إدخال بريد إلكتروني صحيح.',
            'inviteRole.required'  => 'يرجى اختيار الدور.',
            'inviteRole.in'        => 'الدور غير صالح.',
        ]);

        $normalizedEmail = strtolower(trim($this->inviteEmail));

        // Block duplicate pending invitations for the same recipient.
        $duplicate = CompanyInvitation::query()
            ->where('company_id', $this->activeCompanyId)
            ->whereRaw('LOWER(email) = ?', [$normalizedEmail])
            ->whereNull('accepted_at')
            ->whereNull('revoked_at')
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->exists();

        if ($duplicate) {
            $this->addError('inviteEmail', 'هذا البريد لديه دعوة معلقة بالفعل لهذه المنشأة.');
            return;
        }

        // Block re-inviting someone who is already an active member.
        $alreadyMember = User::query()
            ->whereRaw('LOWER(email) = ?', [$normalizedEmail])
            ->whereHas('companies', function ($q) {
                $q->where('companies.id', $this->activeCompanyId);
            })
            ->exists();

        if ($alreadyMember) {
            $this->addError('inviteEmail', 'هذا المستخدم عضو في المنشأة بالفعل.');
            return;
        }

        $plainToken = CompanyInvitation::mintToken();
        $invitation = null;

        try {
            $invitation = CompanyInvitation::create([
                'company_id'  => $this->activeCompanyId,
                'email'       => $normalizedEmail,
                'role'        => $this->inviteRole,
                'token_hash'  => CompanyInvitation::hashToken($plainToken),
                'invited_by'  => Auth::id(),
                'expires_at'  => now()->addDays(7),
            ]);
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('notify', type: 'error', message: 'تعذر إنشاء الدعوة. حاول مرة أخرى.');
            return;
        }

        $this->lastInvitationLink = $invitation->acceptanceUrl($plainToken);

        $this->reset(['inviteEmail']);
        $this->inviteRole = 'employee';

        $this->dispatch('notify', type: 'success', message: 'تم إنشاء الدعوة. انسخ الرابط للمستخدم.');
    }

    /**
     * Revoke a pending invitation. Already-accepted rows can't be
     * revoked (the user is a member; use removeUser instead).
     */
    public function revokeCompanyInvitation(int $invitationId): void
    {
        $this->assertCompanyAdmin();

        $invitation = CompanyInvitation::query()
            ->where('company_id', $this->activeCompanyId)
            ->whereKey($invitationId)
            ->first();

        if (! $invitation || $invitation->isRevoked() || $invitation->isAccepted()) {
            $this->dispatch('notify', type: 'error', message: 'الدعوة غير قابلة للإلغاء.');
            return;
        }

        $invitation->update(['revoked_at' => now()]);

        if ($this->lastInvitationLink) {
            $this->lastInvitationLink = null;
        }

        $this->dispatch('notify', type: 'success', message: 'تم إلغاء الدعوة.');
    }

    /**
     * Mint a fresh token + extend expiry by 7 days. The new plain URL
     * is surfaced via $this->lastInvitationLink. The old hash is
     * overwritten so any leaked URL stops working immediately.
     */
    public function regenerateCompanyInvitation(int $invitationId): void
    {
        $this->assertCompanyAdmin();

        $invitation = CompanyInvitation::query()
            ->where('company_id', $this->activeCompanyId)
            ->whereKey($invitationId)
            ->first();

        if (! $invitation || $invitation->isAccepted()) {
            $this->dispatch('notify', type: 'error', message: 'الدعوة غير قابلة لإعادة الإنشاء.');
            return;
        }

        $plainToken = CompanyInvitation::mintToken();

        $invitation->update([
            'token_hash'  => CompanyInvitation::hashToken($plainToken),
            'expires_at'  => now()->addDays(7),
            'revoked_at'  => null,
        ]);

        $this->lastInvitationLink = $invitation->acceptanceUrl($plainToken);

        $this->dispatch('notify', type: 'success', message: 'تم تجديد الدعوة. انسخ الرابط الجديد.');
    }

    /**
     * Hide the freshly-minted link from the UI. Called when the admin
     * clicks "تم نسخه" / dismiss so the plain URL doesn't linger on
     * screen after they've grabbed it.
     */
    public function dismissInvitationLink(): void
    {
        $this->lastInvitationLink = null;
    }

    /* =========================================================
       Phase B (UI) — permissions matrix editor
       --------------------------------------------------------
       The static service `App\Support\CompanyPermissions` decides
       what's granted. These methods are thin wrappers that load
       the pivot, surface the in-memory matrix to the modal, mutate
       it via togglePermission(), and persist it on save.
    ========================================================= */

    /**
     * The user currently being edited in the matrix modal. Scoped to
     * the active company so a tampered $permissionsUserId can never
     * reach into another tenant's roster.
     */
    public function getPermissionsTargetUserProperty(): ?User
    {
        if (! $this->permissionsUserId || ! $this->activeCompanyId) {
            return null;
        }

        return User::query()
            ->whereKey((int) $this->permissionsUserId)
            ->whereHas('companies', function ($q) {
                $q->where('companies.id', $this->activeCompanyId);
            })
            ->first();
    }

    /**
     * Open the matrix editor for a specific team member. admin/owner
     * rows are intentionally rejected here — their access is granted
     * by role, and the matrix modal would mislead. Employees load the
     * stored JSON (or EMPLOYEE_DEFAULTS for null pivots).
     */
    public function openPermissionsManager(int $userId): void
    {
        $this->assertCompanyAdmin();

        $pivot = $this->activeCompany?->users()
            ->where('users.id', $userId)
            ->first()?->pivot;

        if (! $pivot) {
            $this->dispatch('notify', type: 'error', message: 'المستخدم غير موجود في هذه المنشأة.');
            return;
        }

        $role = strtolower((string) ($pivot->role ?? ''));
        if (in_array($role, ['admin', 'owner'], true)) {
            $this->dispatch('notify', type: 'info', message: 'صلاحيات مسؤولي المنشأة كاملة دائمًا — لا يمكن تعديلها.');
            return;
        }

        $this->permissionsUserId = $userId;
        $this->permissionsMatrix = \App\Support\CompanyPermissions::effective(
            $pivot->role ?? null,
            $pivot->permissions ?? null,
        );

        $this->dispatch('open-modal', 'permissions-manager');
    }

    /**
     * Toggle a single (group, action) cell in the draft matrix.
     * Nothing is persisted here — the admin must click "Save".
     */
    public function togglePermission(string $group, string $action): void
    {
        if (! in_array($group, \App\Support\CompanyPermissions::GROUPS, true)) {
            return;
        }
        if (! in_array($action, \App\Support\CompanyPermissions::ACTIONS, true)) {
            return;
        }

        $existing = $this->permissionsMatrix[$group] ?? [];
        if (! is_array($existing)) {
            $existing = [];
        }

        if (in_array($action, $existing, true)) {
            $existing = array_values(array_diff($existing, [$action]));
        } else {
            $existing[] = $action;
        }

        if (empty($existing)) {
            unset($this->permissionsMatrix[$group]);
        } else {
            $this->permissionsMatrix[$group] = array_values(array_unique($existing));
        }
    }

    /**
     * Persist the in-memory matrix back to the company_user pivot.
     * normalize() drops unknown groups/actions so a manipulated
     * client payload can never widen the matrix beyond the defined
     * GROUPS/ACTIONS vocabulary. Empty matrix → store null so a future
     * EMPLOYEE_DEFAULTS shift is picked up automatically.
     */
    public function saveUserPermissions(): void
    {
        $this->assertCompanyAdmin();

        $target = $this->permissionsTargetUser;
        if (! $target) {
            $this->dispatch('notify', type: 'error', message: 'المستخدم غير موجود.');
            return;
        }

        $clean = \App\Support\CompanyPermissions::normalize($this->permissionsMatrix);
        $payload = empty($clean) ? null : json_encode($clean, JSON_UNESCAPED_UNICODE);

        try {
            DB::table('company_user')
                ->where('company_id', $this->activeCompanyId)
                ->where('user_id', $target->id)
                ->update([
                    'permissions' => $payload,
                    'updated_at'  => now(),
                ]);
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('notify', type: 'error', message: 'تعذر حفظ الصلاحيات. حاول مرة أخرى.');
            return;
        }

        // Keep the modal in sync with what just landed in DB so a quick
        // re-open shows the new state without an extra round-trip.
        $this->permissionsMatrix = $clean;

        $this->dispatch('close-modal', 'permissions-manager');
        $this->dispatch('notify', type: 'success', message: 'تم حفظ الصلاحيات.');
    }

    /**
     * Reset the targeted user's permissions to the role-based
     * default (employee → EMPLOYEE_DEFAULTS). Writes null to the
     * pivot so future default-set changes are picked up automatically.
     */
    public function resetUserPermissionsToDefault(): void
    {
        $this->assertCompanyAdmin();

        $target = $this->permissionsTargetUser;
        if (! $target) {
            return;
        }

        try {
            DB::table('company_user')
                ->where('company_id', $this->activeCompanyId)
                ->where('user_id', $target->id)
                ->update([
                    'permissions' => null,
                    'updated_at'  => now(),
                ]);
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('notify', type: 'error', message: 'تعذر إعادة التعيين.');
            return;
        }

        $this->permissionsMatrix = \App\Support\CompanyPermissions::EMPLOYEE_DEFAULTS;

        $this->dispatch('notify', type: 'success', message: 'تمت إعادة الصلاحيات إلى الافتراضي.');
    }

    /**
     * Close the matrix modal and drop the in-memory draft. Used by
     * the modal's close/cancel buttons and after a successful save.
     */
    public function closePermissionsManager(): void
    {
        $this->permissionsUserId = null;
        $this->permissionsMatrix = [];
        $this->dispatch('close-modal', 'permissions-manager');
    }

    /**
     * Phase 4: أنواع الوثائق الرسمية التي يجب أن يكون لها تاريخ انتهاء
     * (هذه وثائق امتثال حكومية لها صلاحية محدودة).
     * invoice/contract/national_address/articles_of_association/other → اختياري.
     */
    protected const EXPIRY_REQUIRED_TYPES = [
        'cr',
        'commercial_register',
        'commercial_register_certificate',
        'tax',
        'gosi',
        'medical_insurance',
    ];

    public function saveComplianceDoc()
    {
        $this->assertCompanyMember();

        // Phase 4: validation شرطي للتاريخ — مطلوب فقط للأنواع الرسمية أعلاه.
        $expiryRule = in_array((string) $this->docType, self::EXPIRY_REQUIRED_TYPES, true)
            ? 'required|date'
            : 'nullable|date';

        $this->validate([
            'docType'      => 'required|string|max:100',
            'docNumber'    => 'nullable|string|max:100',
            'docIssuer'    => 'nullable|string|max:191',
            'docIssueDate' => 'nullable|date',
            'docExpiry'    => $expiryRule,
            'docNotes'     => 'nullable|string|max:1000',
            'docFile'      => 'required|file|max:10240|mimes:pdf,jpg,jpeg,png',
        ], [
            'docExpiry.required' => 'تاريخ الانتهاء مطلوب لهذا النوع من الوثائق.',
            'docFile.required'   => 'يرجى اختيار ملف الوثيقة.',
            'docFile.mimes'      => 'يجب أن يكون الملف من نوع PDF أو JPG أو PNG.',
            'docFile.max'        => 'حجم الملف يتجاوز الحد المسموح (10 ميجابايت).',
        ]);

        $createdDocument = null;

        try {
            $path = $this->docFile->store("companies/{$this->activeCompanyId}/documents", 'private');

            $createdDocument = CompanyDocument::create([
                'company_id'      => $this->activeCompanyId,
                'type'            => $this->docType,
                'document_number' => $this->docNumber,
                'issuer'          => $this->docIssuer,
                'issue_date'      => $this->docIssueDate,
                'expiry_date'     => $this->docExpiry,
                'notes'           => $this->docNotes,
                'file_path'       => $path,
            ]);
        } catch (\Throwable $e) {
            $this->dispatch('notify', type: 'error', message: 'تعذر حفظ المستند. حاول مرة أخرى.');
            return;
        }

        $this->reset(['docType', 'docNumber', 'docIssuer', 'docIssueDate', 'docExpiry', 'docNotes', 'docFile']);
        $this->docType = 'cr';

        // Stage 3: حدث مخصّص يُغلق المودال على نجاح الحفظ فقط (لا أثناء temp upload).
        $this->dispatch('compliance-document-created');
        $this->dispatch('close-modal', 'add-doc');

        // AI extraction is now manual by default. The client clicks an
        // explicit "تحليل بالذكاء الاصطناعي" button (or backoffice does)
        // via requestAiExtraction below. Auto-queue only fires when the
        // operator opts in via AI_AUTO_EXTRACT_ON_UPLOAD=true.
        $aiQueued = false;
        $aiEnabled = (bool) config('ai.enabled', false);
        $autoExtract = (bool) config('ai.auto_extract_on_upload', false);

        if ($autoExtract) {
            try {
                if ($createdDocument && $this->isAiExtractionSupported($createdDocument->type)) {
                    $aiQueued = $this->autoQueueExtractionFor($createdDocument);
                }
            } catch (\Throwable $e) {
                // لا نكسر تجربة الرفع — فقط نسجّل صامتًا
                $aiQueued = false;
            }
        }

        if ($aiQueued) {
            $this->dispatch(
                'notify',
                type: 'success',
                message: $aiEnabled
                    ? 'تم رفع الوثيقة وبدأ تحليلها تلقائيًا. سنعرض البيانات المستخرَجة للمراجعة.'
                    : 'تم رفع الوثيقة. التحليل الذكي غير مفعّل حاليًا — يمكنك تعبئة البيانات يدويًا.'
            );
        } elseif ($createdDocument && $this->isAiExtractionSupported($createdDocument->type) && ! $aiEnabled) {
            $this->dispatch(
                'notify',
                type: 'success',
                message: 'تم رفع الملف. التحليل الذكي غير مفعّل حاليًا — يمكنك تعبئة البيانات يدويًا.'
            );
        } else {
            $this->dispatch('notify', type: 'success', message: __('msg_saved'));
        }
    }

    /**
     * Auto-queue helper — يمنع التكرار للوثيقة نفسها.
     * يرجع true لو أُنشئ extraction جديد (أو وُجد بانتظار)، false لو فشل.
     */
    protected function autoQueueExtractionFor(CompanyDocument $doc): bool
    {
        try {
            if (! class_exists(\App\Models\AiDocumentExtraction::class)) {
                return false;
            }

            // امنع التكرار: إن وُجد extraction نشط للوثيقة نفسها
            $hasActive = \App\Models\AiDocumentExtraction::query()
                ->where('document_id', $doc->id)
                ->whereIn('status', [
                    \App\Models\AiDocumentExtraction::STATUS_PENDING,
                    \App\Models\AiDocumentExtraction::STATUS_PROCESSING,
                    \App\Models\AiDocumentExtraction::STATUS_READY_FOR_REVIEW,
                ])
                ->exists();

            if ($hasActive) {
                return true; // نعتبره مُجدوَلًا (مرئي للمستخدم في AI Review)
            }

            /** @var \App\Services\Ai\DocumentExtractionService $service */
            $service = app(\App\Services\Ai\DocumentExtractionService::class);
            $extraction = $service->queue($doc, (int) (\Illuminate\Support\Facades\Auth::id() ?? 0));

            \App\Jobs\ProcessCompanyDocumentExtraction::dispatch($extraction->id);

            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * batch helper — يرجع آخر حالة extraction لكل document_id.
     * يستخدم DB::table لتجنّب class_exists model و autoload errors.
     */
    public function extractionStatusesFor(array $documentIds): array
    {
        if (empty($documentIds)) return [];

        try {
            if (! \Illuminate\Support\Facades\Schema::hasTable('ai_document_extractions')) {
                return [];
            }
            $rows = \Illuminate\Support\Facades\DB::table('ai_document_extractions')
                ->whereIn('document_id', $documentIds)
                ->orderBy('document_id')
                ->orderByDesc('id')
                ->get(['document_id', 'status']);

            $latest = [];
            foreach ($rows as $r) {
                if (! isset($latest[$r->document_id])) {
                    $latest[$r->document_id] = (string) $r->status;
                }
            }
            return $latest;
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * ترجمة حالة الاستخراج لعربي مع badge class.
     */
    public function extractionStatusInfo(?string $status): array
    {
        // Phase 3: نصوص بصرية أصدق للحالة — "يحتاج إعادة رفع" بدل "فشل التحليل" (نفس المعنى للمستخدم).
        return match ((string) $status) {
            'pending'           => ['label' => 'بانتظار التحليل',  'class' => 'bg-slate-50 text-slate-600 border border-slate-100',     'icon' => 'fas fa-clock'],
            'processing'        => ['label' => 'جاري التحليل',     'class' => 'bg-amber-50 text-amber-700 border border-amber-100',     'icon' => 'fas fa-circle-notch fa-spin'],
            'ready_for_review'  => ['label' => 'بانتظار المراجعة', 'class' => 'bg-[#0A2540]/5 text-[#0A2540] border border-[#0A2540]/15', 'icon' => 'fas fa-eye'],
            'approved'          => ['label' => 'تم الاعتماد',      'class' => 'bg-emerald-50 text-emerald-700 border border-emerald-100','icon' => 'fas fa-circle-check'],
            'rejected'          => ['label' => 'مرفوضة',            'class' => 'bg-rose-50 text-rose-700 border border-rose-100',        'icon' => 'fas fa-circle-xmark'],
            'failed'            => ['label' => 'يحتاج إعادة رفع',   'class' => 'bg-rose-50 text-rose-700 border border-rose-100',        'icon' => 'fas fa-triangle-exclamation'],
            default             => ['label' => '',                  'class' => '', 'icon' => ''],
        };
    }

    /**
     * ترجمة pivot role / display role للعربي.
     */
    public function translateRole(?string $role): string
    {
        return match (strtolower((string) $role)) {
            'admin'      => 'مدير',
            'owner'      => 'مالك',
            'employee'   => 'موظف',
            'user'       => 'موظف',
            'manager'    => 'مدير قسم',
            'support'    => 'دعم فني',
            'super_admin' => 'مدير عام',
            'customer'   => 'عميل',
            default      => $role ? (string) $role : '—',
        };
    }

    /**
     * ترجمة مفاتيح AI extraction fields (الـ schema الموحَّد).
     */
    public function translateAiFieldKey(string $key): string
    {
        return match ($key) {
            'company_name'                    => 'اسم المنشأة',
            'commercial_registration_number'  => 'رقم السجل التجاري',
            'unified_number'                  => 'الرقم الموحد 700',
            'tax_number'                      => 'الرقم الضريبي',
            'city'                            => 'المدينة',
            'business_activity'               => 'نشاط المنشأة',
            'cr_issued_at'                    => 'تاريخ إصدار السجل',
            'cr_expires_at'                   => 'تاريخ انتهاء السجل',
            'gosi_subscription_number'        => 'رقم اشتراك التأمينات',
            'medical_insurance_company'       => 'شركة التأمين الطبي',
            'medical_insurance_policy_number' => 'رقم وثيقة التأمين الطبي',
            'medical_insurance_starts_at'     => 'بداية التأمين الطبي',
            'medical_insurance_ends_at'       => 'نهاية التأمين الطبي',
            default                           => $key,
        };
    }

    public function deleteDocument($id)
    {
        $this->assertCanManageComplianceDocuments();

        $doc = CompanyDocument::where('company_id', $this->activeCompanyId)->findOrFail((int) $id);
        $doc->delete();
        $this->dispatch('notify', type: 'success', message: __('msg_saved'));
    }

    /* =============================================================
       Phase E — AI extraction (Manual trigger only — لا Observer)
    ============================================================= */

    /**
     * يحدد إن كان نوع الوثيقة مدعومًا للتحليل التلقائي.
     */
    public function isAiExtractionSupported(?string $type): bool
    {
        if (! $type) {
            return false;
        }

        $supported = config('ai.supported_document_types', []);
        return in_array((string) $type, $supported, true);
    }

    /**
     * يحدد إن كان التحليل الذكي مفعّلًا عبر إعدادات البيئة.
     * يقرأ من config('ai.enabled') فقط — لا اتصال خارجي ولا fallback إلى n8n.
     */
    public function isAiExtractionEnabled(): bool
    {
        return (bool) config('ai.enabled', false);
    }

    /**
     * إعادة تحليل وثيقة (للـ backoffice فقط).
     * تُستدعى يدويًا لإعادة المحاولة عندما يفشل التحليل التلقائي.
     * لم تعد تُعرض كزر أساسي على الكرت — فقط زر ثانوي صغير يظهر عندما status=failed.
     */
    public function requestAiExtraction(int $documentId): void
    {
        // assertCompanyMember + the company-scoped document query below
        // are the source of truth for ownership: a user can only trigger
        // extraction on a document that belongs to the company they're an
        // active member of. The previous backoffice-only restriction is
        // dropped so customers can run AI on their own documents.
        $this->assertCompanyMember();

        $user = \Illuminate\Support\Facades\Auth::user();
        abort_unless($user, 403);

        $doc = CompanyDocument::where('company_id', $this->activeCompanyId)->find($documentId);
        if (! $doc) {
            $this->dispatch('notify', type: 'error', message: 'الوثيقة غير موجودة.');
            return;
        }

        if (! $this->isAiExtractionSupported($doc->type)) {
            $this->dispatch('notify', type: 'error', message: 'نوع الوثيقة غير مدعوم للتحليل التلقائي.');
            return;
        }

        $queued = $this->autoQueueExtractionFor($doc);

        if ($queued) {
            $this->dispatch(
                'notify',
                type: 'success',
                message: 'تم بدء تحليل الوثيقة بالذكاء الاصطناعي. ستظهر النتيجة في تبويب "مراجعة الذكاء الاصطناعي".'
            );
        } else {
            $this->dispatch('notify', type: 'error', message: 'تعذّر بدء التحليل. حاول مرة أخرى.');
        }
    }

    public function uploadFile()
    {
        $this->assertCompanyMember();

        $this->validate([
            'newFile' => 'required|file|max:20480|mimes:pdf,jpg,jpeg,png',
            'newFileTitle' => 'nullable|string|max:255',
            'newFileCategory' => 'nullable|string|max:100',
        ], [
            'newFile.required' => 'يرجى اختيار ملف.',
            'newFile.mimes'    => 'يجب أن يكون الملف من نوع PDF أو JPG أو PNG.',
            'newFile.max'      => 'حجم الملف يتجاوز الحد المسموح (20 ميجابايت).',
        ]);

        try {
            $path = $this->newFile->store("companies/{$this->activeCompanyId}/portal-files", 'private');

            CompanyFile::create([
                'company_id'    => $this->activeCompanyId,
                'uploaded_by'   => Auth::id(),
                'title'         => trim($this->newFileTitle) ?: null,
                'category'      => trim($this->newFileCategory) ?: null,
                'disk'          => 'private',
                'path'          => $path,
                'original_name' => $this->newFile->getClientOriginalName(),
                'mime'          => $this->newFile->getMimeType(),
                'size'          => $this->newFile->getSize(),
                'is_public'     => false,
            ]);
        } catch (\Throwable $e) {
            $this->dispatch('notify', type: 'error', message: 'تعذر رفع الملف. حاول مرة أخرى.');
            return;
        }

        $this->reset('newFile', 'newFileTitle');
        $this->dispatch('notify', type: 'success', message: 'تم رفع الملف بنجاح.');
    }

    public function clearNewFile(): void
    {
        $this->reset('newFile');
        $this->resetValidation('newFile');
    }

    public function createServiceRequest(): void
    {
        $this->assertCompanyMember();

        // Double-submit guard — the wizard Step 3 button can be clicked twice
        // while the request is in flight; without this, two rows are created.
        if ($this->createdServiceRequestId) {
            return;
        }

        $this->validate([
            'serviceRequestServiceId'     => 'required|integer|exists:services,id',
            'serviceRequestNotes'         => 'required|string|min:5|max:5000',
            // Phase 3: re-validate attachments before commit (Step 2's
            // validation may have been bypassed via deep link).
            'serviceRequestAttachments.*' => 'nullable|file|max:10240|mimes:pdf,jpg,jpeg,png,doc,docx',
        ], [
            'serviceRequestServiceId.required' => 'يرجى اختيار الخدمة.',
            'serviceRequestServiceId.exists' => 'الخدمة المختارة غير متاحة.',
            'serviceRequestNotes.required' => 'يرجى كتابة تفاصيل الطلب.',
            'serviceRequestNotes.min' => 'تفاصيل الطلب قصيرة جدًا.',
            'serviceRequestAttachments.*.mimes' => 'يجب أن تكون المرفقات من نوع PDF أو صورة أو مستند Word.',
            'serviceRequestAttachments.*.max'   => 'حجم أحد المرفقات يتجاوز الحد المسموح (10 ميجابايت).',
        ]);

        $service = Service::query()
            ->whereKey((int) $this->serviceRequestServiceId)
            ->where('is_active', true)
            ->first();

        if (! $service) {
            $this->addError('serviceRequestServiceId', 'الخدمة المختارة غير متاحة.');
            return;
        }

        $user = Auth::user();
        $company = $this->activeCompany;

        $request = ServiceRequest::create([
            'user_id'            => $user?->id,
            'company_id'         => $this->activeCompanyId,
            'service_id'         => $service->id,
            'payment_method'     => 'bank_transfer',
            'status'             => 'new',
            'name'               => $user?->name,
            'email'              => $user?->email,
            'phone'              => $user?->mobile ?: '-',
            'description'        => trim($this->serviceRequestNotes),
            'applicant_type'     => 'company',
            'establishment_name' => $company?->name,
            'cr_number'          => $company?->cr_number,
        ]);

        // Phase 3: persist optional wizard attachments polymorphically.
        // Failures here are non-fatal — the request itself is already
        // created; we just notify the user and let them re-upload through
        // a follow-up channel.
        if (! empty($this->serviceRequestAttachments)) {
            $savedAttachments = 0;
            foreach ($this->serviceRequestAttachments as $file) {
                if (! $file) {
                    continue;
                }
                try {
                    $path = $file->store("service-requests/{$request->id}", 'private');

                    Attachment::create([
                        'attachable_type' => ServiceRequest::class,
                        'attachable_id'   => $request->id,
                        'company_id'      => $this->activeCompanyId,
                        'user_id'         => $user?->id,
                        'uploaded_by'     => $user?->id,
                        'disk'            => 'private',
                        'path'            => $path,
                        'file_path'       => $path,
                        'original_name'   => $file->getClientOriginalName(),
                        'mime'            => $file->getMimeType(),
                        'size'            => $file->getSize(),
                        'visibility'      => 'private',
                    ]);
                    $savedAttachments++;
                } catch (\Throwable $e) {
                    report($e);
                }
            }

            if ($savedAttachments > 0 && $savedAttachments < count($this->serviceRequestAttachments)) {
                $this->dispatch(
                    'notify',
                    type: 'warning',
                    message: 'تم حفظ الطلب لكن تعذر رفع بعض المرفقات. يمكنك إعادة رفعها من تفاصيل الطلب لاحقًا.'
                );
            }
        }

        $this->createdServiceRequestId = $request->id;
        $this->serviceRequestStep = 4;
        $this->section = 'requests';
        $this->serviceRequestAttachments = [];

        $this->dispatch('notify', type: 'success', message: 'تم إنشاء الطلب بنجاح.');
    }

    /**
     * Phase 3: collection of attachments belonging to the request currently
     * opened in the request-history details panel.
     */
    public function getSelectedServiceRequestAttachmentsProperty()
    {
        if (! $this->selectedServiceRequestId || ! $this->activeCompanyId) {
            return collect();
        }

        // Ownership is enforced by selectedServiceRequest accessor which scopes
        // to company + user; re-scope by company_id here as defence-in-depth.
        return Attachment::query()
            ->where('attachable_type', ServiceRequest::class)
            ->where('attachable_id', (int) $this->selectedServiceRequestId)
            ->where('company_id', $this->activeCompanyId)
            ->latest()
            ->get();
    }

    public function deleteFile($id)
    {
        $this->assertCompanyMember();

        $file = CompanyFile::where('company_id', $this->activeCompanyId)->findOrFail((int) $id);
        Storage::disk($file->disk ?: 'private')->delete($file->path);
        $file->delete();
        $this->dispatch('notify', type: 'success', message: __('msg_saved'));
    }

    public function downloadFile($id)
    {
        $this->assertCompanyMember();

        $file = CompanyFile::where('company_id', $this->activeCompanyId)->find($id);

        if ($file) {
            return Storage::disk($file->disk ?: 'private')->download($file->path, $file->original_name);
        }

        $this->dispatch('notify', type: 'error', message: 'File not found');
    }

    // تم تغيير اسم الدالة من selectTicket إلى openTicket لتتطابق مع الـ Blade
    public function openTicket($id)
    {
        $this->selectedTicketId = $id;
        $this->dispatch('ticket-updated'); // لتشغيل الـ Scroll التلقائي في الشات
    }

    public function createTicket()
    {
        $this->assertCompanyMember();

        // Phase 5 audit fix: mime whitelist لمرفقات التذاكر — لا executables.
        $this->validate([
            'newTicketSubject' => 'required|string|max:255',
            'newTicketMessage' => 'required|string',
            'newTicketAttachments.*' => 'nullable|file|max:10240|mimes:pdf,jpg,jpeg,png,doc,docx',
        ], [
            'newTicketSubject.required'    => 'يرجى كتابة عنوان التذكرة.',
            'newTicketMessage.required'    => 'يرجى كتابة تفاصيل التذكرة.',
            'newTicketAttachments.*.mimes' => 'يجب أن تكون المرفقات من نوع PDF أو صورة أو مستند Word.',
            'newTicketAttachments.*.max'   => 'حجم أحد المرفقات يتجاوز الحد المسموح (10 ميجابايت).',
        ]);

        $ticket = Ticket::create([
            'company_id' => $this->activeCompanyId,
            'user_id'    => Auth::id(),
            'subject'    => $this->newTicketSubject,
            'description'=> $this->newTicketMessage,
            'status'     => 'open'
        ]);

        if ($this->newTicketAttachments) {
            foreach ($this->newTicketAttachments as $file) {
                 $path = $file->store("tickets/{$ticket->id}", 'private');
                 $ticket->attachments()->create([
                     'original_name' => $file->getClientOriginalName(),
                     'path' => $path,
                     'size' => $file->getSize(),
                     'mime' => $file->getMimeType(),
                     'disk' => 'private'
                 ]);
            }
        }

        $this->reset(['newTicketSubject', 'newTicketMessage', 'newTicketAttachments']);
        $this->dispatch('close-modal', 'create-ticket');
        $this->dispatch('notify', type: 'success', message: __('msg_saved'));

        // ✅ إرسال إيميل تنبيه للإدارة
        // P1.6: نقل البريد من قيمة مكوَّدة إلى config('amr7.contact.email')
        // (التي تقرأ من env AMR7_SUPPORT_EMAIL مع fallback آمن).
        try {
            $adminEmail = (string) config('amr7.contact.email', 'info@amr-7.sa');
            $ticket->loadMissing(['company', 'user']);

            Mail::to($adminEmail)->send(new NewTicketAdminMail(
                ticket: $ticket,
                customer: Auth::user(),
                url: config('app.url') . '/amr7/tickets/' . $ticket->id,
            ));
        } catch (\Throwable $e) {}
    }

    public function closeTicket($id)
    {
        $this->assertCompanyMember();

        $ticket = Ticket::where('company_id', $this->activeCompanyId)->find($id);
        if ($ticket) {
            $ticket->update(['status' => 'closed']);
            $this->dispatch('notify', type: 'success', message: __('msg_saved'));
        }
    }

    public function sendReply()
    {
        // Phase 5 audit fix: mime whitelist لمرفقات ردود التذاكر — لا executables.
        $this->validate([
            'replyMessage' => 'required|string',
            'replyAttachments.*' => 'nullable|file|max:10240|mimes:pdf,jpg,jpeg,png,doc,docx',
        ], [
            'replyMessage.required'    => 'يرجى كتابة محتوى الرد.',
            'replyAttachments.*.mimes' => 'يجب أن تكون المرفقات من نوع PDF أو صورة أو مستند Word.',
            'replyAttachments.*.max'   => 'حجم أحد المرفقات يتجاوز الحد المسموح (10 ميجابايت).',
        ]);

        if (! $this->selectedTicketId) return;

        // Defense against $activeCompanyId tampering on the Livewire wire payload:
        // require that the authenticated user is an active member of the active company
        // before scoping the ticket lookup by it.
        $this->assertCompanyMember();

        $ticket = Ticket::where('company_id', $this->activeCompanyId)
            ->whereKey($this->selectedTicketId)
            ->first();

        if (! $ticket) {
            abort(404);
        }

        $reply = TicketReply::create([
            'ticket_id' => $ticket->id,
            'user_id'   => Auth::id(),
            'message'   => $this->replyMessage,
        ]);

        if ($this->replyAttachments) {
            foreach ($this->replyAttachments as $file) {
                 $path = $file->store("tickets/{$ticket->id}/replies", 'private');
                 $reply->attachments()->create([
                     'original_name' => $file->getClientOriginalName(),
                     'path' => $path,
                     'size' => $file->getSize(),
                     'mime' => $file->getMimeType(),
                     'disk' => 'private'
                 ]);
            }
        }

        $this->reset(['replyMessage', 'replyAttachments']);
        $this->dispatch('ticket-updated'); // لتشغيل الـ Scroll التلقائي للأسفل
    }

    private function setNoIndex(): void
    {
        if (class_exists(\Artesaos\SEOTools\Facades\SEOTools::class)) {
            try {
                \Artesaos\SEOTools\Facades\SEOTools::metatags()->addMeta('robots', 'noindex, nofollow', 'name');
            } catch (\Throwable $e) {}
        }
    }

    public function render()
    {
        return view('livewire.dashboard', [
            'selectedTicket' => $this->selectedTicket, 
        ])->layout('layouts.app');
    }
}
