<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

/**
 * P1.2 — سياسة المهام.
 *
 * تعتمد على Spatie permissions ضمن مجموعة "tasks":
 *   tasks.view_all, tasks.view_own, tasks.create, tasks.edit,
 *   tasks.delete, tasks.assign, tasks.change_status
 *
 * تذكير: Gate::before في AuthServiceProvider يمنح super_admin/admin
 * تجاوز كامل لكل السياسات.
 */
class TaskPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('tasks.view_all')
            || $user->hasPermissionTo('tasks.view_own');
    }

    public function view(User $user, Task $task): bool
    {
        if ($user->hasPermissionTo('tasks.view_all')) {
            return true;
        }

        if ((int) $user->id === (int) $task->assigned_to) {
            return true;
        }

        if ((int) $user->id === (int) $task->created_by) {
            return true;
        }

        if ($user->hasPermissionTo('tasks.view_own')
            && $task->department_id
            && (int) $user->department_id === (int) $task->department_id) {
            return true;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('tasks.create');
    }

    public function update(User $user, Task $task): bool
    {
        if ($user->hasPermissionTo('tasks.edit')) {
            return true;
        }

        // المكلَّف يمكنه تغيير الحالة فقط (يُتحقق منها في changeStatus)
        return (int) $user->id === (int) $task->assigned_to
            && $user->hasPermissionTo('tasks.change_status');
    }

    public function delete(User $user, Task $task): bool
    {
        return $user->hasPermissionTo('tasks.delete');
    }

    public function assign(User $user, Task $task): bool
    {
        return $user->hasPermissionTo('tasks.assign');
    }

    public function changeStatus(User $user, Task $task): bool
    {
        if (! $user->hasPermissionTo('tasks.change_status')) {
            return false;
        }

        return (int) $user->id === (int) $task->assigned_to
            || $user->hasPermissionTo('tasks.edit');
    }
}
