<?php

namespace App\Mail\Operations;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewTicketAdminMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Ticket $ticket,
        public ?User $customer = null,
        public ?string $url = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'تذكرة جديدة - شركة آمر سبعة لحلول الأعمال');
    }

    public function content(): Content
    {
        $ticketUrl = $this->url ?: config('app.url') . '/amr7/tickets/' . $this->ticket->id;

        return new Content(
            view: 'emails.operations.new-ticket-admin',
            text: 'emails.plain.operations-notification',
            with: [
                'emailTitle' => 'تذكرة جديدة - شركة آمر سبعة لحلول الأعمال',
                'customerName' => $this->customer?->name ?: $this->ticket->user?->name,
                'companyName' => $this->ticket->company?->name,
                'subject' => $this->ticket->subject,
                'ticketNumber' => $this->ticket->ticket_number,
                'priority' => $this->ticket->priority_label ?: $this->ticket->priority,
                'status' => $this->ticket->status_label ?: $this->ticket->status,
                'url' => $ticketUrl,
                'title' => 'تذكرة جديدة - شركة آمر سبعة لحلول الأعمال',
                'intro' => 'تم إنشاء هذه التذكرة من بوابة آمر سبعة.',
                'actionLabel' => 'فتح التذكرة',
                'lines' => [
                    'اسم العميل' => $this->customer?->name ?: $this->ticket->user?->name,
                    'الشركة' => $this->ticket->company?->name,
                    'الموضوع' => $this->ticket->subject,
                    'رقم التذكرة' => $this->ticket->ticket_number,
                    'الأولوية' => $this->ticket->priority_label ?: $this->ticket->priority,
                    'الحالة' => $this->ticket->status_label ?: $this->ticket->status,
                ],
            ],
        );
    }
}
