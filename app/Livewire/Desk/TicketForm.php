<?php

namespace App\Livewire\Desk;

use Livewire\Component;
use App\Models\Ticket;
use App\Models\Department;

class TicketForm extends Component
{
    public ?Ticket $ticket = null;

    public $subject;
    public $description;
    public $priority = 'medium';
    public $department_id;

    protected function companyId(): int
    {
        return (int) (auth()->user()?->companies()?->first()?->id ?? 0);
    }

    protected function rules()
    {
        return [
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => 'required|in:low,medium,high',
            'department_id' => 'required|exists:departments,id',
        ];
    }

    public function mount(?Ticket $ticket = null)
    {
        if ($ticket) {
            $userCompanyIds = auth()->user()?->companies()?->pluck('id') ?? collect();
            abort_if(! $userCompanyIds->contains($ticket->company_id), 403);

            $this->ticket = $ticket;
            $this->subject = $ticket->subject;
            $this->description = $ticket->description;
            $this->priority = $ticket->priority;
            $this->department_id = $ticket->department_id;
        }
    }

    public function save()
    {
        $this->validate();

        $cid = $this->companyId();
        abort_if($cid === 0, 403);

        if ($this->ticket) {
            // Re-verify ownership on save to guard against Livewire state tampering.
            $userCompanyIds = auth()->user()?->companies()?->pluck('id') ?? collect();
            abort_if(! $userCompanyIds->contains($this->ticket->company_id), 403);

            $this->ticket->update([
                'company_id' => $cid,
                'subject' => $this->subject,
                'description' => $this->description,
                'priority' => $this->priority,
                'department_id' => $this->department_id,
            ]);

            session()->flash('success', 'تم تحديث التذكرة');
            return redirect()->route('amr7.tickets.edit', $this->ticket);
        }

        $ticket = Ticket::create([
            'company_id' => $cid,
            'subject' => $this->subject,
            'description' => $this->description,
            'priority' => $this->priority,
            'department_id' => $this->department_id,
            'status' => 'open',
            'assigned_to' => null,
        ]);

        session()->flash('success', 'تم إنشاء التذكرة');
        return redirect()->route('amr7.tickets.edit', $ticket);
    }

    public function render()
    {
        return view('livewire.desk.ticket-form', [
            'departments' => Department::all(),
        ]);
    }
}
