<?php

namespace App\Mail\Operations;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class TicketReplyMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Ticket $ticket,
        public string $replyPreview,
        public ?string $url = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'تحديث على التذكرة - شركة آمر سبعة لحلول الأعمال');
    }

    public function content(): Content
    {
        $preview = Str::limit(trim($this->replyPreview), 180);
        $url = $this->url ?: route('dashboard', ['ticket_id' => $this->ticket->id]);

        return new Content(
            view: 'emails.operations.ticket-reply',
            text: 'emails.plain.operations-notification',
            with: [
                'emailTitle' => 'تحديث على التذكرة',
                'ticketNumber' => $this->ticket->ticket_number,
                'subject' => $this->ticket->subject,
                'replyPreview' => $preview,
                'status' => $this->ticket->status_label ?: $this->ticket->status,
                'url' => $url,
                'title' => 'تحديث على التذكرة',
                'intro' => 'يوجد رد جديد على محادثة الدعم داخل بوابة آمر سبعة.',
                'actionLabel' => 'عرض المحادثة',
                'lines' => [
                    'رقم التذكرة' => $this->ticket->ticket_number,
                    'الموضوع' => $this->ticket->subject,
                    'آخر رد' => $preview,
                    'الحالة' => $this->ticket->status_label ?: $this->ticket->status,
                ],
            ],
        );
    }
}
