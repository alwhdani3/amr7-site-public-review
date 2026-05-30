<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\{Ticket, TicketReply, Department};
use Illuminate\Support\Facades\Auth;

class SupportTickets extends Component
{
    use WithFileUploads;

    public ?int $companyId = null;

    // إنشاء تذكرة
    public string $subject = '';
    public string $description = '';
    public ?int $department_id = null;
    public string $priority = 'medium';

    // عرض/رد
    public ?int $activeTicketId = null;
    public string $replyMessage = '';

    // مرفقات رد
    public array $replyFiles = [];

    public function mount(): void
    {
        $company = auth()->user()->companies->first();
        $this->companyId = $company?->id;
    }

    public function openTicket(int $id): void
    {
        $this->activeTicketId = $id;
        $this->replyMessage = '';
        $this->replyFiles = [];
    }

    public function createTicket(): void
    {
        $this->validate([
            'subject' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'department_id' => ['nullable', 'integer'],
            'priority' => ['required', 'in:high,medium,low'],
        ]);

        if (!$this->companyId) {
            $this->addError('subject', 'لا توجد منشأة مرتبطة بالمستخدم.');
            return;
        }

        $ticket = Ticket::create([
            'company_id' => $this->companyId,
            'subject' => $this->subject,
            'description' => $this->description,
            'department_id' => $this->department_id,
            'priority' => $this->priority,
            'status' => 'open',
        ]);

        $this->reset(['subject', 'description', 'department_id', 'priority']);
        $this->priority = 'medium';

        $this->openTicket($ticket->id);

        $this->dispatch('ticket-created');
    }

    public function sendReply(): void
    {
        if (!$this->activeTicketId) return;

        $this->validate([
            'replyMessage' => ['required', 'string'],
            'replyFiles.*' => ['nullable', 'file', 'max:5120', 'mimes:pdf,jpg,jpeg,png'],
        ]);

        $ticket = Ticket::query()
            ->where('id', $this->activeTicketId)
            ->when(!auth()->user()->isAdmin(), fn ($q) => $q->where('company_id', $this->companyId))
            ->firstOrFail();

        $reply = TicketReply::create([
            'ticket_id' => $ticket->id,
            'user_id' => auth()->id(),
            'customer_id' => null,
            'message' => $this->replyMessage,
        ]);

        // حفظ مرفقات الرد (polymorphic attachments)
        foreach ($this->replyFiles as $file) {
            $path = $file->store("tickets/{$ticket->id}/replies/{$reply->id}", 'private');

            $reply->attachments()->create([
                'ticket_id' => $ticket->id,
                'company_id' => $ticket->company_id,
                'user_id' => auth()->id(),
                'uploaded_by' => auth()->id(),

                'disk' => 'public',
                'path' => $path,
                'file_path' => $path, // مؤقتًا لأن عندك عمودين
                'original_name' => $file->getClientOriginalName(),
                'mime' => $file->getMimeType(),
                'size' => $file->getSize(),
            ]);
        }

        $ticket->update([
            'last_reply_at' => now(),
            'status' => 'pending_customer',
        ]);

        $this->replyMessage = '';
        $this->replyFiles = [];

        $this->dispatch('reply-sent');
    }

    public function getOpenCountProperty(): int
    {
        if (!$this->companyId) return 0;

        return Ticket::query()
            ->when(!auth()->user()->isAdmin(), fn ($q) => $q->where('company_id', $this->companyId))
            ->where('status', 'open')
            ->count();
    }

    public function getTodayCountProperty(): int
    {
        if (!$this->companyId) return 0;

        return Ticket::query()
            ->when(!auth()->user()->isAdmin(), fn ($q) => $q->where('company_id', $this->companyId))
            ->whereDate('created_at', today())
            ->count();
    }

    public function render()
    {
        $tickets = Ticket::query()
            ->with('company')
            ->when(!auth()->user()->isAdmin(), fn ($q) => $q->where('company_id', $this->companyId))
            ->latest()
            ->get();

        $activeTicket = null;
        if ($this->activeTicketId) {
            $activeTicket = Ticket::query()
                ->with(['company', 'replies.user', 'replies.attachments'])
                ->when(!auth()->user()->isAdmin(), fn ($q) => $q->where('company_id', $this->companyId))
                ->find($this->activeTicketId);
        }

        $departments = Department::query()->orderBy('name')->get();

        return view('livewire.dashboard.support-tickets', compact('tickets', 'activeTicket', 'departments'));
    }
}
