<x-filament-panels::page>
    <div class="space-y-6" dir="rtl">

        {{-- 1. الهيدر المطور --}}
        <div class="relative overflow-hidden rounded-2xl border bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="relative z-10 flex items-start justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white flex items-center gap-2">
                        <x-heroicon-s-cpu-chip class="w-8 h-8 text-primary-600" />
                        المستشار الذكي (AI)
                    </h1>
                    <p class="mt-2 text-sm leading-6 text-gray-600 dark:text-gray-300 max-w-2xl">
                        منصة الإدارة المتطورة لصياغة العقود وتحليل المستندات القانونية. ارفع ملفك وسيقوم الوكيل المرتبط بـ n8n بمعالجته فوراً.
                    </p>
                </div>

                <div class="hidden sm:block">
                    <span class="inline-flex items-center gap-1 rounded-full bg-primary-50 px-3 py-1 text-xs font-medium text-primary-700 dark:bg-primary-950 dark:text-primary-200">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-primary-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-primary-500"></span>
                        </span>
                        Internal Admin Only
                    </span>
                </div>
            </div>
            {{-- تأثير خلفية خفيف --}}
            <div class="absolute -right-10 -top-10 h-32 w-32 rounded-full bg-primary-50/50 dark:bg-primary-900/10"></div>
        </div>

        {{-- 2. الجسم الرئيسي --}}
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

            {{-- نموذج الإدخال --}}
            <div class="lg:col-span-2">
                <div class="rounded-2xl border bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900 h-full">
                    <div class="flex items-center gap-2 mb-6 border-b pb-4">
                        <x-heroicon-o-pencil-square class="w-5 h-5 text-gray-400" />
                        <h2 class="text-base font-semibold text-gray-900 dark:text-white">إدخال بيانات التحليل</h2>
                    </div>

                    <div class="mt-6">
                        {{ $this->form }}
                    </div>

                    <div class="mt-8 flex items-center gap-3 border-t pt-6">
                        <x-filament::button
                            size="lg"
                            icon="heroicon-o-play"
                            wire:click="runAgent"
                            wire:loading.attr="disabled"
                            wire:target="runAgent"
                            class="min-w-[140px]"
                        >
                            {{-- حالة التحميل --}}
                            <span wire:loading.remove wire:target="runAgent">تشغيل الوكيل</span>
                            <span wire:loading wire:target="runAgent" class="flex items-center gap-2">
                                <x-filament::loading-indicator class="h-4 w-4" />
                                جاري المعالجة...
                            </span>
                        </x-filament::button>

                        <x-filament::button
                            color="gray"
                            variant="outline"
                            icon="heroicon-o-arrow-path"
                            wire:click="$refresh"
                        >
                            تحديث الصفحة
                        </x-filament::button>
                    </div>
                </div>
            </div>

            {{-- الجانب المعلوماتي (الإرشادات) --}}
            <div class="space-y-6">
                <x-filament::section icon="heroicon-o-information-circle" icon-color="primary">
                    <x-slot name="heading">إرشادات سريعة</x-slot>
                    <ul class="space-y-3 text-sm text-gray-600 dark:text-gray-400">
                        <li class="flex gap-2">
                            <x-heroicon-m-check-circle class="w-5 h-5 text-green-500 shrink-0" />
                            ارفع ملف PDF أو صورة واضحة لضمان دقة القراءة.
                        </li>
                        <li class="flex gap-2">
                            <x-heroicon-m-check-circle class="w-5 h-5 text-green-500 shrink-0" />
                            حدد نوع الخدمة المطلوبة بدقة (تلخيص، صياغة، تدقيق).
                        </li>
                        <li class="flex gap-2">
                            <x-heroicon-m-check-circle class="w-5 h-5 text-green-500 shrink-0" />
                            النتائج تظهر في الأسفل فور انتهاء معالجة n8n.
                        </li>
                    </ul>
                </x-filament::section>

                <x-filament::section icon="heroicon-o-shield-check" icon-color="warning">
                    <x-slot name="heading">ملاحظات أمنية</x-slot>
                    <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed">
                        تأكد أن الـ Webhook محمي بمفتاح
                        <code class="rounded bg-amber-50 px-1.5 py-0.5 text-xs text-amber-700 dark:bg-amber-900/20 dark:text-amber-400 font-mono">N8N_API_KEY</code>
                        لمنع الوصول غير المصرح به.
                    </p>
                </x-filament::section>
            </div>
        </div>

        {{-- 3. قسم النتيجة المطور --}}
        <div class="rounded-2xl border bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
            <div class="bg-gray-50/50 px-6 py-4 border-b dark:bg-gray-800/50 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-document-text class="w-5 h-5 text-primary-600" />
                    <h2 class="text-base font-semibold text-gray-900 dark:text-white">نتيجة التحليل والصياغة</h2>
                </div>

                @if(!empty($result))
                    <div class="flex items-center gap-2">
                        {{-- زر النسخ --}}
                        <x-filament::button
                            color="gray"
                            size="sm"
                            icon="heroicon-o-clipboard-document"
                            x-on:click="window.navigator.clipboard.writeText(`{{ is_array($result) ? json_encode($result) : $result }}`); $tooltip('تم النسخ', { timeout: 2000 })"
                        >
                            نسخ النتيجة
                        </x-filament::button>

                        <span class="inline-flex items-center rounded-full bg-green-50 px-3 py-1 text-xs font-medium text-green-700 dark:bg-green-950 dark:text-green-200">
                            تمت المعالجة بنجاح
                        </span>
                    </div>
                @else
                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                        بانتظار التشغيل
                    </span>
                @endif
            </div>

            <div class="p-6">
                @if(!empty($result))
                    <div class="prose max-w-none dark:prose-invert">
                        {{-- تنسيق عرض النتيجة --}}
                        <div class="rounded-xl bg-gray-50 p-6 text-sm text-gray-900 dark:bg-gray-950 dark:text-gray-100 border font-mono leading-relaxed">
                            {!! is_array($result)
                                ? '<pre class="whitespace-pre-wrap">' . e(json_encode($result, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT)) . '</pre>'
                                : nl2br(e($result)) !!}
                        </div>
                    </div>
                @else
                    <div class="flex flex-col items-center justify-center py-12 text-center">
                        <x-heroicon-o-beaker class="w-12 h-12 text-gray-200 mb-4" />
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            لا توجد نتائج لعرضها حالياً. قم بتعبئة النموذج أعلاه وتشغيل الوكيل الذكي.
                        </p>
                    </div>
                @endif
            </div>
        </div>

    </div>
</x-filament-panels::page>
