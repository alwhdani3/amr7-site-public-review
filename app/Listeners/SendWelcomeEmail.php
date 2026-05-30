<?php

namespace App\Listeners;

use App\Mail\WelcomeUserMail;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendWelcomeEmail implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(Registered $event): void
    {
        if ($event->user && $event->user->email) {
            Mail::to($event->user->email)->send(new WelcomeUserMail($event->user));
        }
    }
}
