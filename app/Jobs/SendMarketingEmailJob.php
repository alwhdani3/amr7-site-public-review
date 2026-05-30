<?php

namespace App\Jobs;

use App\Mail\MarketingCampaignMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendMarketingEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 120;

    public function __construct(
        public string $email,
        public string $subject,
        public string $body,
        public ?string $name = null
    ) {
        $this->onQueue('emails');
    }

    public function handle(): void
    {
        Mail::to($this->email)->send(
            new MarketingCampaignMail(
                subjectText: $this->subject,
                bodyText: $this->body,
                recipientName: $this->name
            )
        );
    }
}
