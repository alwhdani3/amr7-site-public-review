<?php

use App\Mail\AdminNotificationMail;
use App\Models\Service;
use App\Models\ServiceRequest;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Volt\Component;

new class extends Component {
    public bool $open = false;

    /** 1..5 wizard steps, 6 = success. */
    public int $step = 1;

    public string $name = '';
    public string $phone = '';
    public ?int $service_id = null;
    public string $preferredContactMethod = 'whatsapp';
    public ?string $message = null;

    /** Honeypot. */
    public string $website = '';

    #[Computed]
    public function services(): Collection
    {
        return Service::query()
            ->select(['id', 'title_ar', 'title_en'])
            ->where('is_active', true)
            ->orderBy('id')
            ->get();
    }

    #[On('open-customer-service-chat')]
    public function openFromExternal(): void
    {
        $this->open = true;
    }

    public function toggle(): void
    {
        $this->open = ! $this->open;

        if (! $this->open) {
            $this->resetState();
        }
    }

    public function close(): void
    {
        $this->open = false;
        $this->resetState();
    }

    public function next(): void
    {
        $rules = match ($this->step) {
            1 => ['name' => 'required|string|min:2|max:80'],
            2 => ['phone' => ['required', 'string', 'regex:/^(\+?9665\d{8}|9665\d{8}|05\d{8})$/']],
            3 => ['service_id' => 'required|integer|exists:services,id'],
            4 => ['preferredContactMethod' => 'required|in:whatsapp,phone'],
            5 => ['message' => 'nullable|string|max:2000'],
            default => [],
        };

        $messages = [
            'name.required'                   => 'الاسم مطلوب.',
            'name.min'                        => 'الاسم قصير جدًا.',
            'phone.required'                  => 'رقم الجوال مطلوب.',
            'phone.regex'                     => 'رقم الجوال غير صحيح. يقبل: 05xxxxxxxx أو 9665xxxxxxxx أو +9665xxxxxxxx.',
            'service_id.required'             => 'يرجى اختيار نوع الخدمة.',
            'service_id.exists'               => 'الخدمة المختارة غير موجودة.',
            'preferredContactMethod.required' => 'يرجى اختيار طريقة التواصل المفضلة.',
            'preferredContactMethod.in'       => 'طريقة التواصل غير صحيحة.',
            'message.max'                     => 'الرسالة طويلة جدًا.',
        ];

        if (! empty($rules)) {
            $this->phone = preg_replace('/[\s\-]/', '', (string) $this->phone) ?: $this->phone;
            $this->validate($rules, $messages);
        }

        if ($this->step < 5) {
            $this->step++;
            return;
        }

        $this->submit();
    }

    public function back(): void
    {
        if ($this->step > 1 && $this->step < 6) {
            $this->step--;
        }
    }

    public function submit(): void
    {
        if (! empty($this->website)) {
            return;
        }

        $service = Service::query()->whereKey($this->service_id)->where('is_active', true)->first();
        $serviceTitle = $service?->title_ar ?: $service?->title_en ?: '-';

        $methodLabel = $this->preferredContactMethod === 'phone' ? 'اتصال هاتفي' : 'واتساب';

        $desc = "المصدر: شات الموقع (website_chat)\n"
            . "الاسم: " . $this->name . "\n"
            . "الجوال: " . $this->phone . "\n"
            . "نوع الخدمة: " . $serviceTitle . "\n"
            . "طريقة التواصل المفضلة: " . $methodLabel . "\n"
            . "--------------------------\n"
            . (trim((string) $this->message) !== '' ? trim((string) $this->message) : '(بدون رسالة)');

        try {
            $request = ServiceRequest::create([
                'service_id'               => $this->service_id,
                'user_id'                  => auth()->id(),
                'company_id'               => null,
                'status'                   => 'new',
                'name'                     => $this->name,
                'email'                    => null,
                'applicant_type'           => 'person',
                'phone'                    => $this->phone,
                'description'              => $desc,
                'source'                   => 'website_chat',
                'preferred_contact_method' => $this->preferredContactMethod,
            ]);

            try {
                Mail::to(config('mail.admin_notification_email', 'info@amr-7.sa'))
                    ->send(new AdminNotificationMail($request, null, 'طلب جديد من شات خدمة العملاء'));
            } catch (\Throwable $e) {
                Log::error('CustomerServiceChat mail error', [
                    'request_id' => $request->id ?? null,
                    'message'    => $e->getMessage(),
                ]);
            }

            $this->step = 6;
        } catch (\Throwable $e) {
            Log::error('CustomerServiceChat DB error', [
                'message' => $e->getMessage(),
            ]);

            $this->addError('general', 'حدث خطأ فني، يرجى المحاولة لاحقًا.');
        }
    }

    public function closeAndReset(): void
    {
        $this->open = false;
        $this->resetState();
    }

    protected function resetState(): void
    {
        $this->reset(['name', 'phone', 'service_id', 'message', 'website']);
        $this->preferredContactMethod = 'whatsapp';
        $this->step = 1;
        $this->resetValidation();
    }
}; ?>

