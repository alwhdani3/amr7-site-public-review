<?php

namespace App\Livewire\Company;

use Livewire\Component;
use App\Models\Company;
use Illuminate\Support\Facades\DB;

class CreateCompanyModal extends Component
{
    public bool $open = false;

    public string $name = '';

    public ?string $unified_number = null;

    public ?string $tax_number = null;

    public ?string $city = null;

    public ?string $address = null;

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'unified_number' => ['nullable', 'string', 'max:50', 'regex:/^[0-9]+$/'],
            'tax_number' => ['nullable', 'string', 'max:50'],
            'city' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:255'],
        ];
    }

    protected array $messages = [
        'name.required' => 'اسم المنشأة مطلوب.',
        'name.max' => 'اسم المنشأة طويل جدًا.',
        'unified_number.regex' => 'الرقم الموحد 700 يجب أن يحتوي على أرقام فقط.',
        'unified_number.max' => 'الرقم الموحد 700 لا يجب أن يتجاوز 50 رقمًا.',
        'tax_number.max' => 'الرقم الضريبي لا يجب أن يتجاوز 50 رقمًا.',
        'city.max' => 'اسم المدينة طويل جدًا.',
        'address.max' => 'العنوان طويل جدًا.',
    ];

    public function openModal(): void
    {
        $this->reset(); // تصفير الحقول لضمان خلوها من بيانات سابقة
        $this->resetValidation();
        $this->open = true;
    }

    public function closeModal(): void
    {
        $this->open = false;
        // لا نحتاج reset هنا لأننا نفعله عند الفتح، ولكن يمكن إضافته
    }

    public function save()
    {
        $this->validate(); // سيقوم بقراءة القواعد من الـ Attributes أعلاه

        // ✅ استخدام Transaction لضمان سلامة البيانات 100%
        DB::transaction(function () {
            
            // 1. إنشاء الشركة
            $company = Company::create([
                'name' => $this->name,
                'unified_number' => $this->unified_number,
                'tax_number' => $this->tax_number,
                'city' => $this->city,
                'address' => $this->address,
            ]);

            $user = auth()->user();

            // 2. تحسين الأداء: تعطيل كل شركات المستخدم بضربة واحدة
            DB::table('company_user')
                ->where('user_id', $user->id)
                ->update(['is_active' => false]);

            // 3. ربط الشركة الجديدة وتفعيلها
            $user->companies()->attach($company->id, [
                'role'      => 'owner',
                'is_active' => true,
            ]);

            // 4. تحديث الجلسة
            session(['active_company_id' => $company->id]);
        });

        session()->flash('success', 'تمت إضافة المنشأة وتحديدها كمنشأة نشطة.');
        
        $this->open = false;

        // 5. إعادة التوجيه (Livewire 4 style)
        return $this->redirect(route('dashboard'), navigate: true);
    }

    public function render()
    {
        return view('livewire.company.create-company-modal');
    }
}
