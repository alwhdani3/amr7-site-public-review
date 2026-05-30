@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-slate-50 font-['Tajawal'] relative overflow-x-hidden" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">

    {{-- شريط التقدم للقراءة --}}
    <div id="progress-bar" class="fixed top-0 right-0 h-1.5 bg-gradient-to-r from-[#1FA7A2] to-teal-400 z-[100] transition-all duration-100 ease-out w-0 shadow-[0_0_10px_rgba(35,109,111,0.5)]"></div>

    {{-- الخلفية المتحركة (هوية آمر سبعة) --}}
    <div class="fixed inset-0 pointer-events-none z-0">
        <div class="absolute top-0 right-0 w-[600px] h-[600px] bg-[#1FA7A2]/5 rounded-full blur-3xl mix-blend-multiply opacity-70 animate-blob"></div>
        <div class="absolute top-0 left-0 w-[600px] h-[600px] bg-teal-200/10 rounded-full blur-3xl mix-blend-multiply opacity-70 animate-blob animation-delay-2000"></div>
        <div class="absolute -bottom-32 left-20 w-[600px] h-[600px] bg-slate-200/20 rounded-full blur-3xl mix-blend-multiply opacity-70 animate-blob animation-delay-4000"></div>
        <div class="absolute inset-0 bg-[radial-gradient(#e5e7eb_1px,transparent_1px)] [background-size:32px_32px] opacity-40"></div>
    </div>

    {{-- عنوان المقال (الهيرو) --}}
    <section class="relative z-10 pt-32 pb-10 lg:pt-40 lg:pb-12 border-b border-slate-200/60 bg-white/40 backdrop-blur-md">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="mb-6 animate__animated animate__fadeInDown">
                <a href="{{ route('blog.index') ?? '/blog' }}" 
                   class="inline-flex items-center px-6 py-2.5 bg-white border border-slate-200 rounded-full text-[#1FA7A2] font-bold shadow-sm hover:bg-[#1FA7A2] hover:text-white hover:border-[#1FA7A2] transition-all duration-300 transform hover:-translate-x-1">
                    <i class="fas fa-arrow-right mx-2"></i> العودة للمدونة
                </a>
            </div>
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-[#1FA7A2]/5 border border-[#1FA7A2]/10 text-[#1FA7A2] text-xs font-bold mb-4 animate__animated animate__fadeInUp">
<i class="fas fa-folder-open"></i> {{ $post->category->name ?? 'عام' }}</div>
            <h1 class="text-3xl md:text-5xl font-black text-slate-900 mb-6 leading-tight max-w-4xl mx-auto animate__animated animate__fadeInUp animate__delay-1s">
                {{ $post->title }}
            </h1>
            <div class="flex flex-wrap justify-center gap-6 text-slate-500 text-sm font-bold animate__animated animate__fadeInUp animate__delay-1s">
                <span class="flex items-center gap-2"><i class="far fa-calendar-alt text-[#1FA7A2]"></i> {{ $post->created_at ? $post->created_at->format('Y/m/d') : now()->format('Y/m/d') }}</span>
                <span class="flex items-center gap-2"><i class="far fa-user text-[#1FA7A2]"></i> بواسطة فريق آمر سبعة</span>
                <span class="flex items-center gap-2"><i class="far fa-clock text-[#1FA7A2]"></i> 5 دقائق قراءة</span>
            </div>
        </div>
    </section>

    {{-- محتوى الصفحة (نظام العمودين المستوحى من إتمام بهوية آمر سبعة) --}}
    <section class="py-12 relative z-10">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                
                {{-- العمود الأول: المقال (يأخذ 8 أعمدة من أصل 12) --}}
                <div class="lg:col-span-8">
                    {{-- الصورة البارزة --}}
                    @if($post->image)
                        <div class="mb-10 relative animate__animated animate__fadeInUp">
                            <div class="relative bg-white/60 backdrop-blur-xl border border-white/50 rounded-[2.5rem] p-3 shadow-2xl shadow-slate-200/50 z-10">
                                <img src="{{ asset('storage/' . $post->image) }}" class="w-full h-auto object-cover rounded-[2rem]" alt="{{ $post->title }}">
                            </div>
                        </div>
                    @endif

                    {{-- نص المقال --}}
                    <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] p-8 md:p-10 shadow-xl shadow-slate-200/40 border border-white/60 relative overflow-hidden">
                        <article class="prose prose-lg prose-slate max-w-none prose-headings:font-black prose-headings:text-slate-900 prose-p:text-slate-600 prose-p:leading-loose prose-a:text-[#1FA7A2] prose-img:rounded-2xl prose-li:text-slate-600 animate__animated animate__fadeIn">
                            {!! strip_tags($post->content, '<h1><h2><h3><h4><h5><h6><p><br><hr><strong><em><b><i><u><s><sub><sup><a><img><ul><ol><li><blockquote><code><pre><span><div><table><thead><tbody><tfoot><tr><th><td><caption><figure><figcaption><dl><dt><dd>') !!}
                        </article>

                        {{-- قسم المشاركة --}}
                        <div class="mt-12 pt-8 border-t border-dashed border-slate-200 flex flex-col md:flex-row justify-between items-center gap-6">
                            <div class="flex flex-wrap gap-2">
                                <span class="px-4 py-1.5 bg-slate-100 text-slate-500 rounded-xl text-xs font-bold hover:bg-[#1FA7A2] hover:text-white transition-colors cursor-pointer">#آمر_7</span>
                                <span class="px-4 py-1.5 bg-slate-100 text-slate-500 rounded-xl text-xs font-bold hover:bg-[#1FA7A2] hover:text-white transition-colors cursor-pointer">#تأسيس_شركات</span>
                            </div>
                            <div class="flex items-center gap-3" x-data="{ shareUrl: window.location.href }">
                                <span class="text-slate-400 text-xs font-bold">شارك المعرفة:</span>
                                <a :href="'https://api.whatsapp.com/send?text=' + shareUrl" target="_blank" class="w-10 h-10 flex items-center justify-center rounded-full bg-white border border-slate-200 text-slate-500 hover:bg-[#25D366] hover:text-white hover:border-[#25D366] hover:-translate-y-1 transition-all shadow-sm">
                                    <i class="fab fa-whatsapp text-lg"></i>
                                </a>
                                <a :href="'https://twitter.com/intent/tweet?url=' + shareUrl" target="_blank" class="w-10 h-10 flex items-center justify-center rounded-full bg-white border border-slate-200 text-slate-500 hover:bg-black hover:text-white hover:border-black hover:-translate-y-1 transition-all shadow-sm">
                                    <i class="fab fa-x-twitter text-lg"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- العمود الثاني: القائمة الجانبية (Sidebar) تأخذ 4 أعمدة --}}
                <div class="lg:col-span-4 space-y-6">
                    
                    {{-- جعل القائمة الجانبية تلتصق بالشاشة أثناء النزول --}}
                    <div class="sticky top-28 space-y-6">
                        
                        {{-- 1. صندوق البحث (هوية حديثة) --}}
                        <div class="bg-white/80 backdrop-blur-md rounded-[2rem] p-6 shadow-lg border border-slate-100">
                            <h3 class="text-lg font-black text-slate-800 mb-4 flex items-center gap-2">
                                <i class="fas fa-search text-[#1FA7A2]"></i> ابحث في المدونة
                            </h3>
                            <form action="{{ route('blog.index') ?? '#' }}" method="GET" class="relative group">
                                <input type="text" name="search" placeholder="عن ماذا تبحث؟" 
                                       class="w-full bg-slate-50 border-2 border-transparent focus:border-[#1FA7A2] rounded-xl py-3 px-4 outline-none transition-all text-sm font-bold text-slate-700">
                                <button type="submit" class="absolute left-2 top-1/2 -translate-y-1/2 w-8 h-8 bg-[#1FA7A2] text-white rounded-lg flex items-center justify-center hover:bg-[#167F7B] transition-colors">
                                    <i class="fas fa-arrow-left text-xs"></i>
                                </button>
                            </form>
                        </div>

                        {{-- 2. صندوق التصنيفات --}}
                        <div class="bg-white/80 backdrop-blur-md rounded-[2rem] p-6 shadow-lg border border-slate-100">
                            <h3 class="text-lg font-black text-slate-800 mb-4 flex items-center gap-2">
                                <i class="fas fa-tags text-[#1FA7A2]"></i> تصنيفات هامة
                            </h3>
                            <ul class="space-y-2">
                                <li><a href="#" class="flex items-center justify-between p-3 rounded-xl bg-slate-50 text-slate-600 font-bold text-sm hover:bg-[#1FA7A2] hover:text-white transition-all group"><span class="flex items-center gap-2"><i class="fas fa-circle text-[8px] text-teal-400 group-hover:text-white"></i> تأسيس الشركات</span> <i class="fas fa-chevron-left text-[10px]"></i></a></li>
                                <li><a href="#" class="flex items-center justify-between p-3 rounded-xl bg-slate-50 text-slate-600 font-bold text-sm hover:bg-[#1FA7A2] hover:text-white transition-all group"><span class="flex items-center gap-2"><i class="fas fa-circle text-[8px] text-teal-400 group-hover:text-white"></i> رخص الاستثمار</span> <i class="fas fa-chevron-left text-[10px]"></i></a></li>
                                <li><a href="#" class="flex items-center justify-between p-3 rounded-xl bg-slate-50 text-slate-600 font-bold text-sm hover:bg-[#1FA7A2] hover:text-white transition-all group"><span class="flex items-center gap-2"><i class="fas fa-circle text-[8px] text-teal-400 group-hover:text-white"></i> الأنظمة التجارية</span> <i class="fas fa-chevron-left text-[10px]"></i></a></li>
                            </ul>
                        </div>

                        {{-- 3. صندوق CTA السريع (البديل العصري لصندوق إتمام) --}}
                        <div class="bg-gradient-to-br from-[#1FA7A2] to-[#167F7B] rounded-[2rem] p-8 shadow-xl shadow-teal-900/20 text-center relative overflow-hidden group">
                            {{-- تأثير ضوئي داخلي --}}
                            <div class="absolute -top-10 -right-10 w-32 h-32 bg-white/10 rounded-full blur-2xl group-hover:scale-150 transition-transform duration-700"></div>
                            
                            <div class="w-16 h-16 bg-white/10 backdrop-blur-sm rounded-2xl flex items-center justify-center text-3xl text-white mx-auto mb-4 border border-white/20">
                                <i class="fas fa-headset"></i>
                            </div>
                            <h3 class="text-xl font-black text-white mb-2">تبغى ننجزها لك باحترافية؟</h3>
                            <p class="text-teal-50 text-sm mb-6 leading-relaxed">فريق خبراء آمر سبعة جاهز لخدمتك وإنجاز كافة معاملاتك التجارية بسرعة وموثوقية.</p>
                            
                            <div class="space-y-3 relative z-10">
                                <a href="https://wa.me/966505336956" target="_blank" class="w-full flex items-center justify-center gap-2 py-3 rounded-xl bg-[#25D366] text-white font-bold hover:bg-[#20b858] hover:-translate-y-1 transition-all shadow-md">
                                    <i class="fab fa-whatsapp text-lg"></i> تواصل عبر الواتساب
                                </a>
                                <a href="tel:0505336956" class="w-full flex items-center justify-center gap-2 py-3 rounded-xl bg-white/10 text-white border border-white/20 font-bold hover:bg-white hover:text-[#1FA7A2] transition-all">
                                    <i class="fas fa-phone-alt"></i> اتصل بنا 0505336956
                                </a>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </section>

    {{-- فورم الاستشارة (في أسفل الصفحة كما طلبت) --}}
    <section class="py-16 relative z-10 border-t border-slate-200/60 bg-white/40 backdrop-blur-sm" id="form-section">
        <div class="container mx-auto px-4">
            <div class="max-w-5xl mx-auto">
                <div class="bg-white rounded-[2.5rem] shadow-2xl shadow-slate-200/50 border border-slate-100 p-8 md:p-12 relative overflow-hidden">
                    
                    {{-- شريط ملون أعلى الفورم --}}
                    <div class="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-[#1FA7A2] to-teal-400"></div>
                    
                    <div class="text-center mb-10">
                        <div class="w-16 h-16 bg-gradient-to-br from-[#1FA7A2] to-[#167F7B] text-white rounded-2xl flex items-center justify-center text-2xl mx-auto mb-4 shadow-lg shadow-teal-900/20">
                            <i class="fas fa-file-signature"></i>
                        </div>
                        <h2 class="text-3xl font-black text-slate-900 mb-2">ابدأ رحلة استثمارك الآن</h2>
                        <p class="text-slate-500">سجل بياناتك وسيقوم مستشارنا بالتواصل معك عبر الواتساب فوراً</p>
                    </div>

                    {{-- استدعاء مكون Livewire الخاص بالفورم هنا --}}
                    <livewire:public.landing-company-formation-form />
                    
                </div>
            </div>
        </div>
    </section>

</div>

<script>
    window.addEventListener('scroll', function() {
        let winScroll = document.body.scrollTop || document.documentElement.scrollTop;
        let height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
        let scrolled = (winScroll / height) * 100;
        document.getElementById("progress-bar").style.width = scrolled + "%";
    });
</script>
@endsection