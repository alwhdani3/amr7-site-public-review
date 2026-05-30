<x-filament-panels::page>
    <div dir="rtl" class="space-y-8">
        {{-- Read-only banner --}}
        <div class="rounded-xl border border-sky-200 bg-sky-50 p-4 text-sm text-sky-900 dark:border-sky-900/50 dark:bg-sky-950/40 dark:text-sky-200">
            <div class="flex items-start gap-3">
                <x-filament::icon
                    icon="heroicon-o-information-circle"
                    class="mt-0.5 h-5 w-5 flex-shrink-0 text-sky-600 dark:text-sky-300"
                />
                <div class="space-y-1">
                    <div class="font-semibold">Read-only Dashboard — Snapshots</div>
                    <div class="leading-relaxed">
                        كروت <span class="font-mono">Live (DB)</span> / <span class="font-mono">Live (file)</span> تقرأ مباشرة من قاعدة البيانات أو ملف محلي. باقي الكروت تقرأ آخر <strong>JSON snapshot</strong> دُفع عبر الـ internal API من المصدر الموثوق (n8n، probe، monitor). الحالات: <span class="font-mono">Healthy</span> / <span class="font-mono">Warning</span> / <span class="font-mono">Critical</span> / <span class="font-mono">No data</span>. لا اتصالات خارجية لحظية من هذه الصفحة، ولا أزرار خطرة.
                    </div>
                </div>
            </div>
        </div>

        @foreach ($this->getSections() as $section)
            @php
                $sectionCards = $section['cards'] ?? [];
            @endphp

            <section wire:key="cc-section-{{ $section['key'] }}" class="space-y-4">
                {{-- Section header --}}
                <div class="flex items-center gap-3">
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-gray-100 text-gray-700 dark:bg-white/5 dark:text-gray-300">
                        <x-filament::icon
                            :icon="$section['icon']"
                            class="h-5 w-5"
                        />
                    </div>
                    <div class="min-w-0">
                        <div class="text-base font-semibold text-gray-950 dark:text-white">
                            {{ $section['title'] }}
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">
                            {{ $section['subtitle'] }}
                        </div>
                    </div>
                    <div class="ml-auto rtl:ml-0 rtl:mr-auto text-[11px] uppercase tracking-wider text-gray-400 dark:text-gray-500">
                        {{ count($sectionCards) }} {{ count($sectionCards) === 1 ? 'card' : 'cards' }}
                    </div>
                </div>

                {{-- Cards grid --}}
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach ($sectionCards as $card)
                        @php
                            $accent = $card['accent'] ?? 'gray';
                            $iconClasses = match ($accent) {
                                'indigo'  => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-300',
                                'emerald' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300',
                                'sky'     => 'bg-sky-100 text-sky-700 dark:bg-sky-500/10 dark:text-sky-300',
                                'cyan'    => 'bg-cyan-100 text-cyan-700 dark:bg-cyan-500/10 dark:text-cyan-300',
                                'amber'   => 'bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300',
                                'violet'  => 'bg-violet-100 text-violet-700 dark:bg-violet-500/10 dark:text-violet-300',
                                'rose'    => 'bg-rose-100 text-rose-700 dark:bg-rose-500/10 dark:text-rose-300',
                                'red'     => 'bg-red-100 text-red-700 dark:bg-red-500/10 dark:text-red-300',
                                default   => 'bg-gray-100 text-gray-700 dark:bg-gray-500/10 dark:text-gray-300',
                            };

                            $statusClasses = match ($card['status'] ?? 'pending') {
                                'ok'      => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300',
                                'warning' => 'bg-amber-100 text-amber-800 dark:bg-amber-500/10 dark:text-amber-300',
                                'error'   => 'bg-red-100 text-red-700 dark:bg-red-500/10 dark:text-red-300',
                                default   => 'bg-gray-100 text-gray-600 dark:bg-gray-500/10 dark:text-gray-300',
                            };
                        @endphp

                        <div
                            wire:key="cc-card-{{ $section['key'] }}-{{ $card['key'] }}"
                            class="flex h-full flex-col rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10"
                        >
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex min-w-0 items-center gap-3">
                                    <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-lg {{ $iconClasses }}">
                                        <x-filament::icon
                                            :icon="$card['icon']"
                                            class="h-5 w-5"
                                        />
                                    </div>
                                    <div class="min-w-0">
                                        <div class="truncate text-sm font-semibold text-gray-950 dark:text-white">
                                            {{ $card['title'] }}
                                        </div>
                                        <div class="truncate text-xs text-gray-500 dark:text-gray-400">
                                            {{ $card['subtitle'] }}
                                        </div>
                                    </div>
                                </div>

                                <span class="inline-flex shrink-0 items-center rounded-full px-2 py-0.5 text-[11px] font-medium {{ $statusClasses }}">
                                    {{ $card['status_label'] }}
                                </span>
                            </div>

                            <div class="mt-4">
                                <div class="text-3xl font-bold tracking-tight text-gray-950 dark:text-white">
                                    {{ $card['value'] }}
                                </div>
                            </div>

                            <div class="mt-3 text-xs leading-relaxed text-gray-500 dark:text-gray-400">
                                {{ $card['description'] }}
                            </div>

                            @if(! empty($card['received_at']))
                                <div class="mt-3 flex items-center gap-1.5 text-[11px] text-gray-400 dark:text-gray-500">
                                    <x-filament::icon
                                        icon="heroicon-o-clock"
                                        class="h-3.5 w-3.5"
                                    />
                                    آخر تحديث: {{ \Illuminate\Support\Carbon::parse($card['received_at'])->diffForHumans() }}
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </section>
        @endforeach

        {{-- Footer note --}}
        <div class="rounded-xl border border-dashed border-gray-300 bg-gray-50 p-4 text-xs text-gray-600 dark:border-gray-700 dark:bg-gray-900/40 dark:text-gray-400">
            مصادر الـ snapshots المسموح بها: <span class="font-mono">n8n</span> / <span class="font-mono">whatsapp</span> / <span class="font-mono">server</span> / <span class="font-mono">websites</span> / <span class="font-mono">ssl</span> / <span class="font-mono">backups</span> / <span class="font-mono">security</span>. كل مصدر يدفع JSON عبر <span class="font-mono">POST /api/internal/amr7/command-center/snapshots/{source}</span> مع header <span class="font-mono">X-AMR7-SITE-TOKEN</span>. راجع <span class="font-mono">docs/command-center-snapshots.md</span> للأمثلة.
        </div>
    </div>
</x-filament-panels::page>
