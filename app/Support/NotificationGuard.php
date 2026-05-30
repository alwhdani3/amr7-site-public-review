<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Decides whether an outbound notification on an external channel
 * (email / whatsapp / telegram) is actually allowed to reach the wire.
 *
 * Safe by default. Returns false for every channel until both the
 * production flag is on AND dry-run is off. Database notifications
 * are not routed through this guard — they're internal-only and
 * always allowed.
 */
class NotificationGuard
{
    public static function canSendEmail(): bool
    {
        if (self::isDryRun()) {
            return false;
        }

        return (bool) config('notifications.email.production_enabled', false);
    }

    public static function canSendWhatsApp(): bool
    {
        if (self::isDryRun()) {
            return false;
        }

        return (bool) config('notifications.whatsapp.enabled', false);
    }

    public static function canSendTelegram(): bool
    {
        if (self::isDryRun()) {
            return false;
        }

        return (bool) config('notifications.telegram.enabled', false);
    }

    public static function isDryRun(): bool
    {
        return (bool) config('notifications.dry_run', true);
    }

    /**
     * Redirect any external-channel recipient to the allowed test
     * recipient when dry-run is on. Returns null if no test recipient
     * is configured (caller should then log + skip).
     */
    public static function emailTestRecipient(): ?string
    {
        $email = config('notifications.allowed_test_email');

        return is_string($email) && $email !== '' ? $email : null;
    }

    public static function whatsappTestRecipient(): ?string
    {
        $phone = config('notifications.allowed_test_phone');

        return is_string($phone) && $phone !== '' ? $phone : null;
    }

    public static function telegramTestChatId(): ?string
    {
        $chat = config('notifications.allowed_test_telegram_chat_id');

        return is_string($chat) && $chat !== '' ? $chat : null;
    }

    /**
     * Mask a recipient for safe logging.
     *   "user@example.com" -> "u***@example.com"
     *   "+966500000000"   -> "+966500***000"
     *   "123456789"       -> "12***789"
     */
    public static function maskRecipient(string $recipient): string
    {
        if (str_contains($recipient, '@')) {
            [$local, $domain] = explode('@', $recipient, 2);
            $masked = mb_substr($local, 0, 1) . '***';
            return $masked . '@' . $domain;
        }

        $len = mb_strlen($recipient);
        if ($len <= 4) {
            return '***';
        }

        return mb_substr($recipient, 0, max(2, intdiv($len, 3)))
            . '***'
            . mb_substr($recipient, -3);
    }

    /**
     * Standard structured log entry for a blocked or rerouted send.
     */
    public static function logDryRun(string $channel, string $type, string $originalRecipient, ?string $rerouteTo = null): void
    {
        Log::info('notifications.dry_run.blocked', [
            'channel'           => $channel,
            'notification_type' => $type,
            'recipient_masked'  => self::maskRecipient($originalRecipient),
            'rerouted_to_test'  => $rerouteTo ? self::maskRecipient($rerouteTo) : null,
            'correlation_id'    => (string) Str::uuid(),
        ]);
    }
}
