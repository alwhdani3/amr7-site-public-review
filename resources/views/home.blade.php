@extends('layouts.app')

@php
    $locale = app()->getLocale();
    $isAr   = $locale === 'ar';

    $seoTitle = $isAr ? 'تأسيس شركات في الرياض' : 'Company Formation in Riyadh';

    $statsData = [
        ['label' => $isAr ? 'عميل سعيد'        : 'Happy Clients',           'value' => $stats['clients']      ?? 11000, 'icon' => 'fa-users',          'unit' => '+'],
        ['label' => $isAr ? 'شركة تم تأسيسها'  : 'Companies Formed',        'value' => $stats['companies']    ?? 500,   'icon' => 'fa-building',       'unit' => '+'],
        ['label' => $isAr ? 'معاملة منجزة'     : 'Transactions Completed',  'value' => $stats['transactions'] ?? 15000, 'icon' => 'fa-file-signature', 'unit' => '+'],
        ['label' => $isAr ? 'نسبة رضا'         : 'Satisfaction Rate',       'value' => $stats['satisfaction'] ?? 99,    'icon' => 'fa-star',           'unit' => '%'],
    ];

    $featureCards = [
        [
            'icon'  => 'fa-building',
            'label' => $isAr ? 'تأسيس الشركات' : 'Company Formation',
            'desc'  => $isAr ? 'إصدار السجل التجاري وعقد التأسيس بخطوات نظامية متكاملة.' : 'Full legal registration and CR issuance.',
            'route' => route('public.landing.company-formation-riyadh')
        ],
        [
            'icon'  => 'fa-crown',
            'label' => $isAr ? 'الاستثمار الأجنبي' : 'Foreign Investment',
            'desc'  => $isAr ? 'تسهيل الحصول على تراخيص الاستثمار MISA بكل احترافية.' : 'Facilitating MISA foreign investment licenses.',
            'route' => route('landing.foreign_investment')
        ],
        [
            'icon'  => 'fa-file-invoice-dollar',
            'label' => $isAr ? 'القوائم المالية' : 'Financial Statements',
            'desc'  => $isAr ? 'إعداد واعتماد القوائم المالية للمنشآت بدقة وموثوقية.' : 'Accurate preparation of financial statements.',
            'route' => route('financial-statements.portal')
        ],
        [
            'icon'  => 'fa-store-slash',
            'label' => $isAr ? 'تصفية الشركات' : 'Company Liquidation',
            'desc'  => $isAr ? 'إنهاء أعمال المنشأة وتصفيتها قانونياً بكل يسر وسهولة.' : 'Legal and smooth company liquidation services.',
            'route' => route('landing.liquidation')
        ],
    ];

    $timelineSteps = [
        ['step'=>'1','title'=>$isAr?'قدّم طلبك':'Submit your request', 'desc'=>$isAr?'اختر الخدمة واملأ بياناتك بسهولة.':'Choose your service and fill in your details.'],
        ['step'=>'2','title'=>$isAr?'مراجعة وتدقيق':'Review', 'desc'=>$isAr?'نراجع مستنداتك ونتحقق من نظاميتها باحترافية.':'We review and validate your documents professionally.'],
        ['step'=>'3','title'=>$isAr?'الاعتماد':'Approval', 'desc'=>$isAr?'يصلك تأكيد واضح بالقبول والخطوات التالية.':'You receive a clear confirmation and next steps.'],
        ['step'=>'4','title'=>$isAr?'التنفيذ':'Execution', 'desc'=>$isAr?'نستكمل التسجيلات والربط مع الجهات الحكومية.':'We complete registrations and government integrations.'],
        ['step'=>'5','title'=>$isAr?'التسليم':'Delivery', 'desc'=>$isAr?'تستلم مخرجاتك القانونية والتشغيلية جاهزة.':'You receive your legal and operational deliverables.'],
    ];

    $faqs = [
        ['q'=>$isAr?'هل تقدمون خدماتكم في الرياض فقط أم في جميع مناطق المملكة?':'Do you serve Riyadh only or all regions?','a'=>$isAr?'نخدم عملاءنا في الرياض وفي جميع مناطق المملكة حسب متطلبات الخدمة.':'We serve clients in Riyadh and across all regions.'],
        ['q'=>$isAr?'ما الخدمات التي تقدمها آمر سبعة؟':'What services does Amr 7 provide?','a'=>$isAr?'خدمات تأسيس الشركات، السجل التجاري، العقود، الامتثال والحوكمة، والقوائم المالية.':'Company formation, CR, contracts, compliance, governance, and financial statements.'],
        ['q'=>$isAr?'هل يمكن الحصول على استشارة قبل تقديم الطلب؟':'Can I get a consultation first?','a'=>$isAr?'نعم، تواصل معنا عبر واتساب أو الهاتف وسنرشدك لأفضل نقطة بداية.':'Yes. Contact us via WhatsApp or phone and we will guide you.'],
        ['q'=>$isAr?'ما المتطلبات المعتادة لتأسيس شركة؟':'What are the requirements to form a company?','a'=>$isAr?'يعتمد على نوع المنشأة، وغالباً تشمل: بيانات الشركاء، العنوان، النشاط، ورأس المال.':'It depends on the entity type, but typically: partner data, address, activity, and capital.'],
        ['q'=>$isAr?'كم يستغرق تأسيس الشركة؟':'How long does company formation take?','a'=>$isAr?'يختلف حسب نوع المنشأة والجهة المختصة، وغالباً يتم في غضون أيام عمل قليلة.':'It varies by entity type and authority, usually completed within a few business days.'],
    ];

    $testimonials = [
        ['name' => 'رود بايكر للدراجات النارية', 'date' => '2026/03/08', 'comment' => 'جيد بالتعامل واستخرج لنا سجل شركة بسهوله بدون تعقيدات وله معرفه باستخرج السجلات'],
        ['name' => 'الضرغام 1', 'date' => '2025/09/11', 'comment' => 'شخص جدا رائع ومتفاني ومتعاون قام بإنجاز شغلي على اكمل وجه انصح باالتعامل معة والله يجزاه كل خير على جهدة'],
        ['name' => 'kobaid', 'date' => '2025/09/09', 'comment' => 'ما شاء الله تبارك الله أفضل تجربة مرت علي بإنهاء إجراءات وشطب شركة وباقل التكاليف. تعامل محترف سريع فاهم الإجراءات يقلل عليك التكاليف. امانة عالية واحترافية.'],
        ['name' => 'abo thamer2020', 'date' => '2025/07/17', 'comment' => 'التجربة كانت جدا رائعة وانصح التعامل معة بكل أمانة مخلص في عملة وخبرة في مجالة الله يعطيك الصحة والعافية'],
        ['name' => 'مؤسسة أبراج طيبة العقارية', 'date' => '2025/05/21', 'comment' => 'انصح بالتعامل مع الاستاذ احمد للاسباب التاليه سرعه ومصداقيه في الانجاز'],
        ['name' => 'alshami 6760', 'date' => '2025/03/18', 'comment' => 'الله يحفظك ويوفقك انسان قمة بالأخلاق ورقي بالتعامل وانسان منجز وناصح امين الله يوفقك ويرزقك وبإذن الرحمن بينا شغل'],
        ['name' => 'harbifm1409', 'date' => '2025/02/22', 'comment' => 'شخص جدا محترم وينجز لك الشي المطلوب وزيادة ويعطيك المشورة اللي تحتاجها'],
        ['name' => 'كنز العرب2222', 'date' => '2024/12/04', 'comment' => 'السلام عليكم ورحمة الله وبركاته الله يبيض وجهك انسان أمين وصادق ومحل الثقه لديه خبره وسريع بالإنجاز والرد.. قام بإنجاز عقد تأسيس شركه وتعديله على النظام الجديد'],
        ['name' => 'ابوفهد من الرياض', 'date' => '2024/08/29', 'comment' => 'الله يعطيهم العافية ماقصروا في شطب سجل شركة واجهتني بعض الصعوبات وحلوها بعد توفيق الله وتم شطب السجل في وقت قياسي الف شكر 🙏 وانصح التعامل معهم وبالتوفيق'],
        ['name' => 'ابو راشد43210', 'date' => '2024/08/07', 'comment' => 'الف شكر الاخ احمد على حسن التعاون والتعامل وان شاء الله سوف يكون بيننا عمل اخر'],
        ['name' => 'ابوسعد213', 'date' => '2024/08/07', 'comment' => 'احمد ما قصر في شي فاهم و خدوم الله يحفظه و يجزاه خير خلص موضوعي على اكمل وجه'],
        ['name' => 'رويال المستقبل', 'date' => '2024/07/26', 'comment' => 'الاخ: أحمد تعاملت معه في معاملة تحويل مؤسسة لشركة وكذلك نقل ملكية شركة وكان نعم المنجز والناصح وتم كل ذلك خلال يوم واحد أسأل الله أن يبارك له بصحته وماله'],
        ['name' => 'ghamsm2p', 'date' => '2024/07/07', 'comment' => 'رجل بمعنى الكلمة .. وعد وصدق .. دقه في المواعيد ومصداقية .. أنصح بالتعامل معاه..'],
        ['name' => 'abohor11', 'date' => '2024/07/03', 'comment' => 'تعاملت مع الاخ احمد والله عنوان الصدق والامانه والانجاز وبارك الله بجهدك وقتك وشكرا لك'],
        ['name' => 'عبدالرحمن.585', 'date' => '2024/06/04', 'comment' => 'انصح بالتعامل معه سريع في الانجاز واخلاق عالية وامانة في التعامل اتمنى له التوفيق والنجاح'],
        ['name' => 'hassan1450', 'date' => '2024/05/14', 'comment' => 'منجز وشغله رائع حول لي المؤسسة الي شركه في نص يوم 👌'],
        ['name' => 'anwar2711983', 'date' => '2024/04/25', 'comment' => 'السلام عليكم تعاملت معه قمة الزوق والاحترام والرقى وسرعة فى الانجاز وصدق فى المعاملة والامانة'],
        ['name' => 'stor.eo', 'date' => '2024/04/14', 'comment' => 'من افضل الاشخاص اللي تم التعامل معهم صراحة خبرة وسرعة وفهم لكافة الاعمال كل الشكر والتقدير لكم'],
        ['name' => 'ahmad853', 'date' => '2024/03/20', 'comment' => 'عن تجربه شخص فاهم حولت من عنده موسسة لشركة سريع ومتعاون جدا ويسويلك بعقد التاسيس والاجرائات'],
        ['name' => 'زززحمة', 'date' => '2024/02/06', 'comment' => 'ماشاءالله تبارك الله.. سرعة في الانجاز و خدوم و يجاوب على تساؤلاتك كأن الشغلة له. الله يوفقه و يرزقه.']
    ];

    $avatarColors = ['bg-[#1FA7A2]', 'bg-[#167F7B]', 'bg-slate-800', 'bg-slate-700'];
