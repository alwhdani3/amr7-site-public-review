<?php

namespace App\Support\Seo;

class OfficialServiceContent
{
    public static function forService(string $slug, string $locale): ?array
    {
        return self::localized(self::services(), $slug, $locale);
    }

    public static function forPlatform(string $slug, string $locale): ?array
    {
        return self::localized(self::platforms(), $slug, $locale);
    }

    public static function forLanding(string $key, string $locale): ?array
    {
        return self::localized(self::landings(), $key, $locale);
    }

    public static function faqSchema(?array $content, string $url): ?array
    {
        $faqs = $content['faqs'] ?? [];

        if (! is_array($faqs) || count($faqs) === 0) {
            return null;
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'url' => $url,
            'mainEntity' => array_map(
                fn (array $faq): array => [
                    '@type' => 'Question',
                    'name' => $faq['question'],
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => $faq['answer'],
                    ],
                ],
                $faqs
            ),
        ];
    }

    public static function webpageSchema(?array $content, string $url, string $type = 'WebPage'): ?array
    {
        if (! $content) {
            return null;
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => $type,
            'name' => $content['title'] ?? null,
            'description' => $content['summary'] ?? null,
            'url' => $url,
            'inLanguage' => app()->getLocale() === 'en' ? 'en' : 'ar',
            'about' => array_map(
                fn (array $authority): string => $authority['name'],
                $content['authorities'] ?? []
            ),
        ];
    }

    private static function localized(array $items, string $key, string $locale): ?array
    {
        if (! isset($items[$key])) {
            return null;
        }

        $locale = $locale === 'en' ? 'en' : 'ar';
        $content = $items[$key][$locale] ?? $items[$key]['en'] ?? null;

        if (! $content) {
            return null;
        }

        $content['key'] = $key;

        return $content;
    }

    private static function platforms(): array
    {
        return [
            'wages-protection' => [
                'en' => [
                    'eyebrow' => 'Official Saudi compliance guide',
                    'title' => 'Wages Protection compliance for Saudi employers',
                    'summary' => 'A practical guide for employers that need to keep wage files, payroll records, and labor data aligned with the Saudi Wages Protection Program through the official labor ecosystem.',
                    'who_needs' => [
                        'Private-sector establishments that pay monthly wages to Saudi or non-Saudi workers.',
                        'Employers preparing wage files before raising them through the official wage protection channel.',
                        'Companies that need to reduce payroll mismatch risks between contracts, insurance data, and bank transfers.',
                    ],
                    'requirements' => [
                        'Use establishment and worker data that matches the official labor and social insurance records.',
                        'Prepare wage payment files every month before submission through the approved official channel.',
                        'Review wage amounts and payment dates before upload to reduce compliance exceptions.',
                    ],
                    'documents' => [
                        'Updated employee payroll list.',
                        'Bank transfer or wage file data prepared in the approved format.',
                        'Employment wage records and establishment labor file details.',
                    ],
                    'conditions' => [
                        'The program monitors whether wages are paid on time and in the agreed amount.',
                        'Establishments should reconcile wage files with Ministry and insurance data before submission.',
                    ],
                    'steps' => [
                        ['title' => 'Collect payroll data', 'description' => 'Confirm worker details, wage values, and pay period before preparing the wage file.'],
                        ['title' => 'Prepare the monthly file', 'description' => 'Build the wage file using the approved wage protection format and bank payment data.'],
                        ['title' => 'Submit and review exceptions', 'description' => 'Upload through the official channel and follow up on any rejected or mismatched records.'],
                    ],
                    'duration' => 'Monthly compliance cycle; exception handling depends on data quality and official platform review.',
                    'authorities' => [
                        ['name' => 'HRSD', 'url' => 'https://www.hrsd.gov.sa/en/knowledge-centre/initiatives/national-transformation-initiatives-bank/108808'],
                        ['name' => 'Qiwa', 'url' => 'https://qiwa.sa/'],
                        ['name' => 'GOSI', 'url' => 'https://www.gosi.gov.sa/'],
                    ],
                    'faqs' => [
                        ['question' => 'Is Wages Protection only a payroll task?', 'answer' => 'No. It is a compliance process that compares wage payment data with official labor and insurance records.'],
                        ['question' => 'Why do wage files get exceptions?', 'answer' => 'Common causes include data mismatch, missing worker records, wrong wage values, or late submission.'],
                    ],
                    'related_links' => [
                        ['label' => 'HRSD services platform', 'route' => 'services.platform', 'params' => ['platform' => 'hrsd'], 'description' => 'Labor and establishment compliance services.'],
                        ['label' => 'Register in Qiwa', 'route' => 'services.show', 'params' => ['service' => 'register-in-qiwa-68'], 'description' => 'Prepare access to Qiwa services.'],
                        ['label' => 'ZATCA registration', 'route' => 'services.show', 'params' => ['service' => 'zakat-registration-89'], 'description' => 'Complete tax authority setup after commercial activity starts.'],
                    ],
                    'official_sources' => ['HRSD Wages Protection Program', 'Qiwa', 'GOSI'],
                    'source_notes' => 'Exact portal screens and exception labels may change inside the official platforms.',
                ],
                'ar' => [
                    'eyebrow' => 'دليل امتثال سعودي رسمي',
                    'title' => 'حماية الأجور للمنشآت في السعودية',
                    'summary' => 'دليل عملي للمنشآت التي تحتاج إلى مواءمة ملفات الرواتب والتحويلات البنكية وبيانات العمالة مع برنامج حماية الأجور عبر القنوات الرسمية.',
                    'who_needs' => [
                        'منشآت القطاع الخاص التي تدفع رواتب شهرية للعاملين السعوديين أو غير السعوديين.',
                        'الشركات التي تجهز ملفات الأجور قبل رفعها عبر القناة الرسمية.',
                        'المنشآت التي تريد تقليل مخالفات عدم تطابق بيانات الرواتب والعمل والتأمينات.',
                    ],
                    'requirements' => [
                        'استخدام بيانات منشأة وعاملين متطابقة مع سجلات العمل والتأمينات.',
                        'تجهيز ملف الأجور شهرياً قبل الرفع عبر القناة الرسمية المعتمدة.',
                        'مراجعة مبالغ الرواتب وتواريخ الدفع قبل الرفع لتقليل الاستثناءات.',
                    ],
                    'documents' => [
                        'كشف رواتب محدث للعاملين.',
                        'بيانات التحويل البنكي أو ملف الأجور بالصيغة المعتمدة.',
                        'بيانات ملف المنشأة وسجلات الأجور التعاقدية.',
                    ],
                    'conditions' => [
                        'يراقب البرنامج انتظام دفع الأجور في وقتها وبالمبلغ المتفق عليه.',
                        'ينبغي مطابقة ملف الأجور مع بيانات الوزارة والتأمينات قبل الإرسال.',
                    ],
                    'steps' => [
                        ['title' => 'جمع بيانات الرواتب', 'description' => 'تأكيد بيانات العاملين والأجور وفترة الدفع قبل تجهيز الملف.'],
                        ['title' => 'تجهيز الملف الشهري', 'description' => 'إعداد ملف الأجور وفق الصيغة المعتمدة وبيانات التحويل البنكي.'],
                        ['title' => 'الرفع ومتابعة الاستثناءات', 'description' => 'رفع الملف عبر القناة الرسمية ومراجعة أي سجلات مرفوضة أو غير متطابقة.'],
                    ],
                    'duration' => 'دورة امتثال شهرية، وتختلف معالجة الاستثناءات حسب جودة البيانات ومراجعة المنصة الرسمية.',
                    'authorities' => [
                        ['name' => 'وزارة الموارد البشرية والتنمية الاجتماعية', 'url' => 'https://www.hrsd.gov.sa/'],
                        ['name' => 'قوى', 'url' => 'https://qiwa.sa/'],
                        ['name' => 'التأمينات الاجتماعية', 'url' => 'https://www.gosi.gov.sa/'],
                    ],
                    'faqs' => [
                        ['question' => 'هل حماية الأجور مجرد مهمة رواتب؟', 'answer' => 'لا، هي عملية امتثال تقارن بيانات دفع الأجور مع سجلات العمل والتأمينات الرسمية.'],
                        ['question' => 'لماذا تظهر استثناءات في ملفات الأجور؟', 'answer' => 'غالباً بسبب اختلاف البيانات أو نقص سجلات العاملين أو خطأ في مبلغ الأجر أو تأخر الرفع.'],
                    ],
                    'related_links' => [
                        ['label' => 'خدمات وزارة الموارد البشرية', 'route' => 'services.platform', 'params' => ['platform' => 'hrsd'], 'description' => 'خدمات العمل والامتثال للمنشآت.'],
                        ['label' => 'التسجيل في قوى', 'route' => 'services.show', 'params' => ['service' => 'register-in-qiwa-68'], 'description' => 'تجهيز الوصول إلى خدمات قوى.'],
                        ['label' => 'التسجيل لدى زاتكا', 'route' => 'services.show', 'params' => ['service' => 'zakat-registration-89'], 'description' => 'استكمال إعداد الملف الضريبي بعد بدء النشاط.'],
                    ],
                    'official_sources' => ['برنامج حماية الأجور - وزارة الموارد البشرية', 'قوى', 'التأمينات الاجتماعية'],
                    'source_notes' => 'قد تتغير مسميات الشاشات والاستثناءات داخل المنصات الرسمية.',
                ],
            ],
            'hrsd' => [
                'en' => [
                    'eyebrow' => 'Labor services hub',
                    'title' => 'HRSD and Qiwa services for Saudi establishments',
                    'summary' => 'A focused guide for companies that need labor-file, Qiwa, work-permit, wage, and employee-compliance services without creating duplicate filtered URLs.',
                    'who_needs' => [
                        'New companies after commercial registration and labor-file activation.',
                        'Employers that manage work permits, wage compliance, and establishment labor services.',
                        'Businesses that need authorized access to Qiwa and HRSD digital services.',
                    ],
                    'requirements' => [
                        'An active establishment record and authorized user access.',
                        'Consistent establishment, owner, manager, and worker data across official systems.',
                        'Related records may need alignment with GOSI, ZATCA, or other platforms depending on the service.',
                    ],
                    'documents' => [
                        'Commercial registration or establishment details.',
                        'Authorized user identity and contact details.',
                        'Worker data, permits, or payroll records depending on the selected service.',
                    ],
                    'conditions' => [
                        'Each HRSD/Qiwa service may have its own eligibility checks inside the official platform.',
                        'Some labor services depend on existing establishment status and worker records.',
                    ],
                    'steps' => [
                        ['title' => 'Confirm the establishment record', 'description' => 'Review official establishment identity, activity, and authorized user access.'],
                        ['title' => 'Select the relevant HR service', 'description' => 'Choose the required Qiwa or HRSD service and prepare service-specific data.'],
                        ['title' => 'Submit and monitor', 'description' => 'Complete the request through the official channel and track acceptance, rejection, or missing-data notices.'],
                    ],
                    'duration' => 'Varies by HRSD/Qiwa service and by the completeness of establishment and worker records.',
                    'authorities' => [
                        ['name' => 'Qiwa', 'url' => 'https://qiwa.sa/'],
                        ['name' => 'HRSD', 'url' => 'https://www.hrsd.gov.sa/'],
                        ['name' => 'GOSI', 'url' => 'https://www.gosi.gov.sa/'],
                    ],
                    'faqs' => [
                        ['question' => 'Is Qiwa separate from HRSD?', 'answer' => 'Qiwa is the main digital platform used for many labor services connected to HRSD systems.'],
                        ['question' => 'Why should establishment data be checked first?', 'answer' => 'Many labor services fail or pause when establishment, authorization, or worker records are not aligned.'],
                    ],
                    'related_links' => [
                        ['label' => 'Register in Qiwa', 'route' => 'services.show', 'params' => ['service' => 'register-in-qiwa-68'], 'description' => 'Prepare Qiwa access for establishment services.'],
                        ['label' => 'Wages Protection', 'route' => 'services.platform', 'params' => ['platform' => 'wages-protection'], 'description' => 'Payroll compliance through the official ecosystem.'],
                        ['label' => 'Commercial register service', 'route' => 'services.show', 'params' => ['service' => 'issue-commercial-register-16'], 'description' => 'Start the official business identity.'],
                    ],
                    'official_sources' => ['Qiwa', 'HRSD', 'GOSI'],
                    'source_notes' => 'Service-specific eligibility should be confirmed inside the live official portal before submission.',
                ],
                'ar' => [
                    'eyebrow' => 'مركز خدمات العمل',
                    'title' => 'خدمات الموارد البشرية وقوى للمنشآت',
                    'summary' => 'دليل مختصر للشركات التي تحتاج خدمات ملف العمل وقوى وتصاريح العمل وحماية الأجور دون إنشاء روابط فلاتر أو صفحات مكررة قابلة للفهرسة.',
                    'who_needs' => [
                        'الشركات الجديدة بعد إصدار السجل التجاري وتفعيل ملف المنشأة.',
                        'أصحاب العمل الذين يديرون تصاريح العمل والامتثال والرواتب.',
                        'المنشآت التي تحتاج وصولاً مفوضاً إلى قوى وخدمات الوزارة الرقمية.',
                    ],
                    'requirements' => [
                        'سجل منشأة نشط وصلاحية مستخدم مفوض.',
                        'تطابق بيانات المنشأة والمالك والمدير والعاملين في الأنظمة الرسمية.',
                        'قد يلزم ربط أو مواءمة بيانات التأمينات أو زاتكا حسب نوع الخدمة.',
                    ],
                    'documents' => [
                        'بيانات السجل التجاري أو ملف المنشأة.',
                        'هوية المستخدم المفوض وبيانات التواصل.',
                        'بيانات العاملين أو التصاريح أو الرواتب حسب الخدمة المطلوبة.',
                    ],
                    'conditions' => [
                        'لكل خدمة في قوى أو الوزارة فحوص أهلية خاصة بها داخل المنصة الرسمية.',
                        'تعتمد بعض الخدمات على حالة المنشأة وسجلات العاملين القائمة.',
                    ],
                    'steps' => [
                        ['title' => 'مراجعة ملف المنشأة', 'description' => 'تأكيد هوية المنشأة والنشاط وصلاحيات المستخدم المفوض.'],
                        ['title' => 'اختيار الخدمة المناسبة', 'description' => 'تحديد خدمة قوى أو الوزارة وتجهيز بياناتها الخاصة.'],
                        ['title' => 'التقديم والمتابعة', 'description' => 'إكمال الطلب عبر القناة الرسمية ومتابعة القبول أو الرفض أو نواقص البيانات.'],
                    ],
                    'duration' => 'تختلف حسب نوع خدمة قوى أو الوزارة واكتمال بيانات المنشأة والعاملين.',
                    'authorities' => [
                        ['name' => 'قوى', 'url' => 'https://qiwa.sa/'],
                        ['name' => 'وزارة الموارد البشرية والتنمية الاجتماعية', 'url' => 'https://www.hrsd.gov.sa/'],
                        ['name' => 'التأمينات الاجتماعية', 'url' => 'https://www.gosi.gov.sa/'],
                    ],
                    'faqs' => [
                        ['question' => 'هل قوى منفصلة عن وزارة الموارد البشرية؟', 'answer' => 'قوى هي المنصة الرقمية المستخدمة لكثير من خدمات العمل المرتبطة بأنظمة الوزارة.'],
                        ['question' => 'لماذا نراجع بيانات المنشأة أولاً؟', 'answer' => 'تتعطل كثير من خدمات العمل عند وجود اختلاف في بيانات المنشأة أو الصلاحيات أو سجلات العاملين.'],
                    ],
                    'related_links' => [
                        ['label' => 'التسجيل في قوى', 'route' => 'services.show', 'params' => ['service' => 'register-in-qiwa-68'], 'description' => 'تجهيز وصول المنشأة لخدمات قوى.'],
                        ['label' => 'حماية الأجور', 'route' => 'services.platform', 'params' => ['platform' => 'wages-protection'], 'description' => 'امتثال الرواتب عبر المنظومة الرسمية.'],
                        ['label' => 'إصدار سجل تجاري', 'route' => 'services.show', 'params' => ['service' => 'issue-commercial-register-16'], 'description' => 'بدء الهوية التجارية الرسمية.'],
                    ],
                    'official_sources' => ['قوى', 'وزارة الموارد البشرية والتنمية الاجتماعية', 'التأمينات الاجتماعية'],
                    'source_notes' => 'ينبغي تأكيد أهلية كل خدمة داخل البوابة الرسمية الحية قبل التقديم.',
                ],
            ],
            'gosi' => [
                'en' => [
                    'eyebrow' => 'Social insurance services hub',
                    'title' => 'GOSI services for Saudi establishments and employers',
                    'summary' => 'A practical guide for employers that need to keep the General Organization for Social Insurance (GOSI) file aligned with employee, wage, and contribution data through the official GOSI portal.',
                    'who_needs' => [
                        'Private-sector establishments registering Saudi and non-Saudi workers in social insurance.',
                        'Employers updating wages, employment dates, or worker contracts on GOSI.',
                        'Companies preparing GOSI certificates, end-of-service records, or contribution reconciliations.',
                    ],
                    'requirements' => [
                        'Active establishment record and authorized user access on the GOSI portal.',
                        'Consistent establishment, owner, and worker data across HRSD, Qiwa, and GOSI systems.',
                        'Accurate wage values matching the contracts used for payroll and Wages Protection submissions.',
                    ],
                    'documents' => [
                        'Commercial registration or establishment details.',
                        'Authorized user identity and contact information.',
                        'Worker contracts and payroll data relevant to the requested service.',
                    ],
                    'conditions' => [
                        'Each GOSI service has its own eligibility checks inside the official portal.',
                        'Contribution and registration services may pause when establishment or worker records are incomplete.',
                    ],
                    'steps' => [
                        ['title' => 'Review the establishment file', 'description' => 'Confirm establishment identity, activity, and authorized user access on GOSI.'],
                        ['title' => 'Select the required service', 'description' => 'Choose the GOSI service you need (registration, update, certificate, reconciliation) and prepare its data.'],
                        ['title' => 'Submit and follow up', 'description' => 'Submit through the official GOSI channel and follow up on acceptance, exceptions, or missing data.'],
                    ],
                    'duration' => 'Varies by GOSI service and by the completeness of establishment, worker, and wage records.',
                    'authorities' => [
                        ['name' => 'GOSI', 'url' => 'https://www.gosi.gov.sa/'],
                        ['name' => 'HRSD', 'url' => 'https://www.hrsd.gov.sa/'],
                        ['name' => 'Qiwa', 'url' => 'https://qiwa.sa/'],
                    ],
                    'faqs' => [
                        ['question' => 'Is GOSI registration the same as a labor contract?', 'answer' => 'No. GOSI registration is the social insurance enrollment of a worker; the labor contract is a separate document documented through Qiwa or HRSD systems.'],
                        ['question' => 'Why align GOSI wages with Wages Protection?', 'answer' => 'Wage mismatches between GOSI, payroll, and Wages Protection submissions are a frequent cause of compliance exceptions for employers.'],
                    ],
                    'related_links' => [
                        ['label' => 'HRSD services', 'route' => 'services.platform', 'params' => ['platform' => 'hrsd'], 'description' => 'Labor and establishment compliance hub.'],
                        ['label' => 'Wages Protection', 'route' => 'services.platform', 'params' => ['platform' => 'wages-protection'], 'description' => 'Monthly wage-file compliance through the official ecosystem.'],
                        ['label' => 'Register in Qiwa', 'route' => 'services.show', 'params' => ['service' => 'register-in-qiwa-68'], 'description' => 'Prepare Qiwa access for related labor services.'],
                    ],
                    'official_sources' => ['GOSI', 'HRSD', 'Qiwa'],
                    'source_notes' => 'Service eligibility and fee details should always be confirmed inside the live GOSI portal before submission.',
                ],
                'ar' => [
                    'eyebrow' => 'مركز خدمات التأمينات الاجتماعية',
                    'title' => 'خدمات التأمينات الاجتماعية للمنشآت وأصحاب العمل',
                    'summary' => 'دليل مختصر لأصحاب العمل في المملكة الذين يحتاجون مواءمة ملف التأمينات الاجتماعية مع بيانات العاملين والأجور والاشتراكات عبر البوابة الرسمية للتأمينات.',
                    'who_needs' => [
                        'منشآت القطاع الخاص التي تسجّل عاملين سعوديين أو غير سعوديين في التأمينات.',
                        'أصحاب العمل الذين يحتاجون تحديث الأجور وتواريخ التعيين وعقود العاملين في التأمينات.',
                        'الشركات التي تجهّز شهادات التأمينات أو سجلات نهاية الخدمة أو تسويات الاشتراكات.',
                    ],
                    'requirements' => [
                        'سجل منشأة نشط وصلاحية مستخدم مفوض على بوابة التأمينات.',
                        'تطابق بيانات المنشأة والمالك والعاملين بين أنظمة الموارد البشرية وقوى والتأمينات.',
                        'قيم أجور دقيقة مطابقة للعقود المستخدمة في الرواتب وحماية الأجور.',
                    ],
                    'documents' => [
                        'بيانات السجل التجاري أو ملف المنشأة.',
                        'هوية المستخدم المفوض وبيانات التواصل.',
                        'عقود العاملين وبيانات الرواتب ذات العلاقة بالخدمة المطلوبة.',
                    ],
                    'conditions' => [
                        'لكل خدمة في التأمينات فحوص أهلية خاصة بها داخل البوابة الرسمية.',
                        'قد تتوقف خدمات الاشتراكات والتسجيل عند نقص بيانات المنشأة أو العاملين.',
                    ],
                    'steps' => [
                        ['title' => 'مراجعة ملف المنشأة', 'description' => 'تأكيد هوية المنشأة والنشاط وصلاحيات المستخدم المفوض في بوابة التأمينات.'],
                        ['title' => 'اختيار الخدمة المطلوبة', 'description' => 'اختر خدمة التأمينات اللازمة (تسجيل، تحديث، شهادة، تسوية) وجهّز بياناتها.'],
                        ['title' => 'التقديم والمتابعة', 'description' => 'إكمال الطلب عبر القناة الرسمية للتأمينات ومتابعة القبول أو الاستثناءات أو نواقص البيانات.'],
                    ],
                    'duration' => 'تختلف حسب نوع خدمة التأمينات واكتمال بيانات المنشأة والعاملين والأجور.',
                    'authorities' => [
                        ['name' => 'التأمينات الاجتماعية', 'url' => 'https://www.gosi.gov.sa/'],
                        ['name' => 'وزارة الموارد البشرية والتنمية الاجتماعية', 'url' => 'https://www.hrsd.gov.sa/'],
                        ['name' => 'قوى', 'url' => 'https://qiwa.sa/'],
                    ],
                    'faqs' => [
                        ['question' => 'هل التسجيل في التأمينات نفس عقد العمل؟', 'answer' => 'لا. التسجيل في التأمينات هو قيد اشتراك العامل في التأمينات الاجتماعية، أما عقد العمل فيُوثَّق بشكل مستقل عبر قوى أو أنظمة الوزارة.'],
                        ['question' => 'لماذا نوائم أجور التأمينات مع حماية الأجور؟', 'answer' => 'الفروق بين أجور التأمينات والرواتب وملفات حماية الأجور سبب شائع لاستثناءات الامتثال لدى أصحاب العمل.'],
                    ],
                    'related_links' => [
                        ['label' => 'خدمات الموارد البشرية', 'route' => 'services.platform', 'params' => ['platform' => 'hrsd'], 'description' => 'مركز خدمات العمل والامتثال للمنشآت.'],
                        ['label' => 'حماية الأجور', 'route' => 'services.platform', 'params' => ['platform' => 'wages-protection'], 'description' => 'امتثال ملفات الأجور الشهرية عبر المنظومة الرسمية.'],
                        ['label' => 'التسجيل في قوى', 'route' => 'services.show', 'params' => ['service' => 'register-in-qiwa-68'], 'description' => 'تجهيز وصول المنشأة لخدمات قوى المرتبطة بالعمل.'],
                    ],
                    'official_sources' => ['التأمينات الاجتماعية', 'وزارة الموارد البشرية والتنمية الاجتماعية', 'قوى'],
                    'source_notes' => 'ينبغي تأكيد أهلية كل خدمة وقيم الرسوم داخل بوابة التأمينات الرسمية الحية قبل التقديم.',
                ],
            ],
        ];
    }

    private static function landings(): array
    {
        return [
            'company-formation-riyadh' => [
                'en' => [
                    'eyebrow' => 'Company setup guide',
                    'title' => 'Company formation path in Riyadh',
                    'summary' => 'A source-backed overview of the early formation steps: choosing the entity path, preparing partner documents, issuing the commercial registration, and aligning post-registration files.',
                    'who_needs' => [
                        'Founders starting a Saudi company or sole proprietorship.',
                        'GCC or foreign investors planning an entity that may require additional approvals.',
                        'Businesses that need commercial registration, licensing, tax, and labor setup to be aligned from day one.',
                    ],
                    'requirements' => [
                        'The activity and entity type should be selected before submission.',
                        'Activities that require pre-approval must be supported by the competent authority approval.',
                        'Foreign-owned structures may require an investment registration or related approval before company formation.',
                    ],
                    'documents' => [
                        'Owner, partner, manager, and authorized representative details.',
                        'National address or business location data.',
                        'Power of attorney or authorization where a representative submits the application.',
                        'Foreign parent-company documents when the structure depends on a foreign shareholder.',
                    ],
                    'conditions' => [
                        'Some activities cannot be issued until the relevant licensing authority approves the activity.',
                        'Commercial registration data should match the entity objectives and approved activity.',
                    ],
                    'steps' => [
                        ['title' => 'Define the legal path', 'description' => 'Choose entity type, ownership structure, activity, and whether a pre-license is needed.'],
                        ['title' => 'Prepare partner documents', 'description' => 'Collect identities, authorizations, foreign company papers, and address data as applicable.'],
                        ['title' => 'Submit through the official channel', 'description' => 'File the commercial registration or company establishment request through the official business platform.'],
                        ['title' => 'Align post-registration files', 'description' => 'Prepare tax, labor, insurance, chamber, municipal, and platform accounts after issuance.'],
                    ],
                    'duration' => 'Can be instant for some commercial registration paths; regulated activities and foreign structures depend on official approvals.',
                    'authorities' => [
                        ['name' => 'Saudi Business Center', 'url' => 'https://business.sa/'],
                        ['name' => 'Ministry of Commerce', 'url' => 'https://mc.gov.sa/'],
                        ['name' => 'MISA', 'url' => 'https://misa.gov.sa/'],
                    ],
                    'faqs' => [
                        ['question' => 'Does every activity need a license before formation?', 'answer' => 'No. Only activities classified by the competent authority as requiring pre-approval need that approval before issuance.'],
                        ['question' => 'What happens after the commercial registration is issued?', 'answer' => 'The business usually needs to align tax, labor, social insurance, chamber, address, and activity-specific license files.'],
                    ],
                    'related_links' => [
                        ['label' => 'Commercial register issuance', 'route' => 'services.show', 'params' => ['service' => 'issue-commercial-register-16'], 'description' => 'Prepare the commercial registration path.'],
                        ['label' => 'Foreign investment setup', 'route' => 'landing.foreign_investment', 'description' => 'Plan MISA-related investment requirements.'],
                        ['label' => 'ZATCA registration', 'route' => 'services.show', 'params' => ['service' => 'zakat-registration-89'], 'description' => 'Prepare post-registration tax setup.'],
                        ['label' => 'Qiwa registration', 'route' => 'services.show', 'params' => ['service' => 'register-in-qiwa-68'], 'description' => 'Prepare labor platform access.'],
                    ],
                    'official_sources' => ['Saudi Business Center', 'Ministry of Commerce', 'MISA'],
                    'source_notes' => 'Entity-specific requirements vary by activity, shareholder type, and official approvals.',
                ],
                'ar' => [
                    'eyebrow' => 'دليل تأسيس الشركات',
                    'title' => 'مسار تأسيس الشركات في الرياض',
                    'summary' => 'عرض مختصر مبني على المصادر الرسمية لمراحل التأسيس الأولى: اختيار المسار، تجهيز مستندات الشركاء، إصدار السجل، ومواءمة الملفات بعد الإصدار.',
                    'who_needs' => [
                        'رواد الأعمال الذين يبدأون شركة أو مؤسسة داخل المملكة.',
                        'المستثمرون الخليجيون أو الأجانب ممن قد يحتاجون موافقات إضافية.',
                        'الشركات التي تريد مواءمة السجل والرخص والضرائب والعمل منذ البداية.',
                    ],
                    'requirements' => [
                        'تحديد النشاط ونوع الكيان قبل التقديم.',
                        'إرفاق موافقة الجهة المختصة إذا كان النشاط يتطلب ترخيصاً قبل الإصدار.',
                        'قد يتطلب الهيكل الأجنبي شهادة تسجيل استثمارية أو موافقة مرتبطة قبل التأسيس.',
                    ],
                    'documents' => [
                        'بيانات الملاك والشركاء والمدراء والممثلين المفوضين.',
                        'العنوان الوطني أو بيانات موقع النشاط.',
                        'وكالة أو تفويض عند تقديم الطلب بواسطة ممثل.',
                        'مستندات الشركة الأجنبية الأم عند وجود شريك أجنبي.',
                    ],
                    'conditions' => [
                        'بعض الأنشطة لا تصدر إلا بعد موافقة الجهة المرخصة المختصة.',
                        'يجب أن تتوافق بيانات السجل مع أغراض الكيان والنشاط المعتمد.',
                    ],
                    'steps' => [
                        ['title' => 'تحديد المسار القانوني', 'description' => 'اختيار نوع الكيان والملكية والنشاط والتحقق من الحاجة لترخيص مسبق.'],
                        ['title' => 'تجهيز مستندات الشركاء', 'description' => 'جمع الهويات والتفويضات ومستندات الشركة الأجنبية وبيانات العنوان عند الحاجة.'],
                        ['title' => 'التقديم عبر القناة الرسمية', 'description' => 'تقديم طلب السجل أو تأسيس الشركة عبر منصة الأعمال الرسمية.'],
                        ['title' => 'مواءمة ملفات ما بعد الإصدار', 'description' => 'تجهيز ملفات الزكاة والضريبة والعمل والتأمينات والغرفة والرخص المرتبطة.'],
                    ],
                    'duration' => 'قد تكون فورية لبعض مسارات السجل، بينما تعتمد الأنشطة المنظمة والهياكل الأجنبية على الموافقات الرسمية.',
                    'authorities' => [
                        ['name' => 'المركز السعودي للأعمال', 'url' => 'https://business.sa/'],
                        ['name' => 'وزارة التجارة', 'url' => 'https://mc.gov.sa/'],
                        ['name' => 'وزارة الاستثمار', 'url' => 'https://misa.gov.sa/'],
                    ],
                    'faqs' => [
                        ['question' => 'هل كل نشاط يحتاج ترخيصاً قبل التأسيس؟', 'answer' => 'لا. فقط الأنشطة المصنفة من الجهة المختصة على أنها تحتاج موافقة مسبقة قبل الإصدار.'],
                        ['question' => 'ماذا يحدث بعد إصدار السجل التجاري؟', 'answer' => 'عادة تحتاج المنشأة إلى مواءمة ملفات الضريبة والعمل والتأمينات والغرفة والعنوان والرخص حسب النشاط.'],
                    ],
                    'related_links' => [
                        ['label' => 'إصدار سجل تجاري', 'route' => 'services.show', 'params' => ['service' => 'issue-commercial-register-16'], 'description' => 'تجهيز مسار السجل التجاري.'],
                        ['label' => 'الاستثمار الأجنبي', 'route' => 'landing.foreign_investment', 'description' => 'تخطيط متطلبات وزارة الاستثمار.'],
                        ['label' => 'التسجيل لدى زاتكا', 'route' => 'services.show', 'params' => ['service' => 'zakat-registration-89'], 'description' => 'تجهيز الملف الضريبي بعد الإصدار.'],
                        ['label' => 'التسجيل في قوى', 'route' => 'services.show', 'params' => ['service' => 'register-in-qiwa-68'], 'description' => 'تجهيز ملف العمل والوصول للمنصة.'],
                    ],
                    'official_sources' => ['المركز السعودي للأعمال', 'وزارة التجارة', 'وزارة الاستثمار'],
                    'source_notes' => 'تختلف المتطلبات التفصيلية حسب النشاط ونوع المالك والموافقات الرسمية.',
                ],
            ],
            'foreign-investment' => [
                'en' => [
                    'eyebrow' => 'Foreign investor setup',
                    'title' => 'Foreign investment licensing and company setup',
                    'summary' => 'A practical guide for foreign investors planning the MISA path, Saudi company formation, activity compatibility, and post-license government files.',
                    'who_needs' => [
                        'Foreign companies planning to establish a Saudi entity.',
                        'Investors whose activity requires a Ministry of Investment registration before incorporation.',
                        'Companies that need activity, ownership, and commercial registration data to match the investment approval.',
                    ],
                    'requirements' => [
                        'The foreign investment registration path should match the planned activity.',
                        'Foreign company and shareholder documents may need attestation depending on structure and origin.',
                        'Some regulated activities require approval from the competent Saudi authority.',
                    ],
                    'documents' => [
                        'Foreign parent-company incorporation documents where applicable.',
                        'Shareholder and authorized representative data.',
                        'Activity description and supporting approvals for regulated sectors.',
                        'Investment registration certificate where required for company formation.',
                    ],
                    'conditions' => [
                        'The commercial registration activity should be compatible with the investment registration.',
                        'Activities governed by specialized regulators may require additional approvals before or during setup.',
                    ],
                    'steps' => [
                        ['title' => 'Map the investment activity', 'description' => 'Confirm whether the planned activity is open, regulated, or requires a specialized approval.'],
                        ['title' => 'Prepare investor documents', 'description' => 'Collect corporate documents, authorizations, and representative data.'],
                        ['title' => 'Complete the MISA path', 'description' => 'Submit the investment registration or related request through the official investment channel.'],
                        ['title' => 'Form the Saudi entity', 'description' => 'Use the approved investment path to continue commercial registration and post-registration files.'],
                    ],
                    'duration' => 'Depends on investor structure, activity, document readiness, and official authority review.',
                    'authorities' => [
                        ['name' => 'MISA', 'url' => 'https://misa.gov.sa/'],
                        ['name' => 'Saudi Business Center', 'url' => 'https://business.sa/'],
                        ['name' => 'Ministry of Commerce', 'url' => 'https://mc.gov.sa/'],
                    ],
                    'faqs' => [
                        ['question' => 'Can a foreign investor form a company before the investment path is ready?', 'answer' => 'For structures that require investment registration, the investment path should be aligned before company formation proceeds.'],
                        ['question' => 'Are all sectors treated the same?', 'answer' => 'No. Regulated activities may require approvals from specialized authorities in addition to MISA and commercial registration steps.'],
                    ],
                    'related_links' => [
                        ['label' => 'Company formation in Riyadh', 'route' => 'public.landing.company-formation-riyadh', 'description' => 'Plan the entity and post-registration path.'],
                        ['label' => 'Commercial register issuance', 'route' => 'services.show', 'params' => ['service' => 'issue-commercial-register-16'], 'description' => 'Prepare the commercial registration step.'],
                        ['label' => 'ZATCA registration', 'route' => 'services.show', 'params' => ['service' => 'zakat-registration-89'], 'description' => 'Set up tax files after formation.'],
                    ],
                    'official_sources' => ['MISA', 'Saudi Business Center', 'Ministry of Commerce'],
                    'source_notes' => 'Foreign investor requirements can change by activity, nationality, and regulator review.',
                ],
                'ar' => [
                    'eyebrow' => 'تأسيس المستثمر الأجنبي',
                    'title' => 'ترخيص الاستثمار الأجنبي وتأسيس الشركة',
                    'summary' => 'دليل عملي للمستثمر الأجنبي لتخطيط مسار وزارة الاستثمار، وتأسيس الكيان السعودي، ومواءمة النشاط والملفات الحكومية بعد الترخيص.',
                    'who_needs' => [
                        'الشركات الأجنبية التي تخطط لتأسيس كيان سعودي.',
                        'المستثمرون الذين يتطلب نشاطهم تسجيلاً استثمارياً قبل التأسيس.',
                        'الشركات التي تحتاج توافق بيانات النشاط والملكية والسجل التجاري مع الموافقة الاستثمارية.',
                    ],
                    'requirements' => [
                        'يجب أن يتوافق مسار التسجيل الاستثماري مع النشاط المخطط.',
                        'قد تتطلب مستندات الشركة الأجنبية والشركاء تصديقات حسب الهيكل والدولة.',
                        'بعض الأنشطة المنظمة تحتاج موافقة من الجهة السعودية المختصة.',
                    ],
                    'documents' => [
                        'مستندات تأسيس الشركة الأجنبية الأم عند الحاجة.',
                        'بيانات الشركاء والممثل المفوض.',
                        'وصف النشاط والموافقات الداعمة للقطاعات المنظمة.',
                        'شهادة التسجيل الاستثماري عند اشتراطها للتأسيس.',
                    ],
                    'conditions' => [
                        'يجب أن يتوافق نشاط السجل التجاري مع التسجيل الاستثماري.',
                        'الأنشطة الخاضعة لمنظمين متخصصين قد تتطلب موافقات إضافية قبل أو أثناء التأسيس.',
                    ],
                    'steps' => [
                        ['title' => 'تحليل النشاط الاستثماري', 'description' => 'تأكيد ما إذا كان النشاط مفتوحاً أو منظماً أو يحتاج موافقة خاصة.'],
                        ['title' => 'تجهيز مستندات المستثمر', 'description' => 'جمع مستندات الشركة والتفويضات وبيانات الممثل.'],
                        ['title' => 'إكمال مسار وزارة الاستثمار', 'description' => 'تقديم طلب التسجيل الاستثماري أو الطلب المرتبط عبر القناة الرسمية.'],
                        ['title' => 'تأسيس الكيان السعودي', 'description' => 'استخدام المسار الاستثماري المعتمد لاستكمال السجل والملفات اللاحقة.'],
                    ],
                    'duration' => 'تعتمد على هيكل المستثمر والنشاط وجاهزية المستندات ومراجعة الجهات الرسمية.',
                    'authorities' => [
                        ['name' => 'وزارة الاستثمار', 'url' => 'https://misa.gov.sa/'],
                        ['name' => 'المركز السعودي للأعمال', 'url' => 'https://business.sa/'],
                        ['name' => 'وزارة التجارة', 'url' => 'https://mc.gov.sa/'],
                    ],
                    'faqs' => [
                        ['question' => 'هل يمكن للمستثمر الأجنبي تأسيس الشركة قبل جاهزية مسار الاستثمار؟', 'answer' => 'في الهياكل التي تتطلب تسجيلاً استثمارياً، يجب مواءمة مسار الاستثمار قبل استكمال التأسيس.'],
                        ['question' => 'هل كل القطاعات لها المتطلبات نفسها؟', 'answer' => 'لا. الأنشطة المنظمة قد تحتاج موافقات من جهات مختصة إضافة إلى وزارة الاستثمار والسجل التجاري.'],
                    ],
                    'related_links' => [
                        ['label' => 'تأسيس شركة في الرياض', 'route' => 'public.landing.company-formation-riyadh', 'description' => 'تخطيط الكيان وملفات ما بعد التأسيس.'],
                        ['label' => 'إصدار سجل تجاري', 'route' => 'services.show', 'params' => ['service' => 'issue-commercial-register-16'], 'description' => 'تجهيز خطوة السجل التجاري.'],
                        ['label' => 'التسجيل لدى زاتكا', 'route' => 'services.show', 'params' => ['service' => 'zakat-registration-89'], 'description' => 'إعداد الملف الضريبي بعد التأسيس.'],
                    ],
                    'official_sources' => ['وزارة الاستثمار', 'المركز السعودي للأعمال', 'وزارة التجارة'],
                    'source_notes' => 'قد تختلف متطلبات المستثمر الأجنبي حسب النشاط والجنسية ومراجعة الجهة المنظمة.',
                ],
            ],
        ];
    }

    private static function services(): array
    {
        return [
            'issue-commercial-register-16' => self::commercialRegister(),
            'register-in-qiwa-68' => self::qiwaRegistration(),
            'register-in-muqeem-34' => self::muqeemRegistration(),
            'zakat-registration-89' => self::zatcaRegistration(),
            'issue-commercial-license-56' => self::commercialLicense(),
            'trademark-registration-10' => self::trademarkRegistration(),
        ];
    }

    private static function commercialRegister(): array
    {
        return [
            'en' => [
                'eyebrow' => 'Ministry of Commerce service',
                'title' => 'Commercial registration issuance',
                'summary' => 'Prepare the official commercial registration path with activity, owner, address, manager, and licensing data aligned before submission.',
                'who_needs' => ['Founders starting a sole proprietorship or commercial activity.', 'Businesses that need a legal commercial identity before tax, labor, or licensing setup.'],
                'requirements' => ['Applicant age should meet the official minimum for this service.', 'The applicant should not hold a government position for the sole proprietorship path.', 'Activities that require pre-approval need the relevant licensing authority approval.'],
                'documents' => ['Owner and manager identity details.', 'Business address and contact data.', 'Activity details, capital data, and e-commerce declaration when applicable.', 'Authorization or special supporting documents for associations, charities, or endowment cases.'],
                'conditions' => ['The owner should not already have an active sole proprietorship commercial registration for the same path.', 'The selected activity should be compatible with the requested registration.'],
                'steps' => [
                    ['title' => 'Enter the official business platform', 'description' => 'Access the commercial registration service through the official Saudi business channel.'],
                    ['title' => 'Complete owner and activity data', 'description' => 'Add owner, manager, address, activity, capital, and e-commerce details where relevant.'],
                    ['title' => 'Submit and pay', 'description' => 'Review the application, approve declarations, pay the official invoice, and receive the registration when accepted.'],
                ],
                'duration' => 'Instant for the standard sole-proprietorship path when data and approvals are complete.',
                'authorities' => [['name' => 'Saudi Business Center', 'url' => 'https://business.sa/en/eservices/details/ee829025-1253-41d3-f9fc-08dd6ab9228a'], ['name' => 'Ministry of Commerce', 'url' => 'https://mc.gov.sa/']],
                'faqs' => [['question' => 'Does every activity need a pre-license?', 'answer' => 'No. Pre-approval is needed only when the selected activity is classified as requiring a license before issuance.'], ['question' => 'What files are usually connected after issuance?', 'answer' => 'The business may need tax, labor, insurance, chamber, address, and municipal licensing files depending on its activity.']],
                'related_links' => [['label' => 'Company formation in Riyadh', 'route' => 'public.landing.company-formation-riyadh'], ['label' => 'ZATCA registration', 'route' => 'services.show', 'params' => ['service' => 'zakat-registration-89']], ['label' => 'Issue commercial license', 'route' => 'services.show', 'params' => ['service' => 'issue-commercial-license-56']], ['label' => 'Qiwa registration', 'route' => 'services.show', 'params' => ['service' => 'register-in-qiwa-68']]],
                'official_sources' => ['Saudi Business Center', 'Ministry of Commerce'],
                'source_notes' => 'Company and foreign-investor paths may have additional requirements beyond the sole-proprietorship path.',
            ],
            'ar' => [
                'eyebrow' => 'خدمة وزارة التجارة',
                'title' => 'إصدار السجل التجاري',
                'summary' => 'تجهيز مسار إصدار السجل التجاري الرسمي مع مواءمة النشاط والمالك والعنوان والمدير وبيانات الترخيص قبل التقديم.',
                'who_needs' => ['رواد الأعمال الذين يبدأون مؤسسة أو نشاطاً تجارياً.', 'المنشآت التي تحتاج هوية تجارية نظامية قبل الضريبة والعمل والرخص.'],
                'requirements' => ['أن يحقق مقدم الطلب الحد العمري الرسمي للخدمة.', 'ألا يكون مقدم الطلب موظفاً حكومياً في مسار المؤسسة الفردية.', 'إرفاق موافقة الجهة المرخصة إذا كان النشاط يتطلب ترخيصاً مسبقاً.'],
                'documents' => ['بيانات هوية المالك والمدير.', 'العنوان وبيانات التواصل.', 'بيانات النشاط ورأس المال والتجارة الإلكترونية عند وجودها.', 'تفويض أو مستندات داعمة خاصة للجمعيات أو المؤسسات الوقفية عند الحاجة.'],
                'conditions' => ['ألا يكون لدى المالك سجل مؤسسة فردية نشط في المسار نفسه.', 'أن يتوافق النشاط المختار مع السجل المطلوب.'],
                'steps' => [
                    ['title' => 'الدخول للمنصة الرسمية', 'description' => 'الوصول إلى خدمة السجل التجاري عبر القناة الرسمية للأعمال.'],
                    ['title' => 'إكمال بيانات المالك والنشاط', 'description' => 'إضافة بيانات المالك والمدير والعنوان والنشاط ورأس المال والتجارة الإلكترونية عند الحاجة.'],
                    ['title' => 'التقديم والدفع', 'description' => 'مراجعة الطلب والموافقة على الإقرارات وسداد الفاتورة الرسمية واستلام السجل عند القبول.'],
                ],
                'duration' => 'فوري في مسار المؤسسة الفردية القياسي عند اكتمال البيانات والموافقات.',
                'authorities' => [['name' => 'المركز السعودي للأعمال', 'url' => 'https://business.sa/'], ['name' => 'وزارة التجارة', 'url' => 'https://mc.gov.sa/']],
                'faqs' => [['question' => 'هل كل نشاط يحتاج ترخيصاً مسبقاً؟', 'answer' => 'لا. يحتاج الترخيص المسبق فقط النشاط المصنف من الجهة المختصة بأنه يتطلب موافقة قبل الإصدار.'], ['question' => 'ما الملفات التي ترتبط غالباً بعد إصدار السجل؟', 'answer' => 'قد تحتاج المنشأة ملفات الضريبة والعمل والتأمينات والغرفة والعنوان والرخص البلدية حسب النشاط.']],
                'related_links' => [['label' => 'تأسيس شركة في الرياض', 'route' => 'public.landing.company-formation-riyadh'], ['label' => 'التسجيل لدى زاتكا', 'route' => 'services.show', 'params' => ['service' => 'zakat-registration-89']], ['label' => 'إصدار رخصة تجارية', 'route' => 'services.show', 'params' => ['service' => 'issue-commercial-license-56']], ['label' => 'التسجيل في قوى', 'route' => 'services.show', 'params' => ['service' => 'register-in-qiwa-68']]],
                'official_sources' => ['المركز السعودي للأعمال', 'وزارة التجارة'],
                'source_notes' => 'قد تختلف متطلبات الشركات والمستثمر الأجنبي عن مسار المؤسسة الفردية.',
            ],
        ];
    }

    private static function qiwaRegistration(): array
    {
        return [
            'en' => [
                'eyebrow' => 'Qiwa and HRSD service',
                'title' => 'Register and prepare an establishment on Qiwa',
                'summary' => 'Prepare Qiwa access so the establishment can manage labor services, worker records, permits, and compliance tasks connected to HRSD systems.',
                'who_needs' => ['New employers after commercial registration and labor-file setup.', 'Businesses that need to manage labor services through Qiwa.', 'Authorized users responsible for permits, contracts, and wage-related compliance.'],
                'requirements' => ['The establishment data should be active and consistent in the official labor ecosystem.', 'The user submitting or following up should have valid authorization.', 'Related worker and insurance records should be reviewed where the selected service depends on them.'],
                'documents' => ['Commercial registration or establishment details.', 'Authorized user identity and contact information.', 'Worker records or permit data required by the selected Qiwa service.'],
                'conditions' => ['Qiwa services are tied to HRSD labor records and may pause when establishment or worker data is incomplete.', 'Some requests depend on GOSI or other official records being aligned.'],
                'steps' => [
                    ['title' => 'Confirm establishment identity', 'description' => 'Review commercial, labor, and authorized-user data before using Qiwa services.'],
                    ['title' => 'Prepare the service file', 'description' => 'Collect worker, permit, contract, or wage data required for the selected request.'],
                    ['title' => 'Submit and monitor status', 'description' => 'Complete the request in Qiwa and follow acceptance, rejection, or missing-data notices.'],
                ],
                'duration' => 'Depends on the selected Qiwa service and the readiness of establishment and worker records.',
                'authorities' => [['name' => 'Qiwa', 'url' => 'https://qiwa.sa/'], ['name' => 'HRSD', 'url' => 'https://www.hrsd.gov.sa/'], ['name' => 'GOSI', 'url' => 'https://www.gosi.gov.sa/']],
                'faqs' => [['question' => 'Why does Qiwa data need to match other records?', 'answer' => 'Labor services can depend on establishment, worker, and insurance records, so mismatched data can delay the request.'], ['question' => 'Is Qiwa only for new companies?', 'answer' => 'No. Existing employers also use Qiwa for labor services, permits, contracts, and compliance follow-up.']],
                'related_links' => [['label' => 'HRSD services', 'route' => 'services.platform', 'params' => ['platform' => 'hrsd'], 'description' => 'Labor and establishment compliance hub.'], ['label' => 'Wages Protection', 'route' => 'services.platform', 'params' => ['platform' => 'wages-protection'], 'description' => 'Monthly wage-file compliance.'], ['label' => 'Commercial register issuance', 'route' => 'services.show', 'params' => ['service' => 'issue-commercial-register-16'], 'description' => 'Start the official business identity.']],
                'official_sources' => ['Qiwa', 'HRSD', 'GOSI'],
                'source_notes' => 'Exact checks differ by Qiwa service and should be confirmed in the official platform before submission.',
            ],
            'ar' => [
                'eyebrow' => 'خدمة قوى والموارد البشرية',
                'title' => 'تسجيل وتجهيز منشأة في قوى',
                'summary' => 'تجهيز وصول المنشأة إلى قوى لإدارة خدمات العمل وسجلات العاملين والتصاريح ومهام الامتثال المرتبطة بأنظمة وزارة الموارد البشرية.',
                'who_needs' => ['أصحاب العمل الجدد بعد السجل التجاري وإعداد ملف العمل.', 'المنشآت التي تدير خدمات العمل عبر قوى.', 'المستخدمون المفوضون لمتابعة التصاريح والعقود والامتثال المرتبط بالأجور.'],
                'requirements' => ['أن تكون بيانات المنشأة نشطة ومتسقة في منظومة العمل الرسمية.', 'أن يملك مقدم الطلب أو المتابع صلاحية صحيحة.', 'مراجعة بيانات العاملين والتأمينات عند اعتماد الخدمة المختارة عليها.'],
                'documents' => ['بيانات السجل التجاري أو المنشأة.', 'هوية المستخدم المفوض وبيانات التواصل.', 'بيانات العاملين أو التصاريح التي تطلبها خدمة قوى المحددة.'],
                'conditions' => ['ترتبط خدمات قوى بسجلات العمل وقد تتوقف عند نقص بيانات المنشأة أو العامل.', 'قد تعتمد بعض الطلبات على مواءمة بيانات التأمينات أو سجلات رسمية أخرى.'],
                'steps' => [
                    ['title' => 'تأكيد هوية المنشأة', 'description' => 'مراجعة بيانات السجل والعمل والمستخدم المفوض قبل استخدام خدمات قوى.'],
                    ['title' => 'تجهيز ملف الخدمة', 'description' => 'جمع بيانات العامل أو التصريح أو العقد أو الأجور حسب الطلب.'],
                    ['title' => 'التقديم ومتابعة الحالة', 'description' => 'إكمال الطلب في قوى ومتابعة القبول أو الرفض أو نواقص البيانات.'],
                ],
                'duration' => 'تعتمد على نوع خدمة قوى وجاهزية بيانات المنشأة والعاملين.',
                'authorities' => [['name' => 'قوى', 'url' => 'https://qiwa.sa/'], ['name' => 'وزارة الموارد البشرية والتنمية الاجتماعية', 'url' => 'https://www.hrsd.gov.sa/'], ['name' => 'التأمينات الاجتماعية', 'url' => 'https://www.gosi.gov.sa/']],
                'faqs' => [['question' => 'لماذا يجب تطابق بيانات قوى مع سجلات أخرى؟', 'answer' => 'قد تعتمد خدمات العمل على سجلات المنشأة والعامل والتأمينات، لذلك تؤخر البيانات غير المتطابقة الطلب.'], ['question' => 'هل قوى للشركات الجديدة فقط؟', 'answer' => 'لا. تستخدمها المنشآت القائمة أيضاً لخدمات العمل والتصاريح والعقود ومتابعة الامتثال.']],
                'related_links' => [['label' => 'خدمات الموارد البشرية', 'route' => 'services.platform', 'params' => ['platform' => 'hrsd'], 'description' => 'مركز خدمات العمل والامتثال.'], ['label' => 'حماية الأجور', 'route' => 'services.platform', 'params' => ['platform' => 'wages-protection'], 'description' => 'امتثال ملفات الأجور الشهرية.'], ['label' => 'إصدار سجل تجاري', 'route' => 'services.show', 'params' => ['service' => 'issue-commercial-register-16'], 'description' => 'بدء الهوية التجارية الرسمية.']],
                'official_sources' => ['قوى', 'وزارة الموارد البشرية والتنمية الاجتماعية', 'التأمينات الاجتماعية'],
                'source_notes' => 'تختلف الفحوص التفصيلية حسب خدمة قوى ويجب تأكيدها داخل المنصة الرسمية قبل التقديم.',
            ],
        ];
    }

    private static function muqeemRegistration(): array
    {
        return [
            'en' => [
                'eyebrow' => 'Muqeem and residency services',
                'title' => 'Muqeem registration for establishments',
                'summary' => 'Prepare establishment access and resident-worker data before using Muqeem or related Absher Business residency services.',
                'who_needs' => ['Employers managing resident-worker services.', 'Companies that need establishment access to Muqeem-related transactions.', 'Authorized representatives preparing iqama, passport, or resident status requests.'],
                'requirements' => ['Authorized business access should be ready before submission.', 'Resident identity, passport, work-permit, insurance, and status data may be checked depending on the service.', 'Worker records should not conflict with official absence or eligibility statuses for residency-related services.'],
                'documents' => ['Establishment and authorized-user details.', 'Resident identity or passport data depending on the request.', 'Payment, insurance, or work-permit evidence where the selected service requires it.'],
                'conditions' => ['Absher Business guidance for resident identity renewal references valid passport, no unpaid traffic violations, valid work permit, and insurance checks.', 'Muqeem service eligibility can vary by transaction type and resident status.'],
                'steps' => [
                    ['title' => 'Review establishment access', 'description' => 'Confirm the business account and authorized user before starting resident services.'],
                    ['title' => 'Check resident data', 'description' => 'Review iqama, passport, work-permit, insurance, and payment status as required by the transaction.'],
                    ['title' => 'Submit through the official channel', 'description' => 'Use Muqeem or the relevant official platform and monitor request status.'],
                ],
                'duration' => 'Often immediate for eligible electronic transactions, but final timing depends on the selected official service.',
                'authorities' => [['name' => 'Muqeem', 'url' => 'https://muqeem.sa/'], ['name' => 'Absher Business', 'url' => 'https://www.absher.sa/portal/landing.html'], ['name' => 'HRSD', 'url' => 'https://www.hrsd.gov.sa/']],
                'faqs' => [['question' => 'Is Muqeem the same as Absher Business?', 'answer' => 'No. They are separate official service channels, but some resident-worker transactions rely on related Ministry of Interior or labor data.'], ['question' => 'What causes delays in residency transactions?', 'answer' => 'Delays commonly relate to missing authorization, invalid passport or work-permit data, insurance issues, or eligibility checks.']],
                'related_links' => [['label' => 'Qiwa registration', 'route' => 'services.show', 'params' => ['service' => 'register-in-qiwa-68'], 'description' => 'Prepare labor data connected to worker services.'], ['label' => 'Company formation', 'route' => 'public.landing.company-formation-riyadh', 'description' => 'Align setup files from the start.']],
                'official_sources' => ['Muqeem', 'Absher Business', 'HRSD'],
                'source_notes' => 'The exact checks depend on the chosen resident-service transaction.',
            ],
            'ar' => [
                'eyebrow' => 'خدمات مقيم والإقامة',
                'title' => 'التسجيل في مقيم للمنشآت',
                'summary' => 'تجهيز وصول المنشأة وبيانات العامل المقيم قبل استخدام مقيم أو خدمات أبشر أعمال المرتبطة بالإقامة.',
                'who_needs' => ['أصحاب العمل الذين يديرون خدمات العاملين المقيمين.', 'الشركات التي تحتاج وصولاً لمنصة مقيم ومعاملاتها.', 'الممثلون المفوضون لتجهيز طلبات الإقامة أو الجواز أو حالة المقيم.'],
                'requirements' => ['جاهزية صلاحية الدخول المفوضة للمنشأة قبل التقديم.', 'قد يتم فحص بيانات الإقامة والجواز ورخصة العمل والتأمين والحالة حسب الخدمة.', 'ينبغي ألا تتعارض سجلات العامل مع حالات الغياب أو الأهلية الرسمية للخدمات المرتبطة بالإقامة.'],
                'documents' => ['بيانات المنشأة والمستخدم المفوض.', 'بيانات الإقامة أو الجواز حسب نوع الطلب.', 'إثباتات السداد أو التأمين أو رخصة العمل عند اشتراط الخدمة.'],
                'conditions' => ['تشير إرشادات أبشر أعمال لتجديد هوية مقيم إلى فحوص مثل صلاحية الجواز، عدم وجود مخالفات مرورية غير مسددة، صلاحية رخصة العمل، والتأمين.', 'تختلف أهلية خدمات مقيم حسب نوع المعاملة وحالة المقيم.'],
                'steps' => [
                    ['title' => 'مراجعة وصول المنشأة', 'description' => 'تأكيد حساب الأعمال والمستخدم المفوض قبل بدء خدمات المقيمين.'],
                    ['title' => 'فحص بيانات المقيم', 'description' => 'مراجعة الإقامة والجواز ورخصة العمل والتأمين والسداد حسب المعاملة.'],
                    ['title' => 'التقديم عبر القناة الرسمية', 'description' => 'استخدام مقيم أو المنصة الرسمية المناسبة ومتابعة حالة الطلب.'],
                ],
                'duration' => 'غالباً تكون فورية للمعاملات الإلكترونية المؤهلة، لكن التوقيت النهائي يعتمد على الخدمة المختارة.',
                'authorities' => [['name' => 'مقيم', 'url' => 'https://muqeem.sa/'], ['name' => 'أبشر أعمال', 'url' => 'https://www.absher.sa/portal/landing.html'], ['name' => 'وزارة الموارد البشرية والتنمية الاجتماعية', 'url' => 'https://www.hrsd.gov.sa/']],
                'faqs' => [['question' => 'هل مقيم هو نفسه أبشر أعمال؟', 'answer' => 'لا. هما قناتان رسميتان منفصلتان، لكن بعض معاملات العامل المقيم تعتمد على بيانات وزارة الداخلية أو العمل ذات الصلة.'], ['question' => 'ما أكثر أسباب تأخر معاملات الإقامة؟', 'answer' => 'غالباً تكون بسبب نقص الصلاحية أو بيانات جواز أو رخصة عمل غير صالحة أو مشكلة تأمين أو فحوص أهلية.']],
                'related_links' => [['label' => 'التسجيل في قوى', 'route' => 'services.show', 'params' => ['service' => 'register-in-qiwa-68'], 'description' => 'تجهيز بيانات العمل المرتبطة بخدمات العاملين.'], ['label' => 'تأسيس الشركات', 'route' => 'public.landing.company-formation-riyadh', 'description' => 'مواءمة ملفات التأسيس من البداية.']],
                'official_sources' => ['مقيم', 'أبشر أعمال', 'وزارة الموارد البشرية والتنمية الاجتماعية'],
                'source_notes' => 'تعتمد الفحوص الدقيقة على معاملة خدمة المقيم المختارة.',
            ],
        ];
    }

    private static function zatcaRegistration(): array
    {
        return [
            'en' => [
                'eyebrow' => 'ZATCA tax file setup',
                'title' => 'ZATCA registration and taxpayer account setup',
                'summary' => 'Prepare the ZATCA taxpayer file after commercial registration so the establishment can complete tax-account access and future filing obligations.',
                'who_needs' => ['New establishments after commercial registration.', 'Foreign establishments or companies that must complete income-tax or related ZATCA registration paths.', 'Businesses preparing VAT, zakat, or tax account access through ZATCA.'],
                'requirements' => ['Commercial registration should be completed first for the establishment path.', 'ZATCA may generate taxpayer data after Ministry of Commerce registration and require portal access completion.', 'Companies may need establishment-contract information depending on the selected tax service.'],
                'documents' => ['Commercial registration data.', 'Establishment contract for companies where required.', 'Authorized user and contact details for ZATCA portal access.'],
                'conditions' => ['The selected tax-registration path should match the taxpayer type and business activity.', 'VAT registration thresholds are separate from the basic tax-file setup and must be checked under ZATCA VAT rules when relevant.'],
                'steps' => [
                    ['title' => 'Complete commercial setup', 'description' => 'Finish Ministry of Commerce registration before opening or completing the taxpayer file.'],
                    ['title' => 'Access the ZATCA portal', 'description' => 'Use the official ZATCA e-portal to complete the required account data.'],
                    ['title' => 'Receive confirmation', 'description' => 'Follow the official request until the taxpayer account or registration confirmation is issued.'],
                ],
                'duration' => 'Depends on the tax service path, taxpayer type, and completeness of Ministry of Commerce data.',
                'authorities' => [['name' => 'ZATCA', 'url' => 'https://www.zatca.gov.sa/en/eServices/Pages/eServices_029.aspx'], ['name' => 'Ministry of Commerce', 'url' => 'https://mc.gov.sa/']],
                'faqs' => [['question' => 'Does ZATCA registration happen before commercial registration?', 'answer' => 'For establishment paths, commercial registration is normally completed first, then the taxpayer file is completed through ZATCA.'], ['question' => 'Is VAT registration the same as tax-file setup?', 'answer' => 'No. VAT registration has its own thresholds and rules, while the taxpayer file supports broader ZATCA access and obligations.']],
                'related_links' => [['label' => 'Commercial register issuance', 'route' => 'services.show', 'params' => ['service' => 'issue-commercial-register-16'], 'description' => 'Complete the commercial identity first.'], ['label' => 'Company formation', 'route' => 'public.landing.company-formation-riyadh', 'description' => 'Plan registration and post-setup obligations.'], ['label' => 'Foreign investment setup', 'route' => 'landing.foreign_investment', 'description' => 'Align foreign-company tax setup.']],
                'official_sources' => ['ZATCA', 'Ministry of Commerce'],
                'source_notes' => 'VAT thresholds and other tax obligations must be checked separately against the official ZATCA rules for the business activity.',
            ],
            'ar' => [
                'eyebrow' => 'إعداد الملف الضريبي في زاتكا',
                'title' => 'التسجيل لدى زاتكا وإعداد حساب المكلف',
                'summary' => 'تجهيز ملف المكلف لدى زاتكا بعد السجل التجاري حتى تتمكن المنشأة من إكمال الوصول للحساب الضريبي والالتزامات اللاحقة.',
                'who_needs' => ['المنشآت الجديدة بعد إصدار السجل التجاري.', 'المنشآت أو الشركات الأجنبية التي تحتاج مسار ضريبة دخل أو تسجيل مرتبط في زاتكا.', 'الأعمال التي تجهز الوصول للزكاة أو الضريبة أو ضريبة القيمة المضافة عبر زاتكا.'],
                'requirements' => ['إكمال السجل التجاري أولاً في مسار المنشأة.', 'قد تنشئ زاتكا بيانات المكلف بعد تسجيل وزارة التجارة مع الحاجة لاستكمال الوصول للبوابة.', 'قد تحتاج الشركات إلى بيانات عقد التأسيس حسب الخدمة الضريبية المختارة.'],
                'documents' => ['بيانات السجل التجاري.', 'عقد التأسيس للشركات عند اشتراطه.', 'بيانات المستخدم المفوض والتواصل للوصول إلى بوابة زاتكا.'],
                'conditions' => ['يجب أن يتوافق مسار التسجيل الضريبي مع نوع المكلف ونشاطه.', 'حدود التسجيل في ضريبة القيمة المضافة منفصلة عن إعداد الملف الضريبي الأساسي ويجب التحقق منها عند الحاجة.'],
                'steps' => [
                    ['title' => 'إكمال التأسيس التجاري', 'description' => 'إنهاء تسجيل وزارة التجارة قبل فتح أو استكمال ملف المكلف.'],
                    ['title' => 'الدخول إلى بوابة زاتكا', 'description' => 'استخدام بوابة زاتكا الرسمية لاستكمال بيانات الحساب المطلوبة.'],
                    ['title' => 'استلام التأكيد', 'description' => 'متابعة الطلب الرسمي حتى إصدار تأكيد الحساب أو التسجيل.'],
                ],
                'duration' => 'تعتمد على مسار الخدمة الضريبية ونوع المكلف واكتمال بيانات وزارة التجارة.',
                'authorities' => [['name' => 'زاتكا', 'url' => 'https://www.zatca.gov.sa/'], ['name' => 'وزارة التجارة', 'url' => 'https://mc.gov.sa/']],
                'faqs' => [['question' => 'هل يتم التسجيل في زاتكا قبل السجل التجاري؟', 'answer' => 'في مسارات المنشآت، يتم عادة إكمال السجل التجاري أولاً ثم استكمال ملف المكلف عبر زاتكا.'], ['question' => 'هل تسجيل ضريبة القيمة المضافة هو نفسه إعداد الملف الضريبي؟', 'answer' => 'لا. لضريبة القيمة المضافة حدود وقواعد مستقلة، بينما يدعم ملف المكلف الوصول والالتزامات الأوسع لدى زاتكا.']],
                'related_links' => [['label' => 'إصدار سجل تجاري', 'route' => 'services.show', 'params' => ['service' => 'issue-commercial-register-16'], 'description' => 'إكمال الهوية التجارية أولاً.'], ['label' => 'تأسيس الشركات', 'route' => 'public.landing.company-formation-riyadh', 'description' => 'تخطيط التسجيل والالتزامات اللاحقة.'], ['label' => 'الاستثمار الأجنبي', 'route' => 'landing.foreign_investment', 'description' => 'مواءمة الملف الضريبي للشركة الأجنبية.']],
                'official_sources' => ['زاتكا', 'وزارة التجارة'],
                'source_notes' => 'يجب التحقق من حدود ضريبة القيمة المضافة والالتزامات الضريبية الأخرى بشكل مستقل وفق قواعد زاتكا الرسمية لنشاط المنشأة.',
            ],
        ];
    }

    private static function commercialLicense(): array
    {
        return [
            'en' => [
                'eyebrow' => 'Balady licensing guide',
                'title' => 'Commercial license issuance through Balady',
                'summary' => 'Prepare a municipal commercial license request with activity, location, lease or ownership, safety, and municipality review data aligned before submission.',
                'who_needs' => ['Businesses that need a municipal license before operating a physical location.', 'Owners, managers, or authorized representatives applying through Balady.'],
                'requirements' => ['Select the establishment record, activity, and area.', 'Specify the location and shop or mobile-cart details.', 'Pay fees for instant paths or submit for municipality review when the activity is not instant.'],
                'documents' => ['Exterior shop photo showing the signboard.', 'Lease agreement, ownership deed, or investment contract.', 'Building permit copy where required.', 'Safety equipment invoice or Civil Defense safety report when required by the activity.'],
                'conditions' => ['Government requirements and approvals vary by activity type.', 'Some commercial activities require a Safety Permit issued through Civil Defense integration.'],
                'steps' => [['title' => 'Open the Balady service', 'description' => 'Choose issuing a commercial license from Balady commercial licenses.'], ['title' => 'Enter activity and location', 'description' => 'Select the record, activity, area, and location details.'], ['title' => 'Attach documents and submit', 'description' => 'Upload required files, pay or submit for review, and follow the official request status.']],
                'duration' => 'Balady lists a typical range of 1 to 10 days, depending on activity and review path.',
                'authorities' => [['name' => 'Balady', 'url' => 'https://balady.gov.sa/en/services/issuance-commercial-license'], ['name' => 'Salamah', 'url' => 'https://salamah.998.gov.sa/']],
                'faqs' => [['question' => 'Are Balady requirements the same for all activities?', 'answer' => 'No. Requirements and approvals differ by activity and should be checked through the municipal activity requirements service.'], ['question' => 'Is a safety permit always connected?', 'answer' => 'Balady states that commercial license issuance can include a Safety Permit for approved commercial activities.']],
                'related_links' => [['label' => 'Commercial register issuance', 'route' => 'services.show', 'params' => ['service' => 'issue-commercial-register-16']], ['label' => 'Company formation', 'route' => 'public.landing.company-formation-riyadh']],
                'official_sources' => ['Balady', 'Salamah'],
                'source_notes' => 'Activity-level requirements should be checked in Balady before submitting the final request.',
            ],
            'ar' => [
                'eyebrow' => 'دليل رخص بلدي',
                'title' => 'إصدار الرخصة التجارية عبر بلدي',
                'summary' => 'تجهيز طلب الرخصة البلدية مع مواءمة النشاط والموقع وعقد الإيجار أو الملكية والسلامة وبيانات مراجعة البلدية قبل التقديم.',
                'who_needs' => ['المنشآت التي تحتاج رخصة بلدية قبل تشغيل موقع فعلي.', 'المالك أو المدير أو الممثل المفوض للتقديم عبر بلدي.'],
                'requirements' => ['اختيار سجل المنشأة والنشاط والمنطقة.', 'تحديد الموقع وبيانات المحل أو العربة المتنقلة.', 'سداد الرسوم للمسارات الفورية أو رفع الطلب للمراجعة البلدية للأنشطة غير الفورية.'],
                'documents' => ['صورة خارجية للمحل تظهر اللوحة.', 'عقد إيجار أو صك ملكية أو عقد استثمار.', 'صورة رخصة البناء عند اشتراطها.', 'فاتورة معدات السلامة أو تقرير السلامة من الدفاع المدني عند اشتراط النشاط.'],
                'conditions' => ['تختلف المتطلبات والموافقات الحكومية حسب نوع النشاط.', 'قد يرتبط إصدار الرخصة بتصريح سلامة صادر عبر تكامل الدفاع المدني للأنشطة المعتمدة.'],
                'steps' => [['title' => 'فتح خدمة بلدي', 'description' => 'اختيار إصدار رخصة تجارية من خدمات الرخص التجارية في بلدي.'], ['title' => 'إدخال النشاط والموقع', 'description' => 'تحديد السجل والنشاط والمنطقة وبيانات الموقع.'], ['title' => 'إرفاق المستندات والتقديم', 'description' => 'رفع الملفات المطلوبة والسداد أو الإرسال للمراجعة ومتابعة حالة الطلب.']],
                'duration' => 'تعرض بلدي مدة تقريبية من 1 إلى 10 أيام حسب النشاط ومسار المراجعة.',
                'authorities' => [['name' => 'بلدي', 'url' => 'https://balady.gov.sa/'], ['name' => 'سلامة', 'url' => 'https://salamah.998.gov.sa/']],
                'faqs' => [['question' => 'هل متطلبات بلدي موحدة لكل الأنشطة؟', 'answer' => 'لا. تختلف المتطلبات والموافقات حسب النشاط ويجب التحقق منها عبر خدمة اشتراطات الأنشطة البلدية.'], ['question' => 'هل يرتبط تصريح سلامة دائماً بالرخصة؟', 'answer' => 'توضح بلدي أن إصدار الرخصة التجارية قد يشمل تصريح سلامة للأنشطة التجارية المعتمدة.']],
                'related_links' => [['label' => 'إصدار سجل تجاري', 'route' => 'services.show', 'params' => ['service' => 'issue-commercial-register-16']], ['label' => 'تأسيس الشركات', 'route' => 'public.landing.company-formation-riyadh']],
                'official_sources' => ['بلدي', 'سلامة'],
                'source_notes' => 'يجب التحقق من اشتراطات كل نشاط داخل بلدي قبل تقديم الطلب النهائي.',
            ],
        ];
    }

    private static function trademarkRegistration(): array
    {
        return [
            'en' => [
                'eyebrow' => 'SAIP trademark guide',
                'title' => 'Trademark registration through SAIP',
                'summary' => 'Prepare a trademark filing with applicant details, mark representation, goods or services class, and publication follow-up aligned with SAIP procedures.',
                'who_needs' => ['Companies protecting a brand name, logo, or distinctive commercial sign.', 'Founders filing a mark before market launch or expansion.', 'Trademark owners that need publication and objection follow-up.'],
                'requirements' => ['The mark should be distinctive and not merely a generic name or ordinary description of the goods or services.', 'The filing should identify the applicant and the relevant goods or services class.', 'Publication and objection follow-up should be monitored after acceptance.'],
                'documents' => ['Applicant or company details.', 'Trademark image or wording prepared for filing.', 'Goods or services class and description.', 'Power of attorney or representative authorization when filed through an agent.'],
                'conditions' => ['SAIP examines whether the mark conflicts with legal refusal grounds or earlier marks.', 'IPN publication steps may require payment within the official deadline after acceptance.'],
                'steps' => [
                    ['title' => 'Prepare the mark', 'description' => 'Confirm the wording or logo and the goods or services class before filing.'],
                    ['title' => 'Submit to SAIP', 'description' => 'File the application through the official intellectual-property channel.'],
                    ['title' => 'Follow examination and publication', 'description' => 'Track examination results, publication, objections, and final registration steps.'],
                ],
                'duration' => 'SAIP guidance refers to substantive examination timelines; publication and objection periods depend on the official process status.',
                'authorities' => [['name' => 'SAIP', 'url' => 'https://www.saip.gov.sa/'], ['name' => 'IPN', 'url' => 'https://ipn.saip.gov.sa/']],
                'faqs' => [['question' => 'Can a generic business word be registered as a trademark?', 'answer' => 'A mark generally needs distinctive character; purely generic or ordinary descriptions can be refused under trademark rules.'], ['question' => 'Why is publication important?', 'answer' => 'Accepted marks are published so third parties can review and object within the official process.']],
                'related_links' => [['label' => 'Company formation', 'route' => 'public.landing.company-formation-riyadh', 'description' => 'Align brand protection with entity setup.'], ['label' => 'Commercial register issuance', 'route' => 'services.show', 'params' => ['service' => 'issue-commercial-register-16'], 'description' => 'Prepare company identity before brand filing.']],
                'official_sources' => ['SAIP', 'IPN'],
                'source_notes' => 'Distinctiveness and conflict checks are legal-examination matters decided by the official authority.',
            ],
            'ar' => [
                'eyebrow' => 'دليل العلامات التجارية',
                'title' => 'تسجيل العلامة التجارية عبر الهيئة السعودية للملكية الفكرية',
                'summary' => 'تجهيز طلب تسجيل علامة تجارية ببيانات مقدم الطلب وصورة العلامة وفئة السلع أو الخدمات ومتابعة النشر وفق إجراءات الهيئة.',
                'who_needs' => ['الشركات التي تريد حماية اسم تجاري أو شعار أو علامة مميزة.', 'رواد الأعمال قبل إطلاق العلامة أو التوسع في السوق.', 'ملاك العلامات الذين يحتاجون متابعة النشر والاعتراض.'],
                'requirements' => ['أن تكون العلامة مميزة وليست مجرد اسم شائع أو وصف عادي للسلع أو الخدمات.', 'تحديد مقدم الطلب وفئة السلع أو الخدمات ذات الصلة.', 'متابعة النشر والاعتراض بعد قبول الطلب.'],
                'documents' => ['بيانات مقدم الطلب أو الشركة.', 'صورة العلامة أو نصها المراد تسجيله.', 'فئة السلع أو الخدمات ووصفها.', 'وكالة أو تفويض ممثل عند التقديم عبر وكيل.'],
                'conditions' => ['تفحص الهيئة تحقق موانع الرفض النظامية أو التشابه مع علامات سابقة.', 'قد تتطلب مرحلة النشر في صحيفة الملكية الفكرية سداد المقابل ضمن المهلة الرسمية بعد القبول.'],
                'steps' => [
                    ['title' => 'تجهيز العلامة', 'description' => 'تأكيد النص أو الشعار وفئة السلع أو الخدمات قبل الإيداع.'],
                    ['title' => 'التقديم لدى الهيئة', 'description' => 'إيداع الطلب عبر القناة الرسمية للملكية الفكرية.'],
                    ['title' => 'متابعة الفحص والنشر', 'description' => 'متابعة نتيجة الفحص والنشر والاعتراضات وخطوات التسجيل النهائي.'],
                ],
                'duration' => 'تشير إرشادات الهيئة إلى مدة للفحص الموضوعي، بينما تعتمد مدة النشر والاعتراض على حالة الإجراء الرسمي.',
                'authorities' => [['name' => 'الهيئة السعودية للملكية الفكرية', 'url' => 'https://www.saip.gov.sa/'], ['name' => 'صحيفة الملكية الفكرية', 'url' => 'https://ipn.saip.gov.sa/']],
                'faqs' => [['question' => 'هل يمكن تسجيل كلمة عامة كعلامة تجارية؟', 'answer' => 'تحتاج العلامة عادة إلى صفة مميزة، وقد ترفض العلامات العامة أو الوصفية الخالية من التمييز.'], ['question' => 'لماذا مرحلة النشر مهمة؟', 'answer' => 'تنشر العلامات المقبولة حتى يتمكن الغير من الاطلاع والاعتراض ضمن الإجراء الرسمي.']],
                'related_links' => [['label' => 'تأسيس الشركات', 'route' => 'public.landing.company-formation-riyadh', 'description' => 'مواءمة حماية العلامة مع تأسيس الكيان.'], ['label' => 'إصدار سجل تجاري', 'route' => 'services.show', 'params' => ['service' => 'issue-commercial-register-16'], 'description' => 'تجهيز هوية الشركة قبل ملف العلامة.']],
                'official_sources' => ['الهيئة السعودية للملكية الفكرية', 'صحيفة الملكية الفكرية'],
                'source_notes' => 'التمييز والتشابه وموانع الرفض مسائل فحص نظامي تقررها الجهة الرسمية.',
            ],
        ];
    }
}
