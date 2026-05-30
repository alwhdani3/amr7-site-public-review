<?php

namespace App\Models;

// Hotfix: Filament\Notifications\Actions\Action غير موجود في إصدار Filament
// المربوط حالياً. لا نستوردها كـuse مباشر — نعمل resolve عبر class_exists()
// داخل sendNotifications() حتى لا يفشل إنشاء الرد إذا تغيّر إصدار Filament.
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Route;

class TicketReply extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'user_id',
        'customer_id',
        'message',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class, 'ticket_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    protected static function booted(): void
    {
        static::created(function (TicketReply $reply) {
            $ticket = $reply->ticket;

            if (! $ticket) {
                return;
            }

            $ticket->update([
                'last_reply_at' => now(),
            ]);

            static::sendNotifications($reply, $ticket);
        });
    }

    /**
     * Hotfix: resolve a usable notification action class at runtime.
     * Filament v3.x كان يستخدم Filament\Notifications\Actions\Action.
     * Filament v4.x / إعدادات معيّنة قد تنقلها إلى Filament\Actions\Action.
     * إذا لا توجد أي منهما، نعيد null ويتم إرسال الإشعار بدون action.
     */
    protected static function resolveNotificationActionClass(): ?string
    {
        if (class_exists('Filament\\Notifications\\Actions\\Action')) {
            return 'Filament\\Notifications\\Actions\\Action';
        }
        if (class_exists('Filament\\Actions\\Action')) {
            return 'Filament\\Actions\\Action';
        }
        return null;
    }

    /**
     * Hotfix: تبني مصفوفة actions بأمان. إذا فشل بناء action، نعيد مصفوفة فارغة
     * بدلاً من رمي exception يكسر إنشاء الرد.
     */
    protected static function buildActions(string $label, string $url): array
    {
        $class = static::resolveNotificationActionClass();
        if (! $class) {
            return [];
        }

        try {
            return [
                $class::make('view')
                    ->label($label)
                    ->url($url)
                    ->markAsRead(),
            ];
        } catch (\Throwable $e) {
            report($e);
            return [];
        }
    }

    protected static function sendNotifications(TicketReply $reply, Ticket $ticket): void
    {
        // Hotfix: notification failure يجب ألا يكسر إنشاء الرد. كل المنطق محاط
        // بـtry/catch وreport($e) — إذا فشل أي شيء داخل Filament، نسجّله ونتابع.
        try {
            $sender = $reply->user;

            if (! $sender) {
                return;
            }

            $legacyRole = strtolower((string) ($sender->role ?? ''));

            $isStaff = method_exists($sender, 'hasAnyRole')
                ? $sender->hasAnyRole(['super_admin', 'admin', 'manager', 'employee', 'support'])
                : in_array($legacyRole, ['admin', 'employee', 'manager', 'support'], true);

            $customerDashboardUrl = route('dashboard', ['ticket_id' => $ticket->id]);

            $staffUrl = Route::has('filament.amr7.resources.tickets.view')
                ? route('filament.amr7.resources.tickets.view', $ticket)
                : null;

            if ($isStaff) {
                $usersToNotify = $ticket->company?->users()
                    ->wherePivot('is_active', true)
                    ->get();

                if ($usersToNotify && $usersToNotify->isNotEmpty()) {
                    Notification::make()
                        ->title('رد جديد من آمر سبعة')
                        ->body("قام {$sender->name} بالرد على التذكرة #{$ticket->ticket_number}")
                        ->success()
                        ->actions(static::buildActions('عرض الرد', $customerDashboardUrl))
                        ->sendToDatabase($usersToNotify);
                }

                return;
            }

            $recipients = collect();

            if ($ticket->assignedUser) {
                $recipients->push($ticket->assignedUser);
            }

            $recipients = $recipients
                ->filter()
                ->unique('id')
                ->values();

            if ($recipients->isNotEmpty()) {
                Notification::make()
                    ->title('رد جديد من العميل')
                    ->body("العميل: {$sender->name} - تذكرة #{$ticket->ticket_number}")
                    ->info()
                    ->actions($staffUrl ? static::buildActions('فتح التذكرة', $staffUrl) : [])
                    ->sendToDatabase($recipients);
            }
        } catch (\Throwable $e) {
            // notification فشل لا يجب أن يكسر إنشاء الرد. نسجّل ونصمت.
            report($e);
        }
    }
}