@endphp

@section('title', $seoTitle)

@push('styles')
@if(!empty($testimonials) && count($testimonials) > 0)
    <link rel="preload" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css"></noscript>
@endif
<style>
    .swiper-wrapper { transition-timing-function: linear; }
    .reveal { opacity: 0; transform: translateY(30px); transition: all .8s cubic-bezier(.5,0,0,1); }
    .reveal.active { opacity: 1; transform: translateY(0); }
</style>
@endpush

@section('content')

<section class="relative bg-white pt-16 pb-40 lg:pt-24 lg:pb-48">
    <div class="absolute inset-0 opacity-5 pointer-events-none"
         style="background-image: radial-gradient(#1FA7A2 1.5px, transparent 1.5px); background-size: 24px 24px;"
         aria-hidden="true"></div>

    <div class="container mx-auto px-4 relative z-10">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">

            <div class="lg:col-span-7 text-center lg:text-start rtl:lg:text-right reveal">
                <div class="inline-flex items-center gap-2 bg-[#f0fdfa] border border-[#ccfbf1] text-[#1FA7A2] px-4 py-2 rounded-full text-sm font-bold mb-6 shadow-sm">
                    <i class="fas fa-crown" aria-hidden="true"></i>
                    <span>{{ $isAr ? 'تأسيس شركات وخدمات قانونية في السعودية' : 'Company formation & legal services in Saudi Arabia' }}</span>
                </div>

                <h1 class="text-4xl lg:text-6xl font-black text-gray-900 mb-6 leading-tight">
                    {{ $isAr ? 'تأسيس شركات في' : 'Company Formation in' }} <br/>
                    {{ $isAr ? 'الرياض' : 'Riyadh' }}
                </h1>

                <p class="text-xl text-gray-500 mb-6 font-medium">
                    {{ $isAr ? 'نبني الهوية القانونية لشركتك في السعودية' : "We build your company's legal identity in Saudi Arabia" }}
                </p>

                <p class="text-base text-gray-600 mb-8 leading-relaxed max-w-2xl mx-auto lg:mx-0">
                    @if($isAr)
                        في <strong>آمر سبعة</strong>، لا نكتفي بفتح السجلات فقط؛ نصمّم <span class="text-[#1FA7A2] font-bold">هيكلاً قانونياً متوافقاً</span> ومتناغماً مع رؤية 2030.
                    @else
                        At <strong>Amr 7</strong>, we design a complete <span class="text-[#1FA7A2] font-bold">compliant legal structure</span> aligned with Vision 2030.
                    @endif
                </p>

                <div class="flex flex-wrap justify-center lg:justify-start gap-4">
                    <a href="#services" class="inline-flex min-h-12 items-center justify-center bg-[#1FA7A2] hover:bg-[#167F7B] text-white px-8 py-3.5 rounded-full font-bold shadow-md hover:shadow-lg transition gap-2">
                        <span>{{ $isAr ? 'تواصل معنا الآن' : 'Contact Us Now' }}</span>
                        <i class="fas fa-arrow-{{ $isAr ? 'left' : 'right' }}" aria-hidden="true"></i>
                    </a>

                    <a href="https://wa.me/{{ config('amr7.contact.whatsapp', '966505336956') }}" target="_blank" rel="noopener noreferrer"
                       class="inline-flex min-h-12 items-center justify-center bg-white border border-[#1FA7A2] text-[#1FA7A2] hover:bg-[#f0fdfa] px-8 py-3.5 rounded-full font-bold transition gap-2 shadow-sm">
                        <i class="fab fa-whatsapp text-xl" aria-hidden="true"></i>
                        <span>{{ $isAr ? 'استشارة قانونية فورية' : 'Instant legal consultation' }}</span>
                    </a>
                </div>
            </div>

            <div class="lg:col-span-5 hidden lg:block relative reveal delay-200">
                <div class="relative rounded-[2rem] overflow-hidden shadow-2xl border-4 border-white transition duration-500">
                    <picture>
                        <source
                            srcset="{{ asset('images/hero-600.webp') }} 600w, {{ asset('images/hero-900.webp') }} 900w"
                            sizes="(max-width: 768px) 100vw, 50vw"
                            type="image/webp">
                        <source
                            srcset="{{ asset('images/hero-1280.jpg') }} 1280w"
                            sizes="(max-width: 768px) 100vw, 50vw"
                            type="image/jpeg">
                        <img
                            src="{{ asset('images/hero-1280.jpg') }}"
                            alt="{{ $isAr ? 'فريق آمر سبعة لتأسيس الشركات في الرياض' : 'Amr 7 company formation team in Riyadh' }}"
                            width="1600"
                            height="2385"
                            fetchpriority="high"
                            decoding="async"
                            loading="eager"
                            class="w-full h-auto object-cover"
                            onerror="this.onerror=null;this.src='{{ asset('brand/amr7/amr7-og-image-1200x630.png') }}'">
                    </picture>
                </div>
            </div>

        </div>
    </div>
</section>

<section class="relative z-20 -mt-24 lg:-mt-32 px-4 container mx-auto">
    <h2 class="sr-only">{{ $isAr ? 'خدماتنا الرئيسية' : 'Our Main Services' }}</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        @foreach($featureCards as $card)
            <div class="bg-white rounded-[2rem] p-8 shadow-[0_10px_40px_rgba(0,0,0,0.06)] flex flex-col justify-between h-full border border-gray-50 reveal group hover:-translate-y-1 transition-all duration-300">
                <div>
                    <div class="w-16 h-16 rounded-2xl bg-[#f0fdfa] text-[#1FA7A2] flex items-center justify-center text-3xl mb-6 group-hover:scale-105 transition-transform duration-300">
                        <i class="fas {{ $card['icon'] }}"></i>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-3">{{ $card['label'] }}</h3>
                    <p class="text-slate-600 text-sm leading-relaxed mb-6">{{ $card['desc'] }}</p>
                </div>

                <a href="{{ $card['route'] }}" wire:navigate
   aria-label="{{ ($isAr ? 'اطلب الخدمة: ' : 'Request Service: ') . $card['label'] }}"
   class="inline-flex min-h-11 items-center gap-2 pt-2 text-sm font-bold text-slate-600 hover:text-[#1FA7A2] transition-colors mt-auto">
                    {{ $isAr ? 'اطلب الخدمة' : 'Request Service' }}
                    <i class="fas fa-arrow-{{ $isAr ? 'left' : 'right' }} text-xs transform group-hover:{{ $isAr ? '-translate-x-1' : 'translate-x-1' }} transition-transform"></i>
                </a>
            </div>
        @endforeach
    </div>
</section>

<section class="py-12 bg-white">
    <div class="container mx-auto px-4">
        <h2 class="sr-only">{{ $isAr ? 'إنجازاتنا' : 'Our Achievements' }}</h2>
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-6 text-center reveal">
            @foreach($statsData as $s)
                <div class="p-6 rounded-2xl group">
                    <div class="text-[#1FA7A2] mb-3 text-3xl group-hover:scale-110 transition duration-300">
                        <i class="fas {{ $s['icon'] }}" aria-hidden="true"></i>
                    </div>
                    <div class="text-3xl font-black text-gray-900 mb-1" dir="ltr">
                        <span class="counter" data-target="{{ $s['value'] }}">{{ number_format((int) $s['value']) }}</span>{{ $s['unit'] }}
                    </div>
                    <p class="text-gray-600 text-sm font-bold">{{ $s['label'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

@if(($platforms ?? collect())->isNotEmpty())
<section class="py-10 bg-gray-50 border-y border-gray-100">
    <div class="container mx-auto px-4">
        <div class="text-center mb-10 reveal">
            <h2 class="text-2xl font-black text-gray-900">{{ $isAr ? 'خدمات المنصات الحكومية' : 'Government Platforms Services' }}</h2>
            <p class="text-gray-500 text-sm mt-1">{{ $isAr ? 'نعمل معك لإنجاز معاملاتك بكفاءة' : 'We work with you to process your transactions efficiently' }}</p>
        </div>

        <div class="grid grid-cols-3 sm:grid-cols-4 lg:grid-cols-6 gap-4 reveal">
            @foreach($platforms->take(6) as $platform)
                @php
                    $pName = $isAr
                        ? ($platform->name_ar ?? $platform->name ?? '')
                        : ($platform->name_en ?? $platform->name_ar ?? $platform->name ?? '');
                @endphp

                <a href="{{ route('services.platform', ['platform' => $platform->slug]) }}" wire:navigate
                   class="flex flex-col items-center text-center p-4 rounded-2xl border border-slate-100 bg-white hover:border-[#1FA7A2] hover:shadow-md transition group">
                    <div class="w-12 h-12 rounded-xl bg-[#f0fdfa] flex items-center justify-center mb-3 group-hover:bg-[#1FA7A2] transition">
                        <i class="fas fa-building-columns text-[#1FA7A2] group-hover:text-white text-lg transition" aria-hidden="true"></i>
                    </div>
                    <p class="text-xs font-bold text-slate-700 group-hover:text-[#1FA7A2] leading-tight">{{ $pName }}</p>
                </a>
            @endforeach
        </div>
    </div>
</section>
@endif

<section id="services" class="py-20 bg-white">
    <div class="container mx-auto px-4">
        <div class="text-center mb-16 reveal">
            <span class="text-[#1FA7A2] font-bold uppercase tracking-wider text-sm">{{ $isAr ? 'خدمات تفصيلية' : 'Detailed Services' }}</span>
            <h2 class="text-4xl font-black text-gray-900 mt-2">{{ $isAr ? 'ماذا نقدم لكم؟' : 'What do we offer?' }}</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @forelse(($services ?? []) as $service)
                @php
                    $serviceTitle = app()->getLocale() === 'ar'
                        ? ($service->title_ar ?? $service->title_en ?? '')
                        : ($service->title_en ?? $service->title_ar ?? '');

                    $serviceDesc = \Illuminate\Support\Str::limit(
                        app()->getLocale() === 'ar'
                            ? ($service->description_ar ?? $service->description_en ?? $service->description ?? '')
                            : ($service->description_en ?? $service->description_ar ?? $service->description ?? ''),
                        100
                    );
                @endphp

                <div class="group relative bg-white rounded-3xl p-8 border border-gray-100 shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-2 reveal">
                    <div class="w-16 h-16 mx-auto bg-gray-50 rounded-2xl flex items-center justify-center mb-6 shadow-inner group-hover:bg-[#effcfc] transition">
                        @if(isset($service->icon) && $service->icon)
                            <img
                                src="{{ asset('storage/' . $service->icon) }}"
                                class="w-8 h-8 object-contain" style="max-width:32px;max-height:32px"
                                alt="{{ $serviceTitle }}"
                                width="32"
                                height="32"
                                loading="lazy"
                                decoding="async"
                                onerror="this.onerror=null;this.src='{{ asset('images/platform-placeholder.png') }}';">
                        @else
                            <i class="fas fa-briefcase text-2xl text-[#1FA7A2]" aria-hidden="true"></i>
                        @endif
                    </div>

                    <h3 class="text-xl font-bold text-gray-900 mb-4 text-center">{{ $serviceTitle }}</h3>
                    <p class="text-gray-600 text-sm mb-6 text-center line-clamp-3">{{ $serviceDesc }}</p>

                    <a href="{{ route('services.show', ['service' => $service->slug]) }}"
                       aria-label="{{ ($isAr ? 'عرض تفاصيل: ' : 'View details: ') . $serviceTitle }}"
                       class="inline-flex min-h-12 w-full items-center justify-center text-center border border-[#1FA7A2] text-[#1FA7A2] py-3 rounded-xl font-bold hover:bg-[#1FA7A2] hover:text-white transition relative">
                        {{ $isAr ? 'عرض التفاصيل' : 'View details' }}
                        <span class="absolute inset-0" aria-hidden="true"></span>
                    </a>
                </div>
            @empty
                @for($i = 0; $i < 6; $i++)
                    <div class="group relative bg-white rounded-3xl p-8 border border-gray-100 shadow-sm reveal">
                        <div class="w-16 h-16 mx-auto bg-gray-50 rounded-2xl flex items-center justify-center mb-6">
                            <i class="fas fa-file-contract text-2xl text-[#1FA7A2]" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-4 text-center">{{ $isAr ? 'تأسيس الشركات' : 'Business Registration' }}</h3>
                        <p class="text-gray-600 text-sm mb-6 text-center">{{ $isAr ? 'تسجيل قانوني متكامل للمنشأة.' : 'Full legal registration for your entity.' }}</p>
                        <span class="block w-full text-center text-[#1FA7A2] font-bold">{{ $isAr ? 'قريباً' : 'Coming Soon' }}</span>
                    </div>
                @endfor
            @endforelse
        </div>

        <div class="text-center mt-10 reveal">
            <a href="{{ route('services.index') }}" class="inline-flex min-h-12 items-center gap-2 border-2 border-[#1FA7A2] text-[#1FA7A2] px-8 py-3 rounded-full font-bold hover:bg-[#1FA7A2] hover:text-white transition">
                {{ $isAr ? 'عرض جميع الخدمات' : 'View all services' }}
                <i class="fas fa-arrow-{{ $isAr ? 'left' : 'right' }}" aria-hidden="true"></i>
            </a>
        </div>
    </div>
</section>

<section class="py-20 bg-gray-50 relative overflow-hidden">
    <div class="absolute inset-0 opacity-10 pointer-events-none"
         style="background-image: radial-gradient(#1FA7A2 1px, transparent 1px); background-size: 30px 30px;" aria-hidden="true"></div>

    <div class="container mx-auto px-4 relative z-10">
        <div class="text-center mb-16 reveal">
            <span class="inline-block bg-[#f0fdfa] text-[#1FA7A2] px-4 py-1 rounded-full text-sm font-bold mb-3 shadow-sm border border-[#ccfbf1]">
                <i class="fas fa-route ms-2" aria-hidden="true"></i> {{ $isAr ? 'الآلية' : 'Process' }}
            </span>
            <h2 class="text-4xl font-black text-gray-900 mb-3">
                {{ $isAr ? 'رحلتك مع' : 'Your journey with' }}
                <span class="bg-clip-text text-transparent bg-gradient-to-r from-[#1FA7A2] to-[#167F7B]">{{ $isAr ? 'آمر سبعة' : 'Amr Seven' }}</span>
            </h2>
            <p class="text-gray-600 text-lg">{{ $isAr ? 'خطوات بسيطة لتشغيل منشأتك باحترافية' : 'Simple steps to get your business running professionally' }}</p>
        </div>

        <div class="relative mt-12">
            <div class="absolute left-1/2 transform -translate-x-1/2 w-0.5 h-full bg-gray-200 hidden md:block" aria-hidden="true"></div>

            @foreach($timelineSteps as $i => $s)
                <div class="flex flex-col md:flex-row items-center justify-between mb-12 {{ $i % 2 == 0 ? '' : 'md:flex-row-reverse' }} reveal">
                    <div class="w-full md:w-5/12 {{ $i % 2 == 0 ? 'md:text-right rtl:md:text-left text-center' : 'md:text-left rtl:md:text-right text-center' }}">
                        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 inline-block w-full hover:shadow-md transition">
                            <h3 class="font-bold text-gray-900 mb-2 text-lg">
                                <span class="text-[#1FA7A2] mr-2">#{{ $s['step'] }}</span> {{ $s['title'] }}
                            </h3>
                            <p class="text-gray-600 text-sm leading-relaxed">{{ $s['desc'] }}</p>
                        </div>
                    </div>

                    <div class="w-2/12 flex justify-center py-4 md:py-0 relative z-10">
                        <div class="w-10 h-10 bg-[#1FA7A2] rounded-full border-4 border-white shadow-lg flex items-center justify-center text-white">
                            <i class="fas fa-check text-xs" aria-hidden="true"></i>
                        </div>
                    </div>

                    <div class="w-full md:w-5/12"></div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section id="faq" class="py-20 bg-white">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12 reveal">
            <h2 class="text-3xl font-black text-gray-900">{{ $isAr ? 'أسئلة شائعة' : 'Frequently Asked Questions' }}</h2>
        </div>

        <div class="max-w-3xl mx-auto reveal" x-data="{ activeAccordion: null }">
            <div class="space-y-4">
                @foreach($faqs as $idx => $f)
                    <div class="border border-gray-100 rounded-2xl bg-white overflow-hidden shadow-sm">
                        <button type="button"
                                @click="activeAccordion = activeAccordion === {{ $idx }} ? null : {{ $idx }}"
                                :aria-expanded="activeAccordion === {{ $idx }} ? 'true' : 'false'"
                                class="w-full min-h-12 flex justify-between items-center p-5 text-start focus:outline-none hover:bg-gray-50 transition">
                            <span class="font-bold text-gray-900">{{ $f['q'] }}</span>
                            <i class="fas fa-chevron-down text-gray-500 text-xs transition-transform duration-300"
                               :class="{'rotate-180': activeAccordion === {{ $idx }}}" aria-hidden="true"></i>
                        </button>

                        <div x-show="activeAccordion === {{ $idx }}" x-cloak
                             class="border-t border-gray-100 bg-white p-5 text-gray-600 text-sm leading-relaxed"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 -translate-y-2"
                             x-transition:enter-end="opacity-100 translate-y-0">
                            {{ $f['a'] }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

@if(($posts ?? collect())->isNotEmpty())
<section class="py-20 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="flex items-center justify-between mb-10 reveal">
            <div>
                <h2 class="text-3xl font-black text-gray-900">{{ $isAr ? 'أحدث المقالات' : 'Latest Articles' }}</h2>
                <p class="text-gray-500 text-sm mt-1">{{ $isAr ? 'إلقاء نظرة على آخر الأخبار' : 'A look at the latest news' }}</p>
            </div>

            <a href="{{ route('blog.index') }}" class="inline-flex min-h-11 items-center gap-1 px-2 py-2 text-sm font-bold text-[#1FA7A2] hover:underline">
                {{ $isAr ? 'كل المقالات' : 'All articles' }}
                <i class="fas fa-arrow-{{ $isAr ? 'left' : 'right' }} text-xs" aria-hidden="true"></i>
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            @foreach($posts as $post)
                @php
                    $postTitle   = $post->{'title_'.$locale} ?? $post->title ?? '';
                    $postExcerpt = $post->{'excerpt_'.$locale} ?? $post->excerpt ?? '';
                    $postSlug    = $post->slug ?? '#';
                    $postDate    = $post->published_at ? \Carbon\Carbon::parse($post->published_at)->translatedFormat('d F Y') : '';
                    $postImage   = $post->image ? asset('storage/'.$post->image) : asset('images/placeholder-blog.jpg');
                    $postImageIsPlaceholder = ! $post->image;
                @endphp

                <div class="bg-white rounded-3xl border border-gray-100 shadow-sm hover:shadow-xl transition-all duration-300 overflow-hidden reveal">
                    <a href="{{ route('blog.show', $postSlug) }}" wire:navigate>
                        <img src="{{ $postImage }}"
                             @if($postImageIsPlaceholder)
                                 srcset="{{ asset('images/placeholder-blog-600x400.jpg') }} 600w, {{ asset('images/placeholder-blog-800x533.jpg') }} 800w, {{ asset('images/placeholder-blog.jpg') }} 1200w"
                                 sizes="(max-width: 768px) 100vw, 33vw"
                             @endif
                             alt="{{ $postTitle }}"
                             class="aspect-[16/9] w-full object-cover bg-slate-100"
                             loading="lazy" decoding="async">
                    </a>

                    <div class="p-6">
                        <p class="text-gray-500 text-xs mb-2">{{ $postDate }}</p>
                        <h3 class="font-bold text-gray-900 mb-3 line-clamp-2">
                            <a href="{{ route('blog.show', $postSlug) }}" wire:navigate class="hover:text-[#1FA7A2] transition">
                                {{ $postTitle }}
                            </a>
                        </h3>
                        <p class="text-gray-600 text-sm line-clamp-2 mb-4">{{ $postExcerpt }}</p>
<a href="{{ route('blog.show', $postSlug) }}" wire:navigate
   aria-label="{{ ($isAr ? 'اقرأ أكثر: ' : 'Read more: ') . $postTitle }}"
   class="inline-flex min-h-11 items-center gap-1 text-sm font-bold text-[#1FA7A2] hover:underline">
    {{ $isAr ? 'اقرأ أكثر' : 'Read more' }}
                            <i class="fas fa-arrow-{{ $isAr ? 'left' : 'right' }} text-xs" aria-hidden="true"></i>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

<section class="py-20 bg-white overflow-hidden">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12 reveal">
            <div class="inline-flex items-center gap-2 bg-[#f0fdfa] border border-[#ccfbf1] text-[#1FA7A2] px-4 py-2 rounded-full text-sm font-bold mb-4 shadow-sm">
                <i class="fas fa-star text-amber-400" aria-hidden="true"></i>
                <span>{{ $isAr ? 'موثوقية حراج بلمسة آمر سبعة' : 'Trusted Reviews' }}</span>
            </div>
            <h2 class="text-3xl font-black text-gray-900">{{ $isAr ? 'آراء شركاء النجاح' : 'Client Testimonials' }}</h2>
            <p class="text-gray-500 text-sm mt-2">{{ $isAr ? 'مقتطفات من تقييمات عملائنا في موقع حراج' : 'Reviews from our clients on Haraj' }}</p>
        </div>

        <div class="swiper testimonialsSwiper reveal" dir="rtl">
            <div class="swiper-wrapper py-4">
                @foreach($testimonials as $t)
                    @php
                        $colorClass = $avatarColors[array_rand($avatarColors)];
                        $firstLetter = mb_substr($t['name'], 0, 1, 'UTF-8');
                    @endphp

                    <div class="swiper-slide w-[280px] sm:w-[320px] h-auto">
                        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-[0_4px_20px_rgba(0,0,0,0.03)] hover:shadow-md transition-shadow h-full min-h-[160px] flex flex-col justify-start">
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full {{ $colorClass }} text-white flex items-center justify-center font-bold text-base shadow-sm shrink-0">
                                        {{ $firstLetter }}
                                    </div>
                                    <span class="font-bold text-sm text-gray-800 line-clamp-1">{{ $t['name'] }}</span>
                                </div>

                                <div class="flex items-center gap-1.5 shrink-0">
                                    <span class="text-[10px] text-gray-500" dir="ltr">{{ $t['date'] }}</span>
                                    <span class="flex items-center justify-center bg-[#ebfff0] text-green-500 rounded-full p-1.5">
                                        <i class="fas fa-thumbs-up text-[10px]" aria-hidden="true"></i>
                                    </span>
                                </div>
                            </div>

                            <p class="text-gray-600 text-sm leading-relaxed text-right line-clamp-4 mt-2">
                                {{ $t['comment'] }}
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="text-center mt-10 reveal">
            <a href="https://haraj.com.sa/users/شركة%20امر%20سبعة%20لحلول%20الأعمال" target="_blank" rel="noopener noreferrer"
               class="inline-flex min-h-12 items-center gap-2 border-2 border-[#1FA7A2] text-[#1FA7A2] px-6 py-2 rounded-full font-bold hover:bg-[#1FA7A2] hover:text-white transition">
                <i class="fas fa-external-link-alt text-sm" aria-hidden="true"></i>
                {{ $isAr ? 'شاهد كافة تقييماتنا على حراج' : 'View all reviews on Haraj' }}
            </a>
        </div>
    </div>
</section>

<section class="py-16 bg-gradient-to-br from-[#1FA7A2] to-[#167F7B] text-white">
    <div class="container mx-auto px-4">
        <div class="flex flex-col lg:flex-row items-center justify-between gap-8 reveal">
            <div>
                <h2 class="text-3xl lg:text-4xl font-black mb-3">{{ $isAr ? 'يسعدنا أن نكون جزءاً من نجاحكم' : 'We are proud to be part of your success' }}</h2>
                <p class="opacity-80 font-light">{{ $isAr ? 'خبراء آمر سبعة جاهزون يراجعون متطلباتك' : 'Amr 7 experts are ready to review your requirements' }}</p>
            </div>

            <a href="{{ route('contact.index') }}" wire:navigate
               class="flex-shrink-0 inline-flex min-h-12 items-center justify-center bg-white text-[#1FA7A2] px-10 py-4 rounded-full font-black shadow-lg hover:shadow-xl hover:-translate-y-1 transition-all">
                {{ $isAr ? 'تواصل معنا' : 'Contact Us' }}
            </a>
        </div>
    </div>
</section>

@if(($partners ?? collect())->isNotEmpty())
<section class="py-12 bg-white border-t border-gray-100">
    <div class="container mx-auto px-4">
        <div class="flex flex-wrap justify-center items-center gap-8 opacity-60 hover:opacity-100 transition-opacity duration-500">
            @foreach($partners as $partner)
                <a href="{{ $partner->url ?? '#' }}" target="_blank" rel="noopener noreferrer"
                   class="grayscale hover:grayscale-0 transition duration-300">
                    <img src="{{ $partner->logo_url ?? asset('images/partner-placeholder.png') }}"
                         alt="{{ $partner->name ?? '' }}"
                         class="h-10 w-auto object-contain"
                         loading="lazy"
                         decoding="async">
                </a>
            @endforeach
        </div>
    </div>
</section>
@endif

<section class="bg-white">
    <div class="w-full h-[350px] grayscale opacity-70 hover:grayscale-0 hover:opacity-100 transition-all duration-1000">
        <iframe
            src="{{ $settings?->map_embed_url ?: 'https://maps.google.com/maps?q=شركة+آمر+سبعة+لحلول+الأعمال&z=17&output=embed' }}"
            width="100%"
            height="100%"
            class="border-0 outline-none"
            loading="lazy"
            referrerpolicy="no-referrer-when-downgrade"
            title="{{ $isAr ? 'موقع آمر سبعة' : 'Amr 7 Location' }}">
        </iframe>
    </div>
</section>

<section id="contact" class="py-20 bg-gradient-to-br from-[#1FA7A2] to-[#167F7B] text-white">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <div class="reveal">
                <h2 class="text-4xl lg:text-5xl font-black mb-6">{{ $isAr ? 'جاهز تطلق مشروعك؟' : 'Ready to launch your business?' }}</h2>
                <p class="text-lg opacity-80 mb-8 font-light">{{ $isAr ? 'خبراء آمر سبعة جاهزون يراجعون متطلباتك.' : 'Amr 7 experts are available to review your requirements.' }}</p>
                <ul class="space-y-4">
                    <li class="flex items-center gap-3">
                        <i class="fas fa-check-circle text-2xl opacity-90" aria-hidden="true"></i>
                        <span class="text-lg font-bold">{{ $isAr ? 'استشارة تأسيس شاملة' : 'Comprehensive initial consultation' }}</span>
                    </li>
                    <li class="flex items-center gap-3">
                        <i class="fas fa-check-circle text-2xl opacity-90" aria-hidden="true"></i>
                        <span class="text-lg font-bold">{{ $isAr ? 'سرية عالية وحماية للبيانات' : 'High confidentiality and data security' }}</span>
                    </li>
                </ul>
            </div>

            <div class="reveal delay-200">
                <div class="bg-white text-slate-900 rounded-[2rem] p-8 shadow-xl">
                    <h3 class="text-2xl font-black mb-4">{{ $isAr ? 'ابدأ طلبك الآن' : 'Start your request now' }}</h3>
                    <p class="text-slate-600 mb-6">
                        {{ $isAr ? 'أرسل طلبك عبر صفحة التواصل المخصصة لنراجع احتياجك بسرعة.' : 'Submit your request through the dedicated contact page and we will review it quickly.' }}
                    </p>
                    <a href="{{ route('contact.index') }}"
                       wire:navigate
                       class="inline-flex min-h-12 items-center justify-center px-6 py-3 rounded-xl bg-[#1FA7A2] text-white font-bold hover:bg-[#167F7B] transition">
                        {{ $isAr ? 'الانتقال إلى نموذج الطلب' : 'Go to request form' }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection

@push('scripts')
@if(!empty($testimonials) && count($testimonials) > 0)
<script defer src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
@endif

<script>
(function () {
    let swiperInstance = null;

    const CONFIG = {
        revealClass: 'reveal',
        activeClass: 'active',
        counterSelector: '.counter[data-target]',
        revealThreshold: 0.15
    };

    let revealObserver = null;

    function animateCounters() {
        const counters = document.querySelectorAll(CONFIG.counterSelector);
        if (!counters.length) return;

        const observer = new IntersectionObserver((entries, obs) => {
            entries.forEach(entry => {
                if (!entry.isIntersecting) return;

                const el = entry.target;

                if (el.dataset.animated === '1') {
                    obs.unobserve(el);
                    return;
                }

                el.dataset.animated = '1';
                const target = parseInt(el.getAttribute('data-target') || '0', 10);
                const start = performance.now();

                function tick(now) {
                    const p = Math.min((now - start) / 1500, 1);
                    el.textContent = Math.floor(target * (1 - Math.pow(1 - p, 4))).toLocaleString();

                    if (p < 1) {
                        requestAnimationFrame(tick);
                    } else {
                        el.textContent = target.toLocaleString();
                    }
                }

                requestAnimationFrame(tick);
                obs.unobserve(el);
            });
        }, { threshold: 0.5 });

        counters.forEach(c => observer.observe(c));
    }

    function initScrollReveal() {
        const elements = document.querySelectorAll('.' + CONFIG.revealClass);
        if (!elements.length) return;

        if (revealObserver) {
            revealObserver.disconnect();
            revealObserver = null;
        }

        revealObserver = new IntersectionObserver((entries, obs) => {
            entries.forEach(entry => {
                if (!entry.isIntersecting) return;
                entry.target.classList.add(CONFIG.activeClass);
                obs.unobserve(entry.target);
            });
        }, { threshold: CONFIG.revealThreshold });

        elements.forEach(el => {
            if (!el.classList.contains(CONFIG.activeClass)) {
                revealObserver.observe(el);
            }
        });
    }

    function initTestimonialsSwiper() {
        const el = document.querySelector('.testimonialsSwiper');
        if (!el || typeof Swiper === 'undefined') return;

        if (swiperInstance) {
            swiperInstance.destroy(true, true);
            swiperInstance = null;
        }

        swiperInstance = new Swiper(el, {
            slidesPerView: 'auto',
            spaceBetween: 20,
            loop: true,
            speed: 6500,
            autoplay: {
                delay: 0,
                disableOnInteraction: false,
                pauseOnMouseEnter: true,
            },
            grabCursor: true,
        });
    }

    function init() {
        animateCounters();
        initScrollReveal();
        initTestimonialsSwiper();
    }

    document.addEventListener('DOMContentLoaded', init);
    document.addEventListener('livewire:navigated', () => {
        requestAnimationFrame(init);
    });
    window.addEventListener('load', () => {
        setTimeout(initTestimonialsSwiper, 300);
    });
})();
</script>
@endpush
