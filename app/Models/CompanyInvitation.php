<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

/**
 * Phase A — team invitations.
 *
 * Plain tokens are produced by `mintToken()`, surfaced to the inviting
 * admin once via the dashboard, and **never** persisted. Only the
 * SHA-256 hex digest is stored in `token_hash`. The acceptance
 * endpoint hashes the URL token with `hashToken()` and looks the row
 * up by that unique column.
 */
class CompanyInvitation extends Model
{
    protected $table = 'company_invitations';

    protected $fillable = [
        'company_id',
        'email',
        'role',
        'token_hash',
        'invited_by',
        'accepted_at',
        'expires_at',
        'revoked_at',
    ];

    protected $casts = [
        'accepted_at' => 'datetime',
        'expires_at'  => 'datetime',
        'revoked_at'  => 'datetime',
    ];

    /* =====================
     | Relationships
     ===================== */

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    /* =====================
     | Token helpers
     ===================== */

    /**
     * Generate a fresh URL-safe random token. The plain value is
     * returned to the caller once and never stored.
     */
    public static function mintToken(): string
    {
        return Str::random(48);
    }

    /**
     * Hash a plain token to its on-disk representation. Uses the same
     * SHA-256 hex digest for both `store on creation` and `lookup on
     * acceptance` so the two always agree.
     */
    public static function hashToken(string $plainToken): string
    {
        return hash('sha256', $plainToken);
    }

    /* =====================
     | Lifecycle helpers
     ===================== */

    public function isAccepted(): bool
    {
        return $this->accepted_at !== null;
    }

    public function isRevoked(): bool
    {
        return $this->revoked_at !== null;
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function isPending(): bool
    {
        return ! $this->isAccepted() && ! $this->isRevoked() && ! $this->isExpired();
    }

    public function statusKey(): string
    {
        if ($this->isAccepted()) return 'accepted';
        if ($this->isRevoked())  return 'revoked';
        if ($this->isExpired())  return 'expired';
        return 'pending';
    }

    /**
     * Build the acceptance URL for the plain token. Used by the
     * dashboard right after minting so the admin can copy it.
     */
    public function acceptanceUrl(string $plainToken): string
    {
        return URL::route('company.invitations.accept', ['token' => $plainToken]);
    }
}
