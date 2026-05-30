<?php

namespace App\Policies;

use App\Models\FinancialStatementRequest;
use App\Models\User;

class FinancialStatementRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->canManageFinancialStatements($user);
    }

    public function view(User $user, FinancialStatementRequest $request): bool
    {
        if ($this->canManageFinancialStatements($user)) {
            return true;
        }

        return (int) $request->user_id === (int) $user->id;
    }

    public function create(User $user): bool
    {
        return $user->is_active ?? true;
    }

    public function update(User $user, FinancialStatementRequest $request): bool
    {
        if ($this->canManageFinancialStatements($user)) {
            return true;
        }

        return (int) $request->user_id === (int) $user->id
            && in_array((string) ($request->status ?? 'pending'), ['pending', 'draft'], true);
    }

    public function delete(User $user, FinancialStatementRequest $request): bool
    {
        if ($this->canManageFinancialStatements($user)) {
            return true;
        }

        return (int) $request->user_id === (int) $user->id
            && in_array((string) ($request->status ?? 'pending'), ['pending', 'draft'], true);
    }

    public function restore(User $user, FinancialStatementRequest $request): bool
    {
        return $this->canManageFinancialStatements($user);
    }

    public function forceDelete(User $user, FinancialStatementRequest $request): bool
    {
        return $this->canManageFinancialStatements($user);
    }

    protected function canManageFinancialStatements(User $user): bool
    {
        if (method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['super_admin', 'admin', 'manager', 'employee', 'support'])) {
            return true;
        }

        if (method_exists($user, 'can') && (
            $user->can('financial.viewAny') ||
            $user->can('financial.view') ||
            $user->can('financial.update') ||
            $user->can('financial.delete') ||
            $user->can('financial.manage')
        )) {
            return true;
        }

        $legacyRole = strtolower((string) ($user->role ?? ''));

        return in_array($legacyRole, ['admin', 'superadmin', 'manager', 'employee', 'support'], true);
    }
}