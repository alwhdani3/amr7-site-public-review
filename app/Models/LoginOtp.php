<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoginOtp extends Model
{
    protected $table = 'login_otps';

    protected $fillable = [
        'phone',
        'code_hash',
        'attempts',
        'expires_at',
        'consumed_at',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'consumed_at' => 'datetime',
        'attempts' => 'integer',
    ];

    protected $hidden = ['code_hash'];

    public static function hashCode(string $plainCode): string
    {
        return hash('sha256', $plainCode);
    }

    public function isConsumed(): bool
    {
        return $this->consumed_at !== null;
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function isLocked(): bool
    {
        $max = (int) config('otp.max_attempts', 5);

        return $this->attempts >= $max;
    }

    public function isPending(): bool
    {
        return ! $this->isConsumed() && ! $this->isExpired() && ! $this->isLocked();
    }
}
