@php
    /** @var \App\Models\User $record */
    $companyIds = $record->companies()->pluck('companies.id')->toArray();

    $tickets = \App\Models\Ticket::query()
        ->with(['company', 'department', 'attachments'])
        ->when(!empty($companyIds), fn ($q) => $q->whereIn('company_id', $companyIds))
        ->latest()
        ->limit(30)
        ->get();
@endphp

<div class="rounded-xl border border-gray-700/50 p-4">
    <div class="flex items-center justify-between mb-3">
        <div class="text-lg font-bold">تذاكر الشركات المرتبطة</div>
        <a class="fi-btn fi-btn-color-primary fi-btn-size-sm"
           href="{{ \App\Filament\Resources\TicketResource::getUrl('index') }}">
            فتح التذاكر
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="opacity-70">
            <tr>
                <th class="text-right py-2">#</th>
                <th class="text-right py-2">الشركة</th>
                <th class="text-right py-2">العنوان</th>
                <th class="text-right py-2">الحالة</th>
                <th class="text-right py-2">مرفقات</th>
                <th class="text-right py-2">فتح</th>
            </tr>
            </thead>
            <tbody>
            @forelse($tickets as $t)
                <tr class="border-t border-gray-700/30">
                    <td class="py-2">{{ $t->ticket_number }}</td>
                    <td class="py-2">{{ $t->company?->name ?? '-' }}</td>
                    <td class="py-2 font-semibold">{{ \Illuminate\Support\Str::limit($t->subject, 40) }}</td>
                    <td class="py-2">{{ $t->status }}</td>
                    <td class="py-2">{{ $t->attachments?->count() ?? 0 }}</td>
                    <td class="py-2">
                        <a class="underline"
                           href="{{ \App\Filament\Resources\TicketResource::getUrl('edit', ['record' => $t->id]) }}">
                            عرض
                        </a>
                    </td>
                </tr>
            @empty
                <tr class="border-t border-gray-700/30">
                    <td class="py-3 opacity-70" colspan="6">لا توجد تذاكر</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
