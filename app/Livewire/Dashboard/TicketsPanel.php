<?php

namespace App\Livewire\Dashboard;

use App\Models\Attachment;
use App\Models\Ticket;
use App\Models\TicketReply;
use Livewire\Component;
use Livewire\WithFileUploads;

class TicketsPanel extends Component
{
    use WithFileUploads;

    public int $companyId;

    public string $subject = '';
    public string $description = '';
    public array $newFiles = [];
    public bool $showCreateModal = false;

    public ?int $activeTicketId = null;
    public string $replyMessage = '';
    public array $replyFiles = [];

    protected $listeners = [
        'tickets-open-create' => 'openCreate',
        'tickets-open-ticket' => 'openTicket',
    ];

    public function mount(?int $companyId = null): void
    {
        abort_unless(auth()->check(), 403);

        $resolved = $companyId
            ?? (int) session('active_company_id')
            ?: auth()->user()->primaryCompany()?->id;

        abort_unless(
            $resolved && auth()->user()->companies()
                ->whereKey((int) $resolved)
                ->wherePivot('is_active', true)
                ->exists(),
            403
        );

        $this->companyId = (int) $resolved;
    }

    private function assertActiveCompanyMember(): void
    {
        abort_unless(
            auth()->check() && auth()->user()->companies()
                ->whereKey((int) $this->companyId)
                ->wherePivot('is_active', true)
                ->exists(),
            403
        );
    }

    public function rules(): array
    {
        return [
            'subject'      => ['required', 'string', 'max:255'],
            'description'  => ['required', 'string', 'min:5'],
            'newFiles.*'   => ['file', 'max:5120', 'mimes:jpg,jpeg,png,pdf,webp'],
        ];
    }

    public function replyRules(): array
    {
        return [
            'replyMessage' => ['required', 'string', 'min:1'],
            'replyFiles.*' => ['file', 'max:5120', 'mimes:jpg,jpeg,png,pdf,webp'],
        ];
    }

    public function openCreate(): void
    {
        $this->reset(['subject', 'description', 'newFiles']);
        $this->resetErrorBag();
        $this->showCreateModal = true;
    }

    public function closeCreate(): void
    {
        $this->showCreateModal = false;
    }

    public function createTicket(): void
    {
        $this->assertActiveCompanyMember();

        $this->validate();

        $ticket = Ticket::create([
            'company_id'  => $this->companyId,
            'subject'     => $this->subject,
            'description' => $this->description,
            'user_id'     => auth()->id(),
            'priority'    => 'medium',
            'status'      => 'open',
        ]);

        foreach ($this->newFiles as $file) {
            $path = $file->store(
                "companies/{$this->companyId}/tickets/{$ticket->id}",
                'private'
            );

            Attachment::create([
                'ticket_id'       => $ticket->id,
                'company_id'      => $this->companyId,
                'user_id'         => auth()->id(),
                'uploaded_by'     => auth()->id(),
                'disk'            => 'private',
                'path'            => $path,
                'file_path'       => $path,
                'original_name'   => $file->getClientOriginalName(),
                'mime'            => $file->getMimeType(),
                'size'            => (int) $file->getSize(),
                'attachable_type' => Ticket::class,
                'attachable_id'   => $ticket->id,
            ]);
        }

        $this->showCreateModal = false;
        $this->activeTicketId = $ticket->id;
        $this->reset(['subject', 'description', 'newFiles']);

        session()->flash('success', 'تم فتح التذكرة بنجاح.');
    }

    public function openTicket(int $ticketId): void
    {
        $this->assertActiveCompanyMember();

        $exists = Ticket::query()
            ->where('company_id', $this->companyId)
            ->whereKey($ticketId)
            ->exists();

        abort_unless($exists, 404);

        $this->activeTicketId = $ticketId;
        $this->reset(['replyMessage', 'replyFiles']);
        $this->resetErrorBag();
    }

    public function sendReply(): void
    {
        $this->assertActiveCompanyMember();

        $this->validate($this->replyRules());

        abort_if(! $this->activeTicketId, 404);

        $ticket = Ticket::query()
            ->where('company_id', $this->companyId)
            ->whereKey($this->activeTicketId)
            ->firstOrFail();

        $reply = TicketReply::create([
            'ticket_id' => $ticket->id,
            'user_id'   => auth()->id(),
            'message'   => $this->replyMessage,
        ]);

        foreach ($this->replyFiles as $file) {
            $path = $file->store(
                "companies/{$this->companyId}/tickets/{$ticket->id}/replies/{$reply->id}",
                'private'
            );

            Attachment::create([
                'ticket_id'       => $ticket->id,
                'company_id'      => $this->companyId,
                'user_id'         => auth()->id(),
                'uploaded_by'     => auth()->id(),
                'disk'            => 'private',
                'path'            => $path,
                'file_path'       => $path,
                'original_name'   => $file->getClientOriginalName(),
                'mime'            => $file->getMimeType(),
                'size'            => (int) $file->getSize(),
                'attachable_type' => TicketReply::class,
                'attachable_id'   => $reply->id,
            ]);
        }

        $ticket->update([
            'last_reply_at' => now(),
        ]);

        $this->reset(['replyMessage', 'replyFiles']);

        session()->flash('success', 'تم إرسال الرد.');
    }

    public function render()
    {
        $tickets = Ticket::query()
            ->where('company_id', $this->companyId)
            ->latest()
            ->get();

        $activeTicket = null;
        $replies = collect();
        $attachments = collect();

        if ($this->activeTicketId) {
            $activeTicket = Ticket::query()
                ->where('company_id', $this->companyId)
                ->whereKey($this->activeTicketId)
                ->first();

            if ($activeTicket) {
                $replies = $activeTicket->replies()
                    ->with(['user', 'attachments'])
                    ->oldest()
                    ->get();

                $attachments = $activeTicket->allAttachments()
                    ->latest()
                    ->get();
            }
        }

        return view('livewire.dashboard.tickets-panel', compact(
            'tickets',
            'activeTicket',
            'replies',
            'attachments'
        ));
    }
}