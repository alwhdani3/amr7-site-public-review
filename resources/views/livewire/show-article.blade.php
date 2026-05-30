@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-slate-50/50 font-['Tajawal'] relative overflow-x-hidden" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">

    {{-- شريط التقدم للقراءة (استخدام start-0 بدل rtl/ltr) --}}
    <div id="progress-bar" class="fixed top-0 start-0 h-1 bg-gradient-to-r from-[#1FA7A2] to-emerald-400 z-[9999] transition-all duration-150 ease-out w-0 shadow-[0_0_12px_rgba(35,109,111,0.6)]"></div>

    {{-- الخلفية المتحركة (Blobs) --}}
    <div class="fixed inset-0 pointer-events-none z-0">
        <div class="absolute top-0 right-0 w-[600px] h-[600px] bg-[#1FA7A2]/5 rounded-full blur-3xl mix-blend-multiply opacity-60 animate-blob"></div>
        <div class="absolute top-0 left-0 w-[600px] h-[600px] bg-emerald-200/10 rounded-full blur-3xl mix-blend-multiply opacity-60 animate-blob animation-delay-2000"></div>
        <div class="absolute -bottom-32 left-20 w-[600px] h-[600px] bg-slate-200/30 rounded-full blur-3xl mix-blend-multiply opacity-60 animate-blob animation-delay-4000"></div>
        <div class="absolute inset-0 bg-[radial-gradient(#cbd5e1_1px,transparent_1px)] [background-size:32px_32px] opacity-30"></div>
    </div>

    {{-- قسم الهيرو الخاص بالمقال --}}
    <section class="relative z-10 pt-32 pb-12 lg:pt-40 lg:pb-16">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-4xl mx-auto text-center">
                
                <div class="mb-8 animate__animated animate__fadeInDown">
                    <a href="{{ route('blog.index') }}" 
                       class="inline-flex items-center px-6 py-2.5 bg-white/80 backdrop-blur-md border border-slate-200 rounded-full text-slate-600 font-bold shadow-sm hover:bg-[#1FA7A2] hover:text-white hover:border-[#1FA7A2] transition-all duration-300 group">
                        <i class="fas fa-arrow-right rtl:-scale-x-100 me-2 transition-transform group-hover:-translate-x-1 rtl:group-hover:translate-x-1" aria-hidden="true"></i>
                        {{ __('blog.back_to_blog') }}
                    </a>
                </div>

                {{-- تصنيف المقال --}}
                @if($article->category)
                    <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-[#1FA7A2]/5 border border-[#1FA7A2]/10 text-[#1FA7A2] text-sm font-bold mb-6 animate__animated animate__fadeInUp">
                        <i class="fas fa-feather-alt" aria-hidden="true"></i> {{ $article->category->name }}
                    </div>
                @endif

                <h1 class="text-3xl md:text-5xl lg:text-5xl font-black text-slate-900 mb-8 leading-tight animate__animated animate__fadeInUp animate__delay-1s tracking-tight">
                    {{ $article->title }}
                </h1>

                <div class="flex flex-wrap justify-center gap-4 text-slate-500 text-sm font-bold animate__animated animate__fadeInUp animate__delay-1s">
                    <span class="flex items-center gap-2 bg-white/80 backdrop-blur-sm px-4 py-2 rounded-xl border border-slate-100 shadow-sm">
                        <i class="far fa-calendar-alt text-[#1FA7A2]" aria-hidden="true"></i> 
                        <time datetime="{{ $article->created_at->toDateString() }}">{{ $article->created_at->format('Y/m/d') }}</time>
                    </span>
                    {{-- 
                      يمكنك حساب وقت القراءة برمجياً في الـ Controller:
                      $readTime = ceil(str_word_count(strip_tags($article->content)) / 200); 
                    --}}
                    <span class="flex items-center gap-2 bg-white/80 backdrop-blur-sm px-4 py-2 rounded-xl border border-slate-100 shadow-sm">
                        <i class="far fa-clock text-[#1FA7A2]" aria-hidden="true"></i> 
                        {{ __('blog.read_time', ['min' => 6]) }}
                    </span>
                    {{-- إذا كان لديك عداد زيارات --}}
                    {{-- <span class="flex items-center gap-2 bg-white/80 backdrop-blur-sm px-4 py-2 rounded-xl border border-slate-100 shadow-sm">
                        <i class="far fa-eye text-[#1FA7A2]" aria-hidden="true"></i> 
                        {{ __('blog.views_count', ['count' => $article->views ?? 0]) }}
                    </span> --}}
                </div>
            </div>
        </div>
    </section>

    {{-- محتوى المقال --}}
    <section class="pb-16 relative z-10">
        <div class="container mx-auto px-4">
            <div class="max-w-4xl mx-auto">
                
                {{-- الصورة البارزة بتأثير صفحة الهبوط (Glassmorphism) --}}
                @if($article->image)
                    <div class="mb-12 relative animate__animated animate__fadeInUp">
                        <div class="relative bg-white/40 backdrop-blur-xl border border-white/60 rounded-[2.5rem] p-3 shadow-xl shadow-slate-200/40 text-center z-10">
                            <img src="{{ asset('storage/' . $article->image) }}" class="w-full h-auto max-h-[500px] object-cover rounded-[2rem]" alt="{{ $article->title }}">
                        </div>
                        {{-- تأثيرات ضوئية خلف الصورة (تم تخفيفها لتبدو أكثر احترافية) --}}
                        <div class="absolute -top-10 -right-10 w-40 h-40 bg-emerald-400/20 rounded-full blur-3xl animate-pulse z-0"></div>
                        <div class="absolute -bottom-10 -left-10 w-48 h-48 bg-[#1FA7A2]/20 rounded-full blur-3xl animate-pulse z-0"></div>
                    </div>
                @endif

                {{-- النص --}}
                <div class="bg-white rounded-[2rem] p-6 md:p-12 shadow-lg shadow-slate-200/50 border border-slate-100 relative overflow-hidden">
                    {{-- 
                      تحسينات الـ Typography: 
                      - تكبير مسافة السطور (leading-loose)
                      - مسافة أكبر بين الفقرات (prose-p:mb-6)
                      - توسيط الصور داخل المقال (prose-img:mx-auto)
                      - تلوين الروابط بشكل أوضح
                    --}}
                    <article class="prose prose-lg md:prose-xl prose-slate max-w-none 
                                    prose-headings:font-black prose-headings:text-slate-800 
                                    prose-p:text-slate-600 prose-p:leading-loose prose-p:mb-6 prose-p:font-medium
                                    prose-a:text-[#1FA7A2] prose-a:font-bold prose-a:underline-offset-4 hover:prose-a:text-emerald-600
                                    prose-img:rounded-2xl prose-img:mx-auto prose-img:shadow-md
                                    prose-li:text-slate-600 prose-li:marker:text-[#1FA7A2]
                                    animate__animated animate__fadeIn">
                        {!! $article->content !!}
                    </article>

                    {{-- قسم المشاركة والهاشتاجات --}}
                    <div class="mt-16 pt-8 border-t border-slate-100 flex flex-col md:flex-row justify-between items-center gap-6">
                        
                        <div class="flex flex-wrap gap-2 justify-center md:justify-start">
                            {{-- يمكنك جلب التاجات من الداتا بيز إذا كانت متوفرة --}}
                            @php
                                $tags = ['company_formation', 'amr7', 'vision_2030']; // مثال
                            @endphp
                            @foreach($tags as $tag)
                                <span class="px-4 py-1.5 bg-slate-50 text-slate-500 rounded-xl text-xs font-bold border border-slate-100 hover:bg-[#1FA7A2] hover:text-white hover:border-[#1FA7A2] transition-all cursor-pointer">
                                    #{{ __("blog.tags.$tag") }}
                                </span>
                            @endforeach
                        </div>

                        {{-- أزرار المشاركة مع Alpine.js لنسخ الرابط --}}
                        <div class="flex items-center gap-3" x-data="{ shareUrl: window.location.href, copied: false }">
                            <span class="text-slate-400 text-xs font-bold uppercase tracking-wider">{{ __('blog.share_article') }}:</span>
                            
                            <a :href="'https://twitter.com/intent/tweet?url=' + shareUrl + '&text=' + encodeURIComponent('{{ $article->title }}')" target="_blank" rel="noopener noreferrer" class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-50 border border-slate-200 text-slate-500 hover:bg-black hover:text-white hover:-translate-y-1 hover:shadow-md transition-all duration-300" aria-label="Share on X">
                                <i class="fab fa-x-twitter" aria-hidden="true"></i>
                            </a>
                            <a :href="'https://www.linkedin.com/sharing/share-offsite/?url=' + shareUrl" target="_blank" rel="noopener noreferrer" class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-50 border border-slate-200 text-slate-500 hover:bg-[#0077b5] hover:text-white hover:-translate-y-1 hover:shadow-md transition-all duration-300" aria-label="Share on LinkedIn">
                                <i class="fab fa-linkedin-in" aria-hidden="true"></i>
                            </a>
                            <a :href="'https://api.whatsapp.com/send?text=' + encodeURIComponent('{{ $article->title }} - ') + shareUrl" target="_blank" rel="noopener noreferrer" class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-50 border border-slate-200 text-slate-500 hover:bg-[#25D366] hover:text-white hover:-translate-y-1 hover:shadow-md hover:shadow-green-500/20 transition-all duration-300" aria-label="Share on WhatsApp">
                                <i class="fab fa-whatsapp text-lg" aria-hidden="true"></i>
                            </a>
                            
                            {{-- زر نسخ الرابط --}}
                            <button @click="navigator.clipboard.writeText(shareUrl); copied = true; setTimeout(() => copied = false, 2000)" 
                                    class="relative w-10 h-10 flex items-center justify-center rounded-xl bg-slate-50 border border-slate-200 text-slate-500 hover:bg-[#1FA7A2] hover:text-white hover:-translate-y-1 hover:shadow-md transition-all duration-300 focus:outline-none" aria-label="Copy Link">
                                <i class="fas" :class="copied ? 'fa-check text-emerald-400' : 'fa-link'" aria-hidden="true"></i>
                                
                                {{-- رسالة التأكيد (Tooltip) --}}
                                <span x-show="copied" x-transition.opacity class="absolute -top-10 left-1/2 -translate-x-1/2 bg-slate-800 text-white text-[10px] font-bold py-1 px-2 rounded whitespace-nowrap" style="display: none;">
                                    تم النسخ!
                                </span>
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    {{-- فورم الاستشارة (Call to Action) --}}
    <section class="py-16 relative z-10" id="form-section">
        <div class="container mx-auto px-4">
            <div class="max-w-4xl mx-auto">
                <div class="bg-white rounded-[2.5rem] shadow-2xl shadow-slate-200/50 border border-slate-100 p-8 md:p-12 relative overflow-hidden">
                    
                    {{-- شريط متدرج علوي --}}
                    <div class="absolute top-0 start-0 w-full h-1.5 bg-gradient-to-r from-[#1FA7A2] to-emerald-400"></div>
                    
                    <div class="text-center mb-10">
                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-[#1FA7A2]/5 text-[#1FA7A2] mb-4">
                            <i class="fas fa-headset text-2xl" aria-hidden="true"></i>
                        </div>
                        <h2 class="text-3xl font-black text-slate-900 mb-3">استشرنا في موضوع المقال</h2>
                        <p class="text-slate-500 font-medium">سجل بياناتك وسيقوم مستشارنا بالتواصل معك عبر الواتساب فوراً</p>
                    </div>

                    {{-- استدعاء مكون الفورم الخاص بك --}}
                    <livewire:public.landing-company-formation-form />
                    
                </div>
            </div>
        </div>
    </section>

</div>

{{-- يفضل وضع السكربتات في قسم منفصل في الـ layout إذا كان متاحاً (مثل @push('scripts')) --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const progressBar = document.getElementById("progress-bar");
        
        if (progressBar) {
            window.addEventListener('scroll', function() {
                // استخدام requestAnimationFrame لتحسين الأداء (أفضل من حسابها في كل بيكسل سكرول)
                window.requestAnimationFrame(function() {
                    let winScroll = document.body.scrollTop || document.documentElement.scrollTop;
                    let height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
                    let scrolled = (winScroll / height) * 100;
                    progressBar.style.width = scrolled + "%";
                });
            });
        }
    });
</script>
@endsection