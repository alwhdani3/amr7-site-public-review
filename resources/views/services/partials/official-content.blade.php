@php
    use App\Support\Seo\InternalLinkGuard;

    $officialContent = $officialContent ?? null;
    $officialLinks = $officialContent ? InternalLinkGuard::cleanRelatedLinks($officialContent['related_links'] ?? []) : [];
    $locale = app()->getLocale() === 'en' ? 'en' : 'ar';
    $isAr = $locale === 'ar';

    $labels = $isAr
        ? [
            'who_needs' => 'من يحتاجها',
            'requirements' => 'المتطلبات',
            'documents' => 'المستندات المطلوبة',
            'conditions' => 'الشروط',
            'steps' => 'الخطوات',
            'duration' => 'المدة التقريبية',
            'authorities' => 'الجهات المرتبطة',
            'faqs' => 'الأسئلة الشائعة',
            'related' => 'خدمات ومقالات مرتبطة',
            'official_sources' => 'المصادر الرسمية',
            'source_notes' => 'ملاحظة تحقق',
        ]
        : [
            'who_needs' => 'Who needs it',
            'requirements' => 'Requirements',
            'documents' => 'Required documents',
            'conditions' => 'Conditions',
            'steps' => 'Steps',
            'duration' => 'Approximate duration',
            'authorities' => 'Related authorities',
            'faqs' => 'FAQs',
            'related' => 'Related services and articles',
            'official_sources' => 'Official sources',
            'source_notes' => 'Verification note',
        ];

    $sectionMap = [
        'who_needs' => ['icon' => 'fa-users', 'items' => $officialContent['who_needs'] ?? []],
        'requirements' => ['icon' => 'fa-list-check', 'items' => $officialContent['requirements'] ?? []],
        'documents' => ['icon' => 'fa-folder-open', 'items' => $officialContent['documents'] ?? []],
        'conditions' => ['icon' => 'fa-scale-balanced', 'items' => $officialContent['conditions'] ?? []],
    ];
@endphp

@if($officialContent)
    <section class="mt-8 bg-white border border-slate-200 rounded-3xl p-6 md:p-8 shadow-sm" id="official-service-guide">
        <div class="mb-8">
            @if(!empty($officialContent['eyebrow']))
                <p class="text-xs font-black uppercase tracking-wider text-[#1FA7A2] mb-2">{{ $officialContent['eyebrow'] }}</p>
            @endif
            <h2 class="text-2xl md:text-3xl font-black text-slate-900 mb-3">{{ $officialContent['title'] }}</h2>
            <p class="text-slate-600 leading-relaxed">{{ $officialContent['summary'] }}</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            @foreach($sectionMap as $key => $section)
                @if(count($section['items']) > 0)
                    <div class="rounded-2xl border border-slate-100 bg-slate-50 p-5">
                        <h3 class="flex items-center gap-2 text-base font-black text-slate-800 mb-4">
                            <i class="fas {{ $section['icon'] }} text-[#1FA7A2]" aria-hidden="true"></i>
                            {{ $labels[$key] }}
                        </h3>
                        <ul class="space-y-3">
                            @foreach($section['items'] as $item)
                                <li class="flex gap-3 text-sm text-slate-600 leading-relaxed">
                                    <span class="mt-1 h-2 w-2 rounded-full bg-[#1FA7A2] shrink-0"></span>
                                    <span>{{ $item }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            @endforeach
        </div>

        @if(!empty($officialContent['steps']))
            <div class="mt-6 rounded-2xl border border-slate-100 bg-white p-5">
                <h3 class="flex items-center gap-2 text-base font-black text-slate-800 mb-5">
                    <i class="fas fa-route text-[#1FA7A2]" aria-hidden="true"></i>
                    {{ $labels['steps'] }}
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @foreach($officialContent['steps'] as $step)
                        <div class="rounded-xl bg-slate-50 p-4 border border-slate-100">
                            <div class="text-xs font-black text-[#1FA7A2] mb-2">{{ $loop->iteration }}</div>
                            <h4 class="font-bold text-slate-800 mb-1">{{ $step['title'] }}</h4>
                            <p class="text-xs text-slate-500 leading-relaxed">{{ $step['description'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="mt-6 grid grid-cols-1 lg:grid-cols-2 gap-5">
            @if(!empty($officialContent['duration']))
                <div class="rounded-2xl border border-teal-100 bg-[#f0fdfa] p-5">
                    <h3 class="flex items-center gap-2 text-base font-black text-[#1FA7A2] mb-2">
                        <i class="far fa-clock" aria-hidden="true"></i>
                        {{ $labels['duration'] }}
                    </h3>
                    <p class="text-sm text-teal-800/80 leading-relaxed">{{ $officialContent['duration'] }}</p>
                </div>
            @endif

            @if(!empty($officialContent['authorities']))
                <div class="rounded-2xl border border-slate-100 bg-slate-50 p-5">
                    <h3 class="flex items-center gap-2 text-base font-black text-slate-800 mb-3">
                        <i class="fas fa-building-columns text-[#1FA7A2]" aria-hidden="true"></i>
                        {{ $labels['authorities'] }}
                    </h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach($officialContent['authorities'] as $authority)
                            <a href="{{ $authority['url'] }}" target="_blank" rel="noopener noreferrer"
                               class="inline-flex rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-bold text-slate-600 hover:text-[#1FA7A2] hover:border-[#1FA7A2]/30">
                                {{ $authority['name'] }}
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        @if(!empty($officialContent['faqs']))
            <div class="mt-6 rounded-2xl border border-slate-100 bg-white p-5">
                <h3 class="flex items-center gap-2 text-base font-black text-slate-800 mb-4">
                    <i class="fas fa-circle-question text-[#1FA7A2]" aria-hidden="true"></i>
                    {{ $labels['faqs'] }}
                </h3>
                <div class="space-y-3">
                    @foreach($officialContent['faqs'] as $faq)
                        <details class="group rounded-xl border border-slate-100 bg-slate-50 p-4">
                            <summary class="cursor-pointer list-none font-bold text-slate-800 group-open:text-[#1FA7A2]">
                                {{ $faq['question'] }}
                            </summary>
                            <p class="mt-3 text-sm leading-relaxed text-slate-600">{{ $faq['answer'] }}</p>
                        </details>
                    @endforeach
                </div>
            </div>
        @endif

        @if(count($officialLinks) > 0)
            <div class="mt-6 rounded-2xl border border-slate-100 bg-slate-50 p-5">
                <h3 class="flex items-center gap-2 text-base font-black text-slate-800 mb-4">
                    <i class="fas fa-link text-[#1FA7A2]" aria-hidden="true"></i>
                    {{ $labels['related'] }}
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    @foreach($officialLinks as $link)
                        <a href="{{ $link['url'] }}" class="rounded-xl border border-slate-100 bg-white p-4 hover:border-[#1FA7A2]/30 hover:shadow-sm transition">
                            <span class="block text-sm font-black text-slate-800">{{ $link['label'] }}</span>
                            @if($link['description'])
                                <span class="mt-1 block text-xs leading-relaxed text-slate-500">{{ $link['description'] }}</span>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="mt-6 flex flex-col md:flex-row gap-4 text-xs text-slate-500">
            @if(!empty($officialContent['official_sources']))
                <div class="flex-1 rounded-2xl border border-slate-100 bg-white p-4">
                    <span class="font-black text-slate-700">{{ $labels['official_sources'] }}:</span>
                    {{ implode('، ', $officialContent['official_sources']) }}
                </div>
            @endif
            @if(!empty($officialContent['source_notes']))
                <div class="flex-1 rounded-2xl border border-amber-100 bg-amber-50 p-4 text-amber-800">
                    <span class="font-black">{{ $labels['source_notes'] }}:</span>
                    {{ $officialContent['source_notes'] }}
                </div>
            @endif
        </div>
    </section>
@endif