@php
    $isRtl = app()->getLocale() === 'ar';
@endphp

<div dir="{{ $isRtl ? 'rtl' : 'ltr' }}" class="font-['Tajawal']">
    {{-- Floating launcher: stacked above Call + WhatsApp cluster.
         Visual order from top to bottom: Chat → Call → WhatsApp. --}}
    <button
        type="button"
        wire:click="toggle"
        aria-label="خدمة العملاء"
        aria-expanded="{{ $open ? 'true' : 'false' }}"
        class="fixed bottom-[150px] end-6 z-[8500] w-12 h-12 rounded-full bg-[#0A2540] hover:bg-[#0c2e50] text-white flex items-center justify-center shadow-lg transition hover:scale-110 print:hidden"
    >
        <span class="sr-only">خدمة العملاء</span>
        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
        </svg>
    </button>

    @if ($open)
        {{-- Lightweight chat panel — no full-screen overlay, no backdrop-blur. --}}
        <div
            role="dialog"
            aria-modal="false"
            aria-labelledby="amr7-csc-title"
            class="fixed z-[8600] print:hidden
                   bottom-20 inset-x-3 w-auto
                   sm:bottom-24 sm:inset-x-auto sm:end-6 sm:w-[min(92vw,420px)]
                   max-h-[75vh] overflow-y-auto
                   bg-white rounded-3xl shadow-2xl border border-slate-100"
        >
            {{-- Header --}}
            <div class="bg-[#0A2540] text-white px-5 py-3.5 flex items-center justify-between sticky top-0 z-10">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-[#1FA7A2]/20 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-[#8EDCEF]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                        </svg>
                    </div>
                    <div>
                        <h6 id="amr7-csc-title" class="m-0 font-black text-sm">خدمة العملاء</h6>
                        <p class="m-0 text-xs text-white/70">نسعد بخدمتك على مدار اليوم</p>
                    </div>
                </div>
                <button type="button" wire:click="close" class="text-white/80 hover:text-white focus:outline-none" aria-label="إغلاق">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M18 6L6 18M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Progress --}}
            @if ($step < 6)
                <div class="px-5 pt-3">
                    <div class="flex items-center justify-between text-[11px] text-slate-500 mb-1.5 font-bold">
                        <span>الخطوة {{ $step }} من 5</span>
                        <span>{{ (int) round(($step / 5) * 100) }}%</span>
                    </div>
                    <div class="h-1.5 bg-slate-100 rounded-full overflow-hidden">
                        <div class="h-full bg-[#1FA7A2] transition-all duration-300" style="width: {{ ($step / 5) * 100 }}%"></div>
                    </div>
                </div>
            @endif

            {{-- Body --}}
            <div class="px-5 py-5">
                {{-- Honeypot --}}
                <div class="hidden" aria-hidden="true">
                    <label>اتركه فارغًا
                        <input type="text" wire:model="website" tabindex="-1" autocomplete="off">
                    </label>
                </div>

                @error('general')
                    <div class="mb-3 rounded-xl border border-red-200 bg-red-50 px-3.5 py-2.5 text-red-700 text-xs font-bold">
                        {{ $message }}
                    </div>
                @enderror

                @if ($step === 1)
                    <label class="block text-xs font-black text-slate-700 mb-1.5">الاسم الكامل</label>
                    <input
                        type="text"
                        wire:model.blur="name"
                        wire:keydown.enter.prevent="next"
                        placeholder="مثال: محمد عبدالله"
                        autocomplete="name"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm text-slate-800 focus:bg-white focus:border-[#1FA7A2] focus:ring-2 focus:ring-[#1FA7A2]/20 focus:outline-none transition"
                    >
                    @error('name')<p class="mt-1.5 text-xs text-red-600 font-bold">{{ $message }}</p>@enderror

                @elseif ($step === 2)
                    <label class="block text-xs font-black text-slate-700 mb-1.5">رقم الجوال</label>
                    <input
                        type="tel"
                        wire:model.blur="phone"
                        wire:keydown.enter.prevent="next"
                        placeholder="05xxxxxxxx"
                        inputmode="tel"
                        autocomplete="tel"
                        dir="ltr"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm text-slate-800 focus:bg-white focus:border-[#1FA7A2] focus:ring-2 focus:ring-[#1FA7A2]/20 focus:outline-none transition"
                    >
                    @error('phone')<p class="mt-1.5 text-xs text-red-600 font-bold">{{ $message }}</p>@enderror
                    <p class="mt-1.5 text-[11px] text-slate-500">يقبل: 05xxxxxxxx أو 9665xxxxxxxx أو +9665xxxxxxxx</p>

                @elseif ($step === 3)
                    <label class="block text-xs font-black text-slate-700 mb-1.5">نوع الخدمة</label>
                    <select
                        wire:model="service_id"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm text-slate-800 focus:bg-white focus:border-[#1FA7A2] focus:ring-2 focus:ring-[#1FA7A2]/20 focus:outline-none transition"
                    >
                        <option value="">— اختر الخدمة —</option>
                        @foreach ($this->services as $srv)
                            <option value="{{ $srv->id }}">{{ $srv->title_ar ?: $srv->title_en }}</option>
                        @endforeach
                    </select>
                    @error('service_id')<p class="mt-1.5 text-xs text-red-600 font-bold">{{ $message }}</p>@enderror

                @elseif ($step === 4)
                    <label class="block text-xs font-black text-slate-700 mb-2">طريقة التواصل المفضلة</label>
                    <div class="grid grid-cols-2 gap-2">
                        <label class="cursor-pointer">
                            <input type="radio" wire:model="preferredContactMethod" value="whatsapp" class="sr-only peer">
                            <div class="border-2 border-slate-200 rounded-xl px-3 py-3 text-center text-sm font-bold text-slate-700 peer-checked:border-[#1FA7A2] peer-checked:bg-[#1FA7A2]/5 peer-checked:text-[#0A2540] transition">
                                واتساب
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" wire:model="preferredContactMethod" value="phone" class="sr-only peer">
                            <div class="border-2 border-slate-200 rounded-xl px-3 py-3 text-center text-sm font-bold text-slate-700 peer-checked:border-[#1FA7A2] peer-checked:bg-[#1FA7A2]/5 peer-checked:text-[#0A2540] transition">
                                اتصال هاتفي
                            </div>
                        </label>
                    </div>
                    @error('preferredContactMethod')<p class="mt-1.5 text-xs text-red-600 font-bold">{{ $message }}</p>@enderror

                @elseif ($step === 5)
                    <label class="block text-xs font-black text-slate-700 mb-1.5">رسالتك <span class="font-normal text-slate-400">(اختياري)</span></label>
                    <textarea
                        wire:model.blur="message"
                        rows="4"
                        placeholder="اكتب أي تفاصيل تساعدنا..."
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm text-slate-800 focus:bg-white focus:border-[#1FA7A2] focus:ring-2 focus:ring-[#1FA7A2]/20 focus:outline-none transition resize-none"
                    ></textarea>
                    @error('message')<p class="mt-1.5 text-xs text-red-600 font-bold">{{ $message }}</p>@enderror

                @elseif ($step === 6)
                    <div
                        class="text-center py-3"
                        x-data
                        x-init="setTimeout(() => $wire.closeAndReset(), 2000)"
                    >
                        <div class="w-14 h-14 mx-auto rounded-full bg-emerald-100 flex items-center justify-center mb-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <polyline points="20 6 9 17 4 12"/>
                            </svg>
                        </div>
                        <h6 class="font-black text-slate-800 text-base mb-1">تم استلام طلبك!</h6>
                        <p class="text-xs text-slate-500 m-0">سيتواصل معك فريق آمر سبعة قريبًا.</p>
                    </div>
                @endif
            </div>

            {{-- Footer / actions (hidden on success step). --}}
            @if ($step < 6)
                <div class="px-5 pb-5 pt-1">
                    <div class="flex items-center gap-2">
                        @if ($step > 1)
                            <button
                                type="button"
                                wire:click="back"
                                class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 text-sm font-bold hover:bg-slate-50 transition"
                            >
                                رجوع
                            </button>
                        @endif
                        <button
                            type="button"
                            wire:click="next"
                            wire:loading.attr="disabled"
                            class="flex-1 px-4 py-2.5 rounded-xl bg-[#0A2540] hover:bg-[#0c2e50] text-white text-sm font-black transition disabled:opacity-60 disabled:cursor-not-allowed"
                        >
                            <span wire:loading.remove wire:target="next">
                                {{ $step === 5 ? 'إرسال الطلب' : 'التالي' }}
                            </span>
                            <span wire:loading wire:target="next">جاري الإرسال...</span>
                        </button>
                    </div>
                </div>
            @endif
        </div>
    @endif
</div>
