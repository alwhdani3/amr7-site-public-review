<?php

namespace App\Filament\Pages;

use App\Jobs\SendMarketingEmailJob;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Validator;
use Livewire\WithFileUploads;

class EmailCampaigns extends Page
{
    use WithFileUploads;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-envelope';
    protected static string | \UnitEnum | null $navigationGroup = 'التسويق';
    protected static ?string $navigationLabel = 'حملات البريد';
    protected static ?string $title = 'حملات البريد';
    protected static ?string $slug = 'email-campaigns';
    protected static ?int $navigationSort = 95;

    protected string $view = 'filament.pages.email-campaigns';

    public $csv_file = null;
    public string $subject = "مقترح تعاون مهني لخدمة عملاء آمر سبعة";
    public string $body = "السلام عليكم ورحمة الله وبركاته،

سعادة/ مدير المكتب المحترم
تحية طيبة وبعد،

يسعدنا في شركة آمر سبعة لحلول الأعمال التواصل معكم لبحث إمكانية التعاون المهني مع مكتبكم الكريم، وذلك لخدمة عملائنا من رواد الأعمال والمستثمرين وأصحاب المنشآت ممن يحتاجون إلى خدمات محاسبية وزكوية وضريبية واستشارية ذات علاقة.

تعمل شركة آمر سبعة لحلول الأعمال على مساندة العملاء في تأسيس الشركات، وتنظيم المتطلبات، ومتابعة الإجراءات التشغيلية والإدارية، ومن خلال ذلك يظهر لدى عدد من العملاء احتياج فعلي إلى مكاتب مهنية موثوقة تقدم خدمات عالية الجودة وبأسعار مناسبة.

وتتمثل فكرة التعاون في ترشيح مكتبكم للعملاء المناسبين بحسب نوع الخدمة المطلوبة، على أن يتم التواصل والتعاقد بين مكتبكم والعميل مباشرة، وتحديد نطاق العمل والأتعاب وآلية التنفيذ من قبلكم.

كما نأمل، متى أمكن، تقديم أسعار أو باقات تفضيلية لعملاء آمر سبعة، بما يحقق منفعة مشتركة للطرفين ويرفع جودة التجربة المقدمة للعميل.

وفي حال اهتمامكم بهذا المقترح، يسعدنا ترتيب اتصال مختصر للتعريف بآلية التعاون، ومعرفة الخدمات التي يقدمها مكتبكم، وآلية استقبال العملاء المحالين من طرفنا.

وتفضلوا بقبول فائق الاحترام والتقدير.";
    public int $max_recipients = 30;
    public int $seconds_between_messages = 30;

    public static function canAccess(): bool
    {
        return static::userCanRunCampaigns(auth()->user());
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::userCanRunCampaigns(auth()->user());
    }

    /**
     * Bulk email campaigns can mail up to 2000 recipients per run, so we
     * restrict them to top-level operators. Managers can already trigger
     * single transactional emails through other flows; the campaign sender
     * stays admin-only until there's a clear operator policy for it.
     */
    protected static function userCanRunCampaigns(?\App\Models\User $user): bool
    {
        if (! $user) {
            return false;
        }

        return (bool) $user->is_admin;
    }

    public function queueCampaign(): void
    {
        $this->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt'],
            'subject' => ['required', 'string', 'max:180'],
            'body' => ['required', 'string'],
            'max_recipients' => ['required', 'integer', 'min:1', 'max:2000'],
            'seconds_between_messages' => ['required', 'integer', 'min:10', 'max:600'],
        ], [
            'csv_file.required' => 'ارفع ملف CSV أولًا.',
            'csv_file.mimes' => 'الملف يجب أن يكون CSV أو TXT.',
        ]);

        $absolutePath = $this->csv_file->getRealPath();

        [$recipients, $invalidCount, $duplicateCount] = $this->readRecipientsFromCsv(
            absolutePath: $absolutePath,
            limit: $this->max_recipients
        );

        if (count($recipients) === 0) {
            Notification::make()
                ->title('لم يتم العثور على إيميلات صالحة')
                ->body('تأكد أن ملف CSV يحتوي على عمود email.')
                ->danger()
                ->send();

            return;
        }

        $interval = max(10, min(600, $this->seconds_between_messages));

        foreach ($recipients as $index => $recipient) {
            SendMarketingEmailJob::dispatch(
                email: $recipient['email'],
                subject: $this->subject,
                body: $this->body,
                name: $recipient['name'] ?? null
            )->delay(now()->addSeconds($index * $interval));
        }

        Notification::make()
            ->title('تمت جدولة الحملة')
            ->body('تمت جدولة ' . count($recipients) . ' رسالة. تم تجاهل ' . $invalidCount . ' إيميل غير صالح و ' . $duplicateCount . ' تكرار.')
            ->success()
            ->send();

        $this->reset('csv_file');
    }

    private function readRecipientsFromCsv(string $absolutePath, int $limit): array
    {
        $handle = fopen($absolutePath, 'rb');

        if ($handle === false) {
            return [[], 0, 0];
        }

        $headerRow = fgetcsv($handle);

        if (! is_array($headerRow)) {
            fclose($handle);
            return [[], 0, 0];
        }

        if (isset($headerRow[0])) {
            $headerRow[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) $headerRow[0]);
        }

        $headers = array_map(fn ($value) => $this->normalizeHeader((string) $value), $headerRow);

        $emailIndex = $this->findColumn($headers, [
            'email',
            'e-mail',
            'mail',
            'البريد',
            'البريد الإلكتروني',
            'الايميل',
            'الإيميل',
        ]);

        $nameIndex = $this->findColumn($headers, [
            'name',
            'full_name',
            'contact_name',
            'اسم',
            'الاسم',
        ]);

        if ($emailIndex === null) {
            fclose($handle);
            return [[], 0, 0];
        }

        $recipients = [];
        $seen = [];
        $invalidCount = 0;
        $duplicateCount = 0;

        while (($row = fgetcsv($handle)) !== false) {
            if (count($recipients) >= $limit) {
                break;
            }

            $email = strtolower(trim((string) ($row[$emailIndex] ?? '')));
            $name = $nameIndex !== null ? trim((string) ($row[$nameIndex] ?? '')) : null;

            $validator = Validator::make(['email' => $email], [
                'email' => ['required', 'email'],
            ]);

            if ($validator->fails()) {
                $invalidCount++;
                continue;
            }

            if (isset($seen[$email])) {
                $duplicateCount++;
                continue;
            }

            $seen[$email] = true;

            $recipients[] = [
                'email' => $email,
                'name' => $name ?: null,
            ];
        }

        fclose($handle);

        return [$recipients, $invalidCount, $duplicateCount];
    }

    private function normalizeHeader(string $value): string
    {
        $value = trim(mb_strtolower($value));
        return str_replace([' ', '_', '-', 'ـ'], '', $value);
    }

    private function findColumn(array $headers, array $candidates): ?int
    {
        $normalizedCandidates = array_map(fn ($value) => $this->normalizeHeader($value), $candidates);

        foreach ($headers as $index => $header) {
            if (in_array($header, $normalizedCandidates, true)) {
                return $index;
            }
        }

        return null;
    }
}
