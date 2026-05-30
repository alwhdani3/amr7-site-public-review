<?php

namespace App\Services\Auth;

use App\Models\LoginOtp;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class OtpService
{
    public function isEnabled(): bool
    {
        return (bool) config('otp.enabled', false);
    }

    public function normalisePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';
        $digits = ltrim($digits, '0');

        if ($digits === '') {
            return '';
        }

        $cc = (string) config('otp.default_country_code', '966');

        if (! str_starts_with($digits, $cc)) {
            $digits = $cc.$digits;
        }

        return '+'.$digits;
    }

    public function isOnCooldown(string $phone): bool
    {
        return Cache::has($this->cooldownKey($phone));
    }

    public function mint(string $phone, ?string $ip = null, ?string $userAgent = null): array
    {
        if (! $this->isEnabled()) {
            return [
                'ok' => false,
                'reason' => 'disabled',
            ];
        }

        $normalised = $this->normalisePhone($phone);

        if ($normalised === '') {
            return [
                'ok' => false,
                'reason' => 'invalid_phone',
            ];
        }

        if ($this->isOnCooldown($normalised)) {
            return [
                'ok' => false,
                'reason' => 'cooldown',
            ];
        }

        $length = max(4, min(8, (int) config('otp.code_length', 6)));
        $code = $this->generateCode($length);
        $ttl = max(1, (int) config('otp.ttl_minutes', 5));

        LoginOtp::create([
            'phone' => $normalised,
            'code_hash' => LoginOtp::hashCode($code),
            'attempts' => 0,
            'expires_at' => Carbon::now()->addMinutes($ttl),
            'ip_address' => $ip ? substr($ip, 0, 45) : null,
            'user_agent' => $userAgent ? substr($userAgent, 0, 255) : null,
        ]);

        Cache::put(
            $this->cooldownKey($normalised),
            true,
            max(1, (int) config('otp.cooldown_seconds', 60))
        );

        $this->surfaceCodeForDevelopment($normalised, $code);

        return [
            'ok' => true,
            'phone' => $normalised,
            'expires_in_minutes' => $ttl,
        ];
    }

    public function verify(string $phone, string $plainCode): array
    {
        if (! $this->isEnabled()) {
            return [
                'ok' => false,
                'reason' => 'disabled',
            ];
        }

        $normalised = $this->normalisePhone($phone);

        if ($normalised === '' || $plainCode === '') {
            return [
                'ok' => false,
                'reason' => 'invalid_input',
            ];
        }

        $record = LoginOtp::query()
            ->where('phone', $normalised)
            ->whereNull('consumed_at')
            ->orderByDesc('id')
            ->first();

        if (! $record) {
            return [
                'ok' => false,
                'reason' => 'not_found',
            ];
        }

        if ($record->isExpired()) {
            return [
                'ok' => false,
                'reason' => 'expired',
            ];
        }

        if ($record->isLocked()) {
            return [
                'ok' => false,
                'reason' => 'locked',
            ];
        }

        $record->increment('attempts');

        $expected = LoginOtp::hashCode($plainCode);

        if (! hash_equals($record->code_hash, $expected)) {
            return [
                'ok' => false,
                'reason' => 'mismatch',
                'attempts_left' => max(0, (int) config('otp.max_attempts', 5) - $record->attempts),
            ];
        }

        $record->forceFill(['consumed_at' => Carbon::now()])->save();

        return [
            'ok' => true,
            'phone' => $normalised,
            'user' => $this->findUserByPhone($normalised),
        ];
    }

    public function findUserByPhone(string $phone): ?User
    {
        if (! Schema::hasTable('users')) {
            return null;
        }

        $normalised = $this->normalisePhone($phone);
        $localPart = ltrim(preg_replace('/\D+/', '', $normalised) ?? '', '0');

        $candidates = array_unique(array_filter([
            $normalised,
            ltrim($normalised, '+'),
            $localPart,
        ]));

        $query = User::query();
        $first = true;

        foreach (['phone', 'mobile'] as $column) {
            if (! Schema::hasColumn('users', $column)) {
                continue;
            }

            foreach ($candidates as $needle) {
                if ($first) {
                    $query->where($column, $needle);
                    $first = false;
                } else {
                    $query->orWhere($column, $needle);
                }
            }
        }

        if ($first) {
            return null;
        }

        return $query->first();
    }

    protected function generateCode(int $length): string
    {
        $min = 10 ** ($length - 1);
        $max = (10 ** $length) - 1;

        return (string) random_int($min, $max);
    }

    protected function cooldownKey(string $phone): string
    {
        return 'otp.cooldown.'.$phone;
    }

    protected function surfaceCodeForDevelopment(string $phone, string $code): void
    {
        $provider = config('otp.provider', 'none');

        if ($provider === 'none') {
            return;
        }

        if ($provider === 'log' && app()->isLocal()) {
            Log::channel(config('logging.default', 'stack'))
                ->info('[otp.dev] code minted', ['phone' => $phone, 'code' => $code]);

            return;
        }

        if ($provider === 'dry-run') {
            Cache::put('otp.last.'.$phone, $code, 300);
        }
    }
}
