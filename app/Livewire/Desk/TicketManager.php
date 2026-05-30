<?php

namespace App\Livewire\Desk;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\Ticket;
use App\Models\TicketReply;
use App\Models\User;
use Artesaos\SEOTools\Facades\SEOTools;
use Illuminate\Support\Facades\DB;

class TicketManager extends Component
{
    use WithPagination, WithFileUploads;

    public $filters = [
        'status' => 'all',
        'department' => 'all',
        'search' => '',
    ];

    public $selectedTicket = null;
    public $replyMessage = '';
    public $attachments = [];
    public $perPage = 10;

    // Whitelist of allowed attachment MIME types — blocks executable / web
    // delivery formats (php, js, sh, html, svg, exe) that get past
    // extension-only checks.
    protected const ALLOWED_ATTACHMENT_MIMES = [
        'application/pdf',
        'image/jpeg',
        'image/png',
        'image/webp',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    ];

    protected $rules = [
        'replyMessage' => 'required_without:attachments|max:2000',
        'attachments.*' => 'file|max:20480|mimes:pdf,jpg,jpeg,png,webp,doc,docx,xls,xlsx',
    ];

    protected $queryString = ['filters'];

    protected function companyId(): int
    {
        return (int) (auth()->user()?->companies()?->first()?->id ?? 0);
    }

    public function mount()
    {
        SEOTools::setTitle(__('Ticket Management - Technical Support'));
        SEOTools::metatags()->addMeta('robots', 'noindex, nofollow');
    }

    public function render()
    {
        $cid = $this->companyId();
        abort_if($cid === 0, 403);

        $query = Ticket::query()
            ->where('company_id', $cid)
            ->with([
                'department',
                'assignedUser',
                'company',
            ])
            ->orderBy('created_at', 'desc');

        $user = auth()->user();

        if ($user && $user->role === 'agent' && filled($user->department_id)) {
            $query->where('department_id', $user->department_id);
        }

        if ($this->filters['status'] !== 'all') {
            $query->where('status', $this->filters['status']);
        }

        if ($this->filters['department'] !== 'all') {
            $query->where('department_id', $this->filters['department']);
        }

        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];

            $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('ticket_number', 'like', "%{$search}%");
            });
        }

        $tickets = $query->paginate($this->perPage);

        return view('livewire.desk.ticket-manager', compact('tickets'));
    }

    public function selectTicket(int $id)
    {
        $cid = $this->companyId();
        abort_if($cid === 0, 403);

        $this->selectedTicket = Ticket::query()
            ->where('company_id', $cid)
            ->with([
                'replies.user',
                'replies.attachments',
                'attachments',
                'assignedUser',
                'department',
                'company',
            ])
            ->findOrFail($id);

        $title = __('Ticket #') . $this->selectedTicket->ticket_number;
        $this->dispatch('update-browser-title', title: $title);
    }

    public function reply()
    {
        $this->validate();

        if (!$this->selectedTicket) {
            $this->addError('selectedTicket', __('No ticket selected'));
            return;
        }

        $cid = $this->companyId();
        abort_if($cid === 0, 403);

        if ((int) ($this->selectedTicket->company_id ?? 0) !== $cid) {
            abort(403);
        }

        DB::transaction(function () use ($cid) {
            $reply = TicketReply::create([
                'ticket_id' => $this->selectedTicket->id,
                'user_id' => auth()->id(),
                'customer_id' => null,
                'message' => $this->replyMessage,
            ]);

            if (!empty($this->attachments)) {
                foreach ($this->attachments as $file) {
                    // Second-line MIME check using the server-reported type.
                    // The `mimes:` rule above relies on the extension; this
                    // verifies the actual sniffed content type before storage.
                    $detectedMime = (string) ($file->getMimeType() ?? '');
                    if (! in_array($detectedMime, self::ALLOWED_ATTACHMENT_MIMES, true)) {
                        $this->addError('attachments', __('Unsupported attachment type.'));
                        return;
                    }

                    $path = $file->store(
                        "companies/{$cid}/tickets/{$this->selectedTicket->id}/replies/{$reply->id}",
                        'private'
                    );

                    $reply->attachments()->create([
                        'ticket_id' => $this->selectedTicket->id,
                        'company_id' => $cid,
                        'user_id' => auth()->id(),
                        'uploaded_by' => auth()->id(),
                        'disk' => 'private',
                        'path' => $path,
                        'file_path' => $path,
                        'original_name' => $file->getClientOriginalName(),
                        'mime' => $detectedMime,
                        'size' => $file->getSize(),
                    ]);
                }
            }

            $newStatus = (auth()->user()?->role === 'client') ? 'pending_agent' : 'pending_customer';

            $this->selectedTicket->update([
                'status' => $newStatus,
                'last_reply_at' => now(),
            ]);
        });

        $this->selectedTicket->refresh();
        
        $this->reset(['replyMessage', 'attachments']);

        session()->flash('success', __('Reply sent successfully'));
    }

    public function assign(int $userId)
    {
        if (!$this->selectedTicket) return;

        $cid = $this->companyId();
        abort_if($cid === 0, 403);

        if ((int) ($this->selectedTicket->company_id ?? 0) !== $cid) {
            abort(403);
        }

        $agentExists = User::where('id', $userId)
            ->whereHas('companies', fn($q) => $q->where('id', $cid))
            ->exists();

        if (!$agentExists) {
            $this->addError('assign', __('Invalid agent selected'));
            return;
        }

        $this->selectedTicket->update([
            'assigned_to' => $userId,
            'status' => 'pending_agent'
        ]);

        $this->selectedTicket->refresh();

        session()->flash('success', __('Ticket assigned successfully'));
    }

    public function close(int $id)
    {
        $cid = $this->companyId();
        abort_if($cid === 0, 403);

        $ticket = Ticket::query()
            ->where('company_id', $cid)
            ->find($id);

        if (!$ticket) return;

        $ticket->update(['status' => 'closed']);

        if ($this->selectedTicket && $this->selectedTicket->id === $ticket->id) {
            $this->selectedTicket->refresh();
        }

        session()->flash('success', __('Ticket closed successfully'));
    }

    public function updatingFilters()
    {
        $this->resetPage();
    }

    public function clearSelection()
    {
        $this->selectedTicket = null;
        $this->dispatch('update-browser-title', title: __('Ticket Management - Technical Support'));
    }
}