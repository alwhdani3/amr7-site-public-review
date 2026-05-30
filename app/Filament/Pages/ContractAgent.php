<?php

namespace App\Filament\Pages;

use App\Services\Ai\N8nContentClient;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;

class ContractAgent extends Page implements HasForms
{
    use InteractsWithForms;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-cpu-chip';
    protected static ?string $navigationLabel = 'المستشار الذكي (AI)';
    protected static ?string $title = 'صياغة العقود وتحليل البيانات';
    protected static \UnitEnum|string|null $navigationGroup = 'التشغيل الآلي والذكاء';
    protected static ?int $navigationSort = 5;

    protected string $view = 'filament.pages.contract-agent';

    public ?array $data = [];
    public $result = null;
    public bool $isLoading = false;

    public static function canAccess(): bool
    {
        return static::userCanOperateAiAgent(auth()->user());
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::userCanOperateAiAgent(auth()->user());
    }

    /**
     * Restrict the AI Contract Agent to operators only — this page calls
     * an external n8n workflow with a user-uploaded file and free-text
     * instructions, so support / employee / accountant should not be able
     * to fire it without explicit operator authorisation.
     */
    protected static function userCanOperateAiAgent(?\App\Models\User $user): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->is_admin) {
            return true;
        }

        if (method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['manager'])) {
            return true;
        }

        return strtolower((string) ($user->role ?? '')) === 'manager';
    }

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('إدخال البيانات')
                    ->description('اكتب اسم الشركة، وارفع الملف (اختياري)، ثم ضع التعليمات واضغط تشغيل.')
                    ->schema([
                        TextInput::make('company_name')
                            ->label('اسم الشركة / الطرف الأول')
                            ->required()
                            ->placeholder('مثال: شركة آمر سبعة لحلول الأعمال'),

                        FileUpload::make('attachment')
                            ->label('رفع ملف (PDF/صورة) للتحليل')
                            ->disk('private')
                            ->directory('ai-requests')
                            ->acceptedFileTypes(['application/pdf', 'image/*'])
                            ->maxSize(10240),

                        Textarea::make('instructions')
                            ->label('تعليمات للوكيل')
                            ->placeholder('مثال: صغ عقد تأسيس بناءً على السجل التجاري المرفق...')
                            ->rows(4)
                            ->columnSpanFull()
                            ->required(),
                    ])->columns(2),
            ])
            ->statePath('data');
    }

    public function runAgent()
    {
        $this->validate();

        $formData = $this->form->getState();
        $this->isLoading = true;
        $this->result = null;

        Notification::make()
            ->title('جاري الاتصال بالوكيل الذكي...')
            ->info()
            ->body('يرجى الانتظار قليلاً، جاري قراءة الملفات وصياغة العقد.')
            ->send();

        $fileStream = null;
        $fileName   = null;

        try {
            if (! empty($formData['attachment'])) {
                $resolved = $this->resolveStoredFile($formData['attachment']);

                if ($resolved) {
                    [$disk, $path] = $resolved;
                    $fullPath = Storage::disk($disk)->path($path);

                    if (is_file($fullPath)) {
                        $fileStream = fopen($fullPath, 'r');
                        $fileName   = basename((string) $formData['attachment']);
                    }
                }
            }

            $client = app(N8nContentClient::class);

            $envelope = $client->callContractAgent(
                payload: [
                    'company_name' => $formData['company_name'],
                    'instructions' => $formData['instructions'],
                    'user_id'      => auth()->id(),
                ],
                fileResource: $fileStream,
                fileName: $fileName,
            );

            if (! $envelope['ok']) {
                Notification::make()
                    ->title('فشل المستشار الذكي')
                    ->body($client->reasonMessage($envelope['reason'], $envelope['status']))
                    ->danger()
                    ->persistent()
                    ->send();
                return;
            }

            $data   = $envelope['data'];
            $result = $data['result'] ?? $data['raw'] ?? null;

            if (! is_string($result) || trim($result) === '') {
                // الرد ناجح HTTP-wise لكن بدون محتوى مفيد (result/raw فاضي).
                // نعرض كـ JSON خام إن وُجد، وإلا نعتبره فشلاً ونمتنع عن عرض نجاح كاذب.
                if (! empty($data['json']) && is_array($data['json'])) {
                    $this->result = $data['json'];

                    Notification::make()
                        ->title('تمت المعالجة')
                        ->body('استُلم رد JSON من n8n بدون حقل result نصي — تم عرض الرد الخام.')
                        ->warning()
                        ->duration(7000)
                        ->send();
                    return;
                }

                Notification::make()
                    ->title('n8n رجّع رداً فارغاً')
                    ->body('استلمنا 2xx من n8n لكن بدون محتوى مفيد. تحقّق من إعدادات الـ workflow.')
                    ->danger()
                    ->persistent()
                    ->send();
                return;
            }

            $this->result = $result;

            Notification::make()
                ->title('تمت الصياغة بنجاح!')
                ->success()
                ->body('تم استلام الرد من الوكيل الذكي.')
                ->duration(5000)
                ->send();
        } catch (\Throwable $e) {
            Notification::make()
                ->title('خطأ في المعالجة')
                ->body($e->getMessage())
                ->danger()
                ->persistent()
                ->send();
        } finally {
            if (is_resource($fileStream)) {
                fclose($fileStream);
            }

            $this->isLoading = false;
        }
    }

    public function resetPage()
    {
        $this->result = null;
        $this->form->fill();

        Notification::make()
            ->title('تم تصفية البيانات')
            ->info()
            ->send();
    }

    private function resolveStoredFile(string $path): ?array
    {
        if (Storage::disk('private')->exists($path)) {
            return ['private', $path];
        }

        if (Storage::disk('public')->exists($path)) {
            return ['public', $path];
        }

        return null;
    }
}