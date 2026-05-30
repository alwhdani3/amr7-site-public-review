<div class="max-w-7xl mx-auto px-4 py-8">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-black text-gray-900">{{ __('الموظفون والمستخدمون') }}</h1>
            <p class="text-gray-500 text-sm mt-1">إدارة فريق العمل وصلاحياتهم</p>
        </div>
        @can('employees.create')
        <button wire:click="openCreate"
                class="bg-[#1FA7A2] hover:bg-[#167F7B] text-white px-5 py-2.5 rounded-xl font-bold text-sm flex items-center gap-2 transition">
            <i class="fas fa-plus"></i> موظف جديد
        </button>
        @endcan
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 mb-6 grid grid-cols-1 md:grid-cols-4 gap-3">
        <div class="relative">
            <i class="fas fa-search absolute top-3 right-3 text-gray-400 text-sm"></i>
            <input wire:model.live.debounce.300ms="search"
                   type="text" placeholder="بحث بالاسم أو البريد..."
                   class="w-full bg-gray-50 border border-gray-200 rounded-xl pr-9 pl-3 py-2.5 text-sm focus:ring-[#1FA7A2] focus:border-[#1FA7A2] outline-none">
        </div>

        <select wire:model.live="roleFilter"
                class="bg-gray-50 border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:ring-[#1FA7A2] focus:border-[#1FA7A2] outline-none">
            <option value="">كل الأدوار</option>
            @foreach($roles as $role)
                <option value="{{ $role->name }}">{{ $role->display_name ?? $role->name }}</option>
            @endforeach
        </select>

        <select wire:model.live="deptFilter"
                class="bg-gray-50 border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:ring-[#1FA7A2] focus:border-[#1FA7A2] outline-none">
            <option value="">كل الأقسام</option>
            @foreach($departments as $dept)
                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
            @endforeach
        </select>

        <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
            <input wire:model.live="activeOnly" type="checkbox" class="rounded text-[#1FA7A2]">
            النشطون فقط
        </label>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="text-right px-4 py-3 font-bold text-gray-600">الموظف</th>
                    <th class="text-right px-4 py-3 font-bold text-gray-600">الدور</th>
                    <th class="text-right px-4 py-3 font-bold text-gray-600">القسم</th>
                    <th class="text-right px-4 py-3 font-bold text-gray-600">الحالة</th>
                    <th class="text-right px-4 py-3 font-bold text-gray-600">الإجراءات</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($employees as $emp)
                <tr class="hover:bg-gray-50 transition" wire:key="emp-{{ $emp->id }}">
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            <img src="{{ $emp->avatar ? asset('storage/'.$emp->avatar) : 'https://ui-avatars.com/api/?name='.urlencode($emp->name).'&background=1FA7A2&color=fff&size=40' }}"
                                 class="w-9 h-9 rounded-full object-cover" alt="{{ $emp->name }}">
                            <div>
                                <div class="font-bold text-gray-900">{{ $emp->name }}</div>
                                <div class="text-gray-400 text-xs">{{ $emp->email }}</div>
                                @if($emp->job_title)
                                    <div class="text-gray-400 text-xs">{{ $emp->job_title }}</div>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3">
                        @if($emp->roles->first())
                            <span class="bg-teal-50 text-teal-700 text-xs font-bold px-2.5 py-1 rounded-full">
                                {{ $emp->roles->first()->display_name ?? $emp->roles->first()->name }}
                            </span>
                        @else
                            <span class="text-gray-400 text-xs">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-gray-600">{{ $emp->department?->name ?? '—' }}</td>
                    <td class="px-4 py-3">
                        @if($emp->is_active)
                            <span class="bg-green-50 text-green-700 text-xs font-bold px-2.5 py-1 rounded-full">نشط</span>
                        @else
                            <span class="bg-red-50 text-red-700 text-xs font-bold px-2.5 py-1 rounded-full">موقوف</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2">
                            @can('employees.edit')
                            <button wire:click="openEdit({{ $emp->id }})"
                                    class="text-gray-400 hover:text-[#1FA7A2] transition" title="تعديل">
                                <i class="fas fa-pen text-sm"></i>
                            </button>
                            @endcan

                            @can('employees.toggle_active')
                            <button wire:click="toggleActive({{ $emp->id }})"
                                    wire:confirm="{{ $emp->is_active ? 'إيقاف الحساب؟' : 'تفعيل الحساب؟' }}"
                                    class="text-gray-400 hover:text-orange-500 transition"
                                    title="{{ $emp->is_active ? 'إيقاف' : 'تفعيل' }}">
                                <i class="fas {{ $emp->is_active ? 'fa-ban' : 'fa-check-circle' }} text-sm"></i>
                            </button>
                            @endcan

                            @can('employees.delete')
                            @if($emp->id !== auth()->id())
                            <button wire:click="delete({{ $emp->id }})"
                                    wire:confirm="حذف {{ $emp->name }} نهائياً؟"
                                    class="text-gray-400 hover:text-red-500 transition" title="حذف">
                                <i class="fas fa-trash text-sm"></i>
                            </button>
                            @endif
                            @endcan
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center py-12 text-gray-400">
                        <i class="fas fa-users text-3xl mb-3 block"></i>
                        لا يوجد موظفون مطابقون
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <div class="px-4 py-3 border-t border-gray-100">
            {{ $employees->links() }}
        </div>
    </div>

    {{-- Modal إنشاء / تعديل --}}
    @if($showModal)
    <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4" wire:click.self="$set('showModal', false)">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between p-5 border-b border-gray-100">
                <h2 class="font-black text-gray-900">{{ $editingId ? 'تعديل الموظف' : 'موظف جديد' }}</h2>
                <button wire:click="$set('showModal', false)" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form wire:submit="save" class="p-5 space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">الاسم الكامل *</label>
                        <input wire:model="name" type="text" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:ring-[#1FA7A2] focus:border-[#1FA7A2] outline-none">
                        @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">البريد الإلكتروني *</label>
                        <input wire:model="email" type="email" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:ring-[#1FA7A2] focus:border-[#1FA7A2] outline-none">
                        @error('email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">الجوال</label>
                        <input wire:model="mobile" type="tel" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:ring-[#1FA7A2] focus:border-[#1FA7A2] outline-none" dir="ltr">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">المسمى الوظيفي</label>
                        <input wire:model="job_title" type="text" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:ring-[#1FA7A2] focus:border-[#1FA7A2] outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">الدور *</label>
                        <select wire:model="spatie_role" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:ring-[#1FA7A2] focus:border-[#1FA7A2] outline-none">
                            <option value="">اختر الدور...</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->name }}">{{ $role->display_name ?? $role->name }}</option>
                            @endforeach
                        </select>
                        @error('spatie_role') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">القسم</label>
                        <select wire:model="department_id" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:ring-[#1FA7A2] focus:border-[#1FA7A2] outline-none">
                            <option value="">بدون قسم</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">
                        كلمة المرور {{ $editingId ? '(اتركها فارغة إذا لا تريد تغييرها)' : '*' }}
                    </label>
                    <input wire:model="password" type="password"
                           class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:ring-[#1FA7A2] focus:border-[#1FA7A2] outline-none">
                    @error('password') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <label class="flex items-center gap-2 text-sm cursor-pointer">
                    <input wire:model="is_active" type="checkbox" class="rounded text-[#1FA7A2]">
                    <span class="font-bold text-gray-700">الحساب مفعّل</span>
                </label>

                <div class="flex gap-3 pt-2">
                    <button type="submit"
                            class="flex-1 bg-[#1FA7A2] hover:bg-[#167F7B] text-white py-2.5 rounded-xl font-bold text-sm transition"
                            wire:loading.attr="disabled">
                        <span wire:loading.remove>{{ $editingId ? 'حفظ التعديلات' : 'إنشاء الموظف' }}</span>
                        <span wire:loading><i class="fas fa-spinner fa-spin"></i> جارٍ الحفظ...</span>
                    </button>
                    <button type="button" wire:click="$set('showModal', false)"
                            class="px-5 py-2.5 border border-gray-200 rounded-xl text-sm text-gray-600 hover:bg-gray-50 transition">
                        إلغاء
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

</div>
