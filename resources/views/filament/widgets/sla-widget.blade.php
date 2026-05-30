<x-filament::card>
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold">SLA Performance</h3>
            <span class="text-sm text-gray-500">آخر 30 يوم</span>
        </div>

        {{-- Response Time --}}
        <div>
            <div class="flex justify-between text-sm mb-1">
                <span>Response Time</span>
                <span class="font-medium">92%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
                <div class="bg-emerald-600 h-2 rounded-full" style="width:92%"></div>
            </div>
        </div>

        {{-- Resolution Time --}}
        <div>
            <div class="flex justify-between text-sm mb-1">
                <span>Resolution Time</span>
                <span class="font-medium">86%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
                <div class="bg-sky-600 h-2 rounded-full" style="width:86%"></div>
            </div>
        </div>
    </div>
</x-filament::card>
