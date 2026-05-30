@php
    /** @var \App\Models\User $record */
    $record->loadMissing(['companies.documents']);
@endphp

<div class="space-y-4">
    @forelse($record->companies as $company)
        <div class="rounded-xl border border-gray-700/50 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-lg font-bold">{{ $company->name }}</div>
                    <div class="text-sm opacity-70">
                        الرقم الموحد: {{ $company->unified_number ?? '-' }} | الضريبي: {{ $company->tax_number ?? '-' }}
                    </div>
                </div>

                <a class="fi-btn fi-btn-color-primary fi-btn-size-sm"
                   href="{{ \App\Filament\Resources\CompanyResource::getUrl('edit', ['record' => $company->id]) }}">
                    فتح الشركة
                </a>
            </div>

            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="opacity-70">
                    <tr>
                        <th class="text-right py-2">الوثيقة</th>
                        <th class="text-right py-2">الحالة</th>
                        <th class="text-right py-2">ملف</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($company->documents as $doc)
                        <tr class="border-t border-gray-700/30">
                            <td class="py-2 font-semibold">{{ $doc->type ?? '-' }}</td>
                            <td class="py-2">{{ $doc->status ?? '-' }}</td>
                            <td class="py-2">
                                @if(!empty($doc->file_path))
                                    <a class="underline" target="_blank" href="{{ route('company.docs.download', $doc) }}">عرض</a>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr class="border-t border-gray-700/30">
                            <td class="py-3 opacity-70" colspan="3">لا توجد وثائق لهذه الشركة</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @empty
        <div class="rounded-xl border border-gray-700/50 p-4 opacity-70">
            لا توجد شركات مرتبطة بهذا العضو.
        </div>
    @endforelse
</div>