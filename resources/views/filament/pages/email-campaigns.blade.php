<x-filament-panels::page>
    <div dir="rtl" class="space-y-6">
        <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
            <strong>تنبيه مهم:</strong>
            ابدأ بتجربة 1 إلى 30 بريد فقط. لا ترسل كامل القائمة دفعة واحدة، خصوصًا أن SMTP الحالي عبر Gmail.
        </div>

        <form wire:submit.prevent="queueCampaign" class="space-y-6">
            <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900">
                <div class="grid gap-6">
                    <div>
                        <label class="block text-sm font-medium mb-2">ملف CSV</label>
                        <input type="file" wire:model="csv_file" accept=".csv,.txt" class="block w-full rounded-lg border border-gray-300 p-2 text-sm">
                        @error('csv_file') <div class="mt-2 text-sm text-red-600">{{ $message }}</div> @enderror
                        <p class="mt-2 text-xs text-gray-500">لازم يحتوي الملف على عمود email، ويمكن إضافة name اختياريًا.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2">عنوان الرسالة</label>
                        <input type="text" wire:model="subject" class="block w-full rounded-lg border border-gray-300 p-2 text-sm">
                        @error('subject') <div class="mt-2 text-sm text-red-600">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2">نص الرسالة</label>
                        <textarea wire:model="body" rows="10" class="block w-full rounded-lg border border-gray-300 p-2 text-sm"></textarea>
                        @error('body') <div class="mt-2 text-sm text-red-600">{{ $message }}</div> @enderror
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium mb-2">أقصى عدد للإرسال الآن</label>
                            <input type="number" wire:model="max_recipients" min="1" max="2000" class="block w-full rounded-lg border border-gray-300 p-2 text-sm">
                            @error('max_recipients') <div class="mt-2 text-sm text-red-600">{{ $message }}</div> @enderror
                            <p class="mt-2 text-xs text-gray-500">ابدأ بـ 1 أو 30 فقط للتجربة.</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2">الفاصل بين كل رسالة بالثواني</label>
                            <input type="number" wire:model="seconds_between_messages" min="10" max="600" class="block w-full rounded-lg border border-gray-300 p-2 text-sm">
                            @error('seconds_between_messages') <div class="mt-2 text-sm text-red-600">{{ $message }}</div> @enderror
                            <p class="mt-2 text-xs text-gray-500">الأفضل 30 إلى 60 ثانية مع SMTP الحالي.</p>
                        </div>
                    </div>
                </div>
            </div>

            <x-filament::button type="submit" icon="heroicon-o-paper-airplane" wire:loading.attr="disabled">
                جدولة الإرسال
            </x-filament::button>
        </form>
    </div>
</x-filament-panels::page>
