<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MarketingCampaignMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $subjectText,
        public string $bodyText,
        public ?string $recipientName = null
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(
                config('mail.from.address'),
                config('mail.from.name', 'آمر سبعة')
            ),
            subject: $this->subjectText
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.marketing-campaign',
            with: [
                'subjectText' => $this->subjectText,
                'bodyText' => $this->bodyText,
                'recipientName' => $this->recipientName,
            ]
        );
    }
}
