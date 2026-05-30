<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

/**
 * User = login account for the system.
 *
 * Identity / responsibility split (see docs/data-model.md):
 *   - User is a *login account*. It may belong to an internal staff member
 *     OR an external client who uses the portal — both shapes share this
 *     same row and are distinguished by their Spatie role.
 *   - System-level role is **Spatie/Permission only** (super_admin, admin,
 *     manager, employee, support, accountant, customer). Do not write new
 *     code against the legacy `users.role` column — it stays for read-only
 *     fallbacks and is scheduled for removal in a future phase.
 *   - Membership in a client *Company* is held in the `company_user` pivot.
 *     The pivot also has its own `role` column with two values (admin,
 *     employee) — that is the user's role *inside that company*, NOT a
 *     system role. Do not conflate the two.
 *   - `job_title` and `department_id` are inline on this table for
 *     simplicity; they belong logically to internal staff. There is no
 *     separate StaffProfile / Employee model today.
 *
 * Related tables: companies, company_user, departments.
 */
class User extends Authenticatable implements FilamentUser, MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, TwoFactorAuthenticatable, HasRoles;

    protected string $guard_name = 'web';

    // TODO (P1.6 cleanup): العمود 'role' أدناه قديم (legacy).
    // المصدر الرسمي للأدوار هو Spatie/Permission. لا تعتمد على users.role
    // في كود جديد. للحذف في Phase 2 بعد التأكد من عدم وجود قراءات حية.
    protected $fillable = [
        'name',
        'email',
        'password',
        'public_id',
        'role',
        'type',
        'locale',
        'mobile',
        'is_active',
        'suspended_at',
        'bio',
        'avatar',
        'signature',
        'job_title',
        'department_id',
        'active_company_id', // Mobile API — يُحدَّث عبر POST /api/mobile/companies/select
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
        'suspended_at' => 'datetime',
    ];

    protected $appends = [
        'initials',
        'is_admin',
    ];

    protected static function booted(): void
    {
        static::creating(function (User $user) {
            if (blank($user->public_id)) {
                $user->public_id = Str::upper(Str::random(10));
            }
        });
    }

    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class, 'company_user')
            // Phase B: expose `permissions` (nullable JSON) so the
            // helper methods below can read the stored matrix off
            // the pivot directly.
            ->withPivot(['role', 'designation', 'is_active', 'permissions'])
            ->withTimestamps();
    }

    /**
     * Phase B — per-company permission check. Resolves the user's
     * pivot row on the given company (defaults to the active company
     * in session), then defers to App\Support\CompanyPermissions for
     * the role-based / JSON-based decision. Returns false on any
     * missing pivot so a non-member never silently gets access.
     */
    public function hasCompanyPermission(string $group, string $action = 'view', ?int $companyId = null): bool
    {
        $companyId = (int) ($companyId ?? session('active_company_id') ?? 0);
        if ($companyId <= 0) {
            return false;
        }

        $pivot = $this->companies()
            ->where('companies.id', $companyId)
            ->first()?->pivot;

        if (! $pivot) {
            return false;
        }

        $matrix = \App\Support\CompanyPermissions::effective(
            $pivot->role ?? null,
            $pivot->permissions ?? null,
        );

        return \App\Support\CompanyPermissions::grants($matrix, $group, $action);
    }

    /**
     * Convenience wrapper used by Dashboard.mount() + the sidebar
     * filter. Returns false for any section not in SECTION_MAP so
     * unknown keys never silently grant access (`ai-review` is
     * intentionally absent — gated separately by hasBackofficeAccess).
     */
    public function canAccessCompanySection(string $section, ?int $companyId = null): bool
    {
        if (! array_key_exists($section, \App\Support\CompanyPermissions::SECTION_MAP)) {
            return false;
        }

        return $this->hasCompanyPermission(
            \App\Support\CompanyPermissions::sectionGroup($section),
            'view',
            $companyId,
        );
    }

    public function serviceRequests(): HasMany
    {
        return $this->hasMany(ServiceRequest::class, 'user_id');
    }

    public function financialStatementRequests(): HasMany
    {
        return $this->hasMany(FinancialStatementRequest::class, 'user_id');
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'user_id');
    }

    public function assignedTickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'assigned_to');
    }

    public function assignedTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'assigned_to');
    }

    public function createdTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'created_by');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class, 'user_id');
    }

    public function uploadedAttachments(): HasMany
    {
        return $this->hasMany(Attachment::class, 'uploaded_by');
    }

    public function getIsAdminAttribute(): bool
    {
        if (method_exists($this, 'hasAnyRole') && $this->hasAnyRole(['super_admin', 'admin'])) {
            return true;
        }

        return $this->hasLegacyRole(['admin', 'superadmin']);
    }

    public function isAdmin(): bool
    {
        return $this->is_admin;
    }

    public function isAgent(): bool
    {
        return $this->hasBackofficeAccess();
    }

    public function canReply(): bool
    {
        return $this->hasBackofficeAccess();
    }

    public function hasBackofficeAccess(): bool
    {
        if ($this->is_admin) {
            return true;
        }

        // Phase 9C-0: accountant مضاف للأدوار المسموح لها بدخول Filament.
        // customer لا يظهر في أي قائمة — يبقى محظوراً تلقائياً.
        if (method_exists($this, 'hasAnyRole') && $this->hasAnyRole(['manager', 'employee', 'support', 'accountant'])) {
            return true;
        }

        return $this->hasLegacyRole(['manager', 'agent', 'staff', 'employee', 'support', 'accountant']);
    }

    protected function hasLegacyRole(array $roles): bool
    {
        $legacyRole = strtolower((string) ($this->role ?? ''));
        $roles = array_map(fn ($role) => strtolower((string) $role), $roles);

        return in_array($legacyRole, $roles, true);
    }

    public function getInitialsAttribute(): string
    {
        $name = trim((string) $this->name);

        return collect(preg_split('/\s+/u', $name, -1, PREG_SPLIT_NO_EMPTY))
            ->map(fn ($part) => mb_substr($part, 0, 1))
            ->take(2)
            ->join('');
    }

    public function getPhoneAttribute(): ?string
    {
        return $this->mobile ?: null;
    }

    public function canAccessPanel(Panel $panel): bool
    {
        $panelId = $panel->getId();

        if (in_array($panelId, ['amr', 'amr7', 'admin'], true)) {
            return $this->hasBackofficeAccess();
        }

        return true;
    }

    public function scopeAdmins(Builder $query): Builder
    {
        return $query->where(function (Builder $q) {
            $q->whereHas('roles', function (Builder $roleQuery) {
                $roleQuery->whereIn('name', ['super_admin', 'admin']);
            })->orWhereIn('role', ['admin', 'superadmin']);
        });
    }

    public function scopeStaff(Builder $query): Builder
    {
        return $query->where(function (Builder $q) {
            $q->whereHas('roles', function (Builder $roleQuery) {
                $roleQuery->whereIn('name', ['super_admin', 'admin', 'manager', 'employee', 'support']);
            })->orWhereIn('role', ['admin', 'superadmin', 'manager', 'agent', 'staff', 'employee', 'support']);
        });
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function primaryCompany(): ?Company
    {
        return $this->companies()->wherePivot('is_active', true)->first()
            ?? $this->companies()->first();
    }

    public function displayRole(): string
    {
        $role = $this->roles()->first();

        if ($role) {
            return $role->display_name ?? $role->name;
        }

        return $this->role ?: 'عميل';
    }
}