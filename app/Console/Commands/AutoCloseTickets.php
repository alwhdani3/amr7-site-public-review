<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Ticket;

class AutoCloseTickets extends Command
{
    protected $signature = 'tickets:auto-close';
    protected $description = 'إغلاق التذاكر تلقائياً بعد فترة بدون رد';

    public function handle(): int
    {
        $count = Ticket::where('status', 'open')
            ->where('updated_at', '<=', now()->subDays(3))
            ->update(['status' => 'closed']);

        $this->info("تم إغلاق {$count} تذكرة تلقائياً.");

        return self::SUCCESS;
    }
}
