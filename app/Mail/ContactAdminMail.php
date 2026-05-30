<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;

class ContactAdminMail extends Mailable
{
    use Queueable, SerializesModels;

    public array $data;
    public string $locale;

    public function __construct(array $data, string $locale = 'ar')
    {
        $this->data = $data;
        $this->locale = $locale;
    }

    public function build()
    {
        App::setLocale($this->locale);

        $email = $this->subject(__('طلب تواصل جديد - شركة آمر سبعة لحلول الأعمال'))
            ->view('emails.contact_admin')
            ->with(['data' => $this->data]);

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