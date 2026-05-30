<?php

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;

class TicketPolicy
{
    /**
     * يشوف قائمة التذاكر — الفلترة الفعلية في الـ query
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('tickets.view_all')
            || $user->hasPermissionTo('tickets.view_own');
    }

    /**
     * يشوف تذكرة معينة
     */
    public function view(User $user, Ticket $ticket): bool
    {
        // يشوف الكل
        if ($user->hasPermissionTo('tickets.view_all')) {
            return true;
        }

        // مكلّف بها
        if ($user->id === $ticket->assigned_to) {
            return true;
        }

        // نفس القسم
        if ($user->hasPermissionTo('tickets.view_own') && $user->department_id === $ticket->department_id) {
            return true;
        }

        // عميل — يملك الشركة
        return $user->companies()
            ->where('companies.id', $ticket->company_id)
            ->wherePivot('is_active', true)
            ->exists();
    }

    /**
     * إنشاء تذكرة — العملاء الموثّقون فقط
     */
    public function create(User $user): bool
    {
        if (! $user->hasVerifiedEmail()) {
            return false;
        }

        return $user->companies()->wherePivot('is_active', true)->exists();
    }

    /**
     * الرد على التذكرة
     */
    public function reply(User $user, Ticket $ticket): bool
    {
        if ($ticket->status === 'closed') {
            return false;
        }

        return $user->hasPermissionTo('tickets.reply') && $this->view($user, $ticket);
    }

    /**
     * إغلاق التذكرة
     */
    public function close(User $user, Ticket $ticket): bool
    {
        return $user->hasPermissionTo('tickets.close') && $this->view($user, $ticket);
    }

    /**
     * تغيير الأولوية
     */
    public function changePriority(User $user, Ticket $ticket): bool
    {
        return $user->hasPermissionTo('tickets.change_priority');
    }

    /**
     * تعيين موظف
     */
    public function assign(User $user, Ticket $ticket): bool
    {
        return $user->hasPermissionTo('tickets.assign');
    }

    /**
     * حذف التذكرة
     */
    public function delete(User $user, Ticket $ticket): bool
    {
        return $user->hasPermissionTo('tickets.delete');
    }

    /**
     * تحديث التذكرة (Filament)
     */
    public function update(User $user, Ticket $ticket): bool
    {
        return $user->hasPermissionTo('tickets.assign')
            || $user->hasPermissionTo('tickets.change_priority')
            || $user->id === $ticket->assigned_to;
    }
}
