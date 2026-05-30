<?php

namespace App\Livewire;

use App\Models\Department;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Rule;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

/**
 * لوحة إدارة الموظفين — Livewire بدون Controller
 * Route: GET /amr7-staff  (أضفها في web.php)
 */
class EmployeesPanel extends Component
{
    use WithPagination;

    // ─── Filters ───
    public string $search     = '';
    public string $roleFilter = '';
    public string $deptFilter = '';
    public bool   $activeOnly = true;

    // ─── Modal: إنشاء / تعديل ───
    public bool   $showModal  = false;
    public ?int   $editingId  = null;

    #[Rule('required|string|max:255')]
    public string $name       = '';

    #[Rule('required|email|max:255')]
    public string $email      = '';

    #[Rule('nullable|string|max:20')]
    public string $mobile     = '';

    #[Rule('nullable|string|max:255')]
    public string $job_title  = '';

    #[Rule('required')]
    public string $spatie_role = '';

    #[Rule('nullable|exists:departments,id')]
    public ?int $department_id = null;

    #[Rule('nullable|min:8')]
    public string $password   = '';

    public bool   $is_active  = true;

    // ─── Authorization ───
    public function mount(): void
    {
        abort_unless(auth()->user()?->hasPermissionTo('employees.view'), 403);
    }

    // ─── Computed ───

    #[Computed]
    public function employees()
    {
        return User::with(['roles', 'department'])
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('email', 'like', "%{$this->search}%")
                  ->orWhere('mobile', 'like', "%{$this->search}%");
            }))
            ->when($this->roleFilter, fn ($q) => $q->whereHas('roles', fn ($r) => $r->where('name', $this->roleFilter)))
            ->when($this->deptFilter, fn ($q) => $q->where('department_id', $this->deptFilter))
            ->when($this->activeOnly, fn ($q) => $q->where('is_active', true))
            ->orderBy('name')
            ->paginate(15);
    }

    #[Computed]
    public function roles()
    {
        return Role::orderBy('display_name')->get();
    }

    #[Computed]
    public function departments()
    {
        return Department::active()->orderBy('name')->get();
    }

    // ─── Modal: فتح لإنشاء ───
    public function openCreate(): void
    {
        abort_unless(auth()->user()?->hasPermissionTo('employees.create'), 403);
        $this->reset(['editingId', 'name', 'email', 'mobile', 'job_title', 'spatie_role', 'department_id', 'password']);
        $this->is_active = true;
        $this->showModal = true;
    }

    // ─── Modal: فتح لتعديل ───
    public function openEdit(int $id): void
    {
        abort_unless(auth()->user()?->hasPermissionTo('employees.edit'), 403);
        $user = User::with('roles')->findOrFail($id);
        $this->editingId     = $id;
        $this->name          = $user->name;
        $this->email         = $user->email;
        $this->mobile        = $user->mobile ?? '';
        $this->job_title     = $user->job_title ?? '';
        $this->spatie_role   = $user->roles->first()?->name ?? '';
        $this->department_id = $user->department_id;
        $this->is_active     = $user->is_active;
        $this->password      = '';
        $this->showModal     = true;
    }

    // ─── حفظ (إنشاء أو تعديل) ───
    public function save(): void
    {
        $this->validate();

        $data = [
            'name'          => $this->name,
            'email'         => $this->email,
            'mobile'        => $this->mobile ?: null,
            'job_title'     => $this->job_title ?: null,
            'department_id' => $this->department_id,
            'is_active'     => $this->is_active,
        ];

        if ($this->password) {
            $data['password'] = Hash::make($this->password);
        }

        if ($this->editingId) {
            // تعديل
            $user = User::findOrFail($this->editingId);
            $user->update($data);
        } else {
            // إنشاء
            $data['password'] = Hash::make($this->password ?: str()->random(12));
            $data['email_verified_at'] = now(); // الموظفون موثّقون تلقائياً
            $user = User::create($data);
        }

        // ✅ تعيين الدور Spatie
        if ($this->spatie_role) {
            $user->syncRoles([$this->spatie_role]);
        }

        $this->showModal = false;
        $this->dispatch('notify', type: 'success', message: 'تم الحفظ بنجاح');
    }

    // ─── تفعيل / إيقاف الحساب ───
    public function toggleActive(int $id): void
    {
        abort_unless(auth()->user()?->hasPermissionTo('employees.toggle_active'), 403);
        $user = User::findOrFail($id);
        $user->update([
            'is_active'    => ! $user->is_active,
            'suspended_at' => ! $user->is_active ? null : now(),
        ]);
        $this->dispatch('notify', type: 'success', message: 'تم تحديث الحالة');
    }

    // ─── حذف ───
    public function delete(int $id): void
    {
        abort_unless(auth()->user()?->hasPermissionTo('employees.delete'), 403);
        abort_if($id === auth()->id(), 403, 'لا يمكنك حذف حسابك');
        User::findOrFail($id)->delete();
        $this->dispatch('notify', type: 'success', message: 'تم الحذف');
    }

    public function render()
    {
        return view('livewire.employees-panel', [
            'employees'   => $this->employees,
            'roles'       => $this->roles,
            'departments' => $this->departments,
        ])->layout('layouts.app', ['title' => 'الموظفون']);
    }
}
