<?php

namespace App\Policies;

use App\Models\ServiceRequest;
use App\Models\User;

/**
 * P1.2 — سياسة طلبات الخدمات.
 *
 * تعتمد على Spatie permissions ضمن مجموعة "services":
 *   service_requests.view_all, service_requests.view_own,
 *   service_requests.update_status
 *
 * منطق الوصول للعميل:
 *   - مالك الطلب (user_id) يرى/يعدّل طلبه.
 *   - عضو نشط في منشأة الطلب (company_id) يرى الطلب.
 *   - الموظفون يستخدمون صلاحيات Spatie.
 *
 * Gate::before في AuthServiceProvider يمنح super_admin/admin
 * تجاوز كامل لكل السياسات.
 */
class ServiceRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('service_requests.view_all')
            || $user->hasPermissionTo('service_requests.view_own');
    }

    public function view(User $user, ServiceRequest $request): bool
    {
        if ($user->hasPermissionTo('service_requests.view_all')) {
            return true;
        }

        if ((int) $request->user_id === (int) $user->id) {
            return true;
        }

        if ($request->company_id && $this->isActiveCompanyMember($user, (int) $request->company_id)) {
            return true;
        }

        return false;
    }

    public function create(User $user): bool
    {
        if (! ($user->is_active ?? true)) {
            return false;
        }

        return $user->companies()
            ->wherePivot('is_active', true)
            ->exists()
            || $user->hasPermissionTo('service_requests.view_own');
    }

    public function update(User $user, ServiceRequest $request): bool
    {
        if ($user->hasPermissionTo('service_requests.update_status')) {
            return true;
        }

        return (int) $request->user_id === (int) $user->id
            && in_array((string) ($request->status ?? 'pending'), ['pending', 'new', 'draft'], true);
    }

    public function delete(User $user, ServiceRequest $request): bool
    {
        if ($user->hasPermissionTo('service_requests.update_status')) {
            return true;
        }

        return (int) $request->user_id === (int) $user->id
            && in_array((string) ($request->status ?? 'pending'), ['pending', 'new', 'draft'], true);
    }

    public function updateStatus(User $user, ServiceRequest $request): bool
    {
        return $user->hasPermissionTo('service_requests.update_status');
    }

    protected function isActiveCompanyMember(User $user, int $companyId): bool
    {
        return $user->companies()
            ->whereKey($companyId)
            ->wherePivot('is_active', true)
            ->exists();
    }
}
