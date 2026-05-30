<div class="min-h-screen bg-slate-50/50 font-['Tajawal']" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    
    @php
        $articlesSafe = $articles ?? collect(); 
        $hasBlogShow = \Illuminate\Support\Facades\Route::has('blog.show');
        $defaultImage = asset('images/placeholder-blog.jpg');
    @endphp

    {{-- Hero Section --}}
    <section class="relative pt-28 pb-24 overflow-hidden bg-gradient-to-b from-white to-slate-50/50">
        {{-- الشبكة الخلفية --}}
        <div class="absolute inset-0 opacity-[0.03] pointer-events-none" 
             style="background-image: radial-gradient(#1FA7A2 1.5px, transparent 1.5px); background-size: 32px 32px;">
        </div>
        
        {{-- إضاءة متوافقة مع الهوية --}}
        <div class="absolute top-[-50%] start-1/2 -translate-x-1/2 rtl:translate-x-1/2 w-[600px] h-[600px] rounded-full bg-[#1FA7A2]/10 blur-[120px] pointer-events-none"></div>

        <div class="container mx-auto px-4 relative z-10 text-center">
            <div class="mb-8 animate__animated animate__fadeInDown">
                <span class="inline-flex items-center px-5 py-2 rounded-full bg-slate-50 border border-slate-200 shadow-sm text-[#1FA7A2] font-bold text-sm">
                    <i class="fas fa-feather-alt me-2" aria-hidden="true"></i> {{ __('blog.hero_badge') }}
                </span>
            </div>

            <h1 class="text-4xl md:text-5xl lg:text-6xl font-black text-slate-800 mb-6 animate__animated animate__fadeInUp tracking-tight">
                {{ __('blog.hero_title_1') }} 
                <span class="bg-clip-text text-transparent bg-gradient-to-br from-[#1FA7A2] to-emerald-500">
                    {{ __('blog.hero_title_2') }}
                </span>
            </h1>

            <p class="text-lg text-slate-500 max-w-2xl mx-auto leading-relaxed font-medium animate__animated animate__fadeInUp delay-100">
                {{ __('blog.hero_description') }}
            </p>
        </div>
    </section>

    {{-- Articles Grid --}}
    <section class="pb-24 pt-8">
        <div class="container mx-auto px-4">
            
            {{-- عنوان القسم --}}
            <div class="flex items-center justify-between mb-12 border-b border-slate-200 pb-5">
                {{-- استخدام border-s-4 للعمل تلقائياً مع اللغتين --}}
                <h2 class="text-2xl font-black text-slate-800 border-s-4 border-[#1FA7A2] ps-4">
                    {{ __('blog.latest_articles') }}
                </h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 xl:gap-10">
                @forelse($articlesSafe as $article)
                    @php
                        $title   = $article->title ?? __('blog.no_title');
                        $content = $article->content ?? '';
                        $img     = $article->image ?? null;
                        $date    = optional($article->created_at)->format('Y/m/d');
                        $showUrl = ($hasBlogShow) ? route('blog.show', $article) : '#';
                        $finalImageUrl = $img ? asset('storage/' . $img) : $defaultImage;
                    @endphp

                    <article wire:key="post-{{ $article->id }}" class="group bg-white rounded-[2rem] border border-slate-100 shadow-sm hover:shadow-2xl hover:shadow-slate-200/50 hover:-translate-y-2 transition-all duration-500 flex flex-col overflow-hidden h-full">
                        
                        {{-- Image Container --}}
                        <figure class="relative aspect-[16/9] w-full overflow-hidden bg-slate-100 shrink-0">
                            <a href="{{ $showUrl }}" class="block h-full w-full" aria-label="{{ $title }}">
                                <img src="{{ $finalImageUrl }}"
                                     @unless($img)
                                         srcset="{{ asset('images/placeholder-blog-600x400.jpg') }} 600w, {{ asset('images/placeholder-blog-800x533.jpg') }} 800w, {{ asset('images/placeholder-blog.jpg') }} 1200w"
                                         sizes="(max-width: 768px) 100vw, 33vw"
                                     @endunless
                                     class="w-full h-full object-cover bg-slate-100 transition-transform duration-700 group-hover:scale-105"
                                     alt="{{ $title }}" loading="lazy">
                            </a>
                            
                            {{-- Category Badge --}}
                            @if($article->category)
                                <span class="absolute top-4 start-4 bg-white/90 backdrop-blur-md text-[#1FA7A2] text-xs font-bold px-3.5 py-1.5 rounded-xl shadow-sm border border-slate-100">
                                    {{ $article->category->name }}
                                </span>
                            @endif
                        </figure>

                        <div class="p-6 md:p-8 flex flex-col flex-grow">
                            <div class="flex items-center gap-2 text-xs text-slate-400 font-semibold mb-4">
                                <i class="far fa-calendar-alt text-[#1FA7A2]" aria-hidden="true"></i>
                                <time datetime="{{ optional($article->created_at)->toDateString() }}">{{ $date }}</time>
                            </div>

                            <h3 class="text-xl font-bold text-slate-800 mb-3 leading-snug">
                                <a href="{{ $showUrl }}" class="hover:text-[#1FA7A2] transition-colors line-clamp-2 focus:outline-none focus:text-[#1FA7A2]">
                                    {{ $title }}
                                </a>
                            </h3>

                            <p class="text-slate-500 text-sm leading-relaxed mb-8 line-clamp-3 flex-grow font-medium">
                                {{ \Illuminate\Support\Str::limit(strip_tags($content), 120) }}
                            </p>

                            <div class="mt-auto pt-5 border-t border-slate-100/60">
                                <a href="{{ $showUrl }}" class="flex items-center justify-between text-sm font-bold text-[#1FA7A2] hover:text-[#167F7B] transition-colors group/link w-full">
                                    <span>{{ __('blog.read_more') }}</span>
                                    <div class="w-8 h-8 rounded-full bg-slate-50 flex items-center justify-center group-hover/link:bg-[#1FA7A2] group-hover/link:text-white transition-all">
                                        {{-- السهم ينقلب تلقائياً في العربي بسبب rtl:-scale-x-100 --}}
                                        <i class="fas fa-arrow-right rtl:-scale-x-100 text-[10px]" aria-hidden="true"></i>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="col-span-full py-24 text-center bg-white rounded-[2rem] border border-slate-100 border-dashed">
                        <div class="inline-flex items-center justify-center w-20 h-20 rounded-2xl bg-slate-50 text-slate-300 mb-6">
                            <i class="fas fa-folder-open text-3xl" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-xl font-bold text-slate-800 mb-2">{{ __('blog.no_articles_found') }}</h3>
                        <p class="text-slate-500 font-medium">{{ __('blog.check_back_later') }}</p>
                    </div>
                @endforelse
            </div>

            {{-- Smart Pagination Logic --}}
            <div class="mt-16 flex flex-col items-center justify-center">
                @if($articlesSafe->count() < $total)
                    <div x-data x-intersect="$wire.loadMore()" class="w-full">
                        <div wire:loading wire:target="loadMore" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 xl:gap-10 w-full mt-8">
                            {{-- Skeleton Loaders (متطابقة مع الكروت الأصلية) --}}
                            @for($i = 0; $i < 3; $i++)
                                <div class="animate-pulse bg-white rounded-[2rem] border border-slate-100 shadow-sm flex flex-col overflow-hidden h-[450px]">
                                    <div class="bg-slate-100 h-60 w-full shrink-0"></div>
                                    <div class="p-6 md:p-8 flex flex-col flex-grow">
                                        <div class="h-3 bg-slate-100 rounded-full w-24 mb-6"></div>
                                        <div class="h-5 bg-slate-200 rounded-lg w-full mb-3"></div>
                                        <div class="h-5 bg-slate-200 rounded-lg w-2/3 mb-6"></div>
                                        <div class="h-3 bg-slate-100 rounded-full w-full mb-2"></div>
                                        <div class="h-3 bg-slate-100 rounded-full w-4/5 mb-8"></div>
                                        <div class="mt-auto pt-5 border-t border-slate-50 flex justify-between items-center">
                                            <div class="h-4 bg-slate-200 rounded-lg w-20"></div>
                                            <div class="w-8 h-8 rounded-full bg-slate-100"></div>
                                        </div>
                                    </div>
                                </div>
                            @endfor
                        </div>
                    </div>
                @elseif($total > 0)
                    <div class="py-10 text-center animate__animated animate__fadeIn">
                        <span class="inline-flex items-center gap-2 text-slate-500 text-sm font-bold bg-white px-6 py-2.5 rounded-full border border-slate-200 shadow-sm">
                            <i class="fas fa-check-circle text-emerald-500" aria-hidden="true"></i>
                            {{ __('blog.all_articles_loaded') }}
                        </span>
                    </div>
                @endif
            </div>

        </div>
    </section>
</div>