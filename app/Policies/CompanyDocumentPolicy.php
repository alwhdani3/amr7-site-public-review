<?php

namespace App\Policies;

use App\Models\CompanyDocument;
use App\Models\User;

/**
 * P1.2 — سياسة وثائق المنشأة.
 *
 * لا توجد مجموعة صلاحيات Spatie مخصصة للوثائق حاليًا — نعتمد على
 * عضوية المنشأة عبر pivot company_user:
 *   - admin/owner على المنشأة → إدارة كاملة لوثائقها (إنشاء/تعديل/حذف).
 *   - employee نشط على المنشأة → عرض فقط، ورفع الوثائق المطلوبة.
 *   - super_admin/admin النظام → تجاوز كامل عبر Gate::before.
 *
 * owner مدعوم كقيمة legacy آمنة حتى لا تُكسر منشآت لم تُطبّع بياناتها بعد.
 */
class CompanyDocumentPolicy
{
    public function viewAny(User $user): bool
    {
        return (bool) $user;
    }

    public function view(User $user, CompanyDocument $document): bool
    {
        return $this->isActiveCompanyMember($user, (int) $document->company_id);
    }

    public function create(User $user): bool
    {
        return $user->companies()
            ->wherePivot('is_active', true)
            ->exists();
    }

    public function update(User $user, CompanyDocument $document): bool
    {
        return $this->isActiveCompanyAdmin($user, (int) $document->company_id);
    }

    public function delete(User $user, CompanyDocument $document): bool
    {
        return $this->isActiveCompanyAdmin($user, (int) $document->company_id);
    }

    public function restore(User $user, CompanyDocument $document): bool
    {
        return $this->isActiveCompanyAdmin($user, (int) $document->company_id);
    }

    public function forceDelete(User $user, CompanyDocument $document): bool
    {
        return false;
    }

    protected function isActiveCompanyMember(User $user, int $companyId): bool
    {
        if ($companyId <= 0) {
            return false;
        }

        return $user->companies()
            ->whereKey($companyId)
            ->wherePivot('is_active', true)
            ->exists();
    }

    protected function isActiveCompanyAdmin(User $user, int $companyId): bool
    {
        if ($companyId <= 0) {
            return false;
        }

        if (method_exists($user, 'hasBackofficeAccess') && $user->hasBackofficeAccess()) {
            return true;
        }

        return $user->companies()
            ->whereKey($companyId)
            ->wherePivotIn('role', ['admin', 'owner'])
            ->wherePivot('is_active', true)
            ->exists();
    }
}
