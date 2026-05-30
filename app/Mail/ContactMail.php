<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ContactMail extends Mailable
{
    use Queueable, SerializesModels;

    public array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function build()
    {
        $subject = '🔔 طلب تواصل جديد من: ' . ($this->data['name'] ?? 'زائر');

        $email = $this->subject($subject)
            ->view('emails.admin_notification')
            ->with(['formData' => $this->data]);

        if (! empty($this->data['attachment'])) {
            $attachment = $this->data['attachment'];

            if (Storage::disk('private')->exists($attachment)) {
                $email->attachFromStorageDisk('private', $attachment);
            } elseif (Storage::disk('public')->exists($attachment)) {
                $email->attachFromStorageDisk('public', $attachment);
            }
        }

        return $email;
    }
}