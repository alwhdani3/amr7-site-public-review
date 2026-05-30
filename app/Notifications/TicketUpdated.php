<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Filament\Notifications\Notification as FilamentNotification;

class TicketUpdated extends Notification
{
    use Queueable;

    public function __construct(public Ticket $ticket, public string $action)
    {
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return FilamentNotification::make()
            ->title('تحديث على التذكرة')
            ->body(match ($this->action) {
                'assigned' => 'تم إسناد التذكرة لك',
                'closed' => 'تم إغلاق التذكرة',
                default => 'تم تحديث التذكرة',
            })
            ->icon('heroicon-o-bell')
            ->success()
            ->toArray();
    }
}
