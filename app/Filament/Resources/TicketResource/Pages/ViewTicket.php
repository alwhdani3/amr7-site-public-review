<?php

namespace App\Filament\Resources\TicketResource\Pages;

use App\Filament\Resources\TicketResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components as F;
use Filament\Resources\Pages\ViewRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage; // ✅ ضروري عشان نجيب معلومات الملف (الحجم والنوع)
use Illuminate\Support\Str;

class ViewTicket extends ViewRecord
{
    protected static string $resource = TicketResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('reply')
                ->label('إضافة رد جديد')
                ->icon('heroicon-o-chat-bubble-left-right')
                ->modalHeading('إرسال رد على التذكرة')
                ->visible(fn () => $this->canCurrentUserReply())
                ->form([
                    F\Textarea::make('message')
                        ->label('نص الرد')
                        ->rows(5)
                        ->required()
                        ->columnSpanFull(),

                    F\FileUpload::make('attachments')
                        ->label('مرفقات (اختياري)')
                        ->multiple()
                        ->disk('private') // تأكد أن هذا يطابق إعداداتك
                        // ✅ حفظ الملفات داخل مجلد خاص برقم التذكرة
                        ->directory(fn () => "tickets/" . $this->record->id . "/replies")
                        // ✅ يحفظ الاسم الأصلي للملف بدلاً من تغييره لرموز عشوائية
                        ->preserveFilenames()
                        ->maxSize(10240)
                        ->columnSpanFull(),
                ])
                ->action(function (array $data): void {
                    $ticket = $this->record;

                    // 1. إنشاء الرد
                    // (الموديل TicketReply سيتكفل بتحديث الوقت والإشعارات تلقائياً)
                    $reply = $ticket->replies()->create([
                        'user_id' => Auth::id(),
                        'message' => $data['message'],
                    ]);

                    // 2. معالجة المرفقات
                    $disk = 'private'; // نفس الديسك المستخدم في الفورم

                    foreach (($data['attachments'] ?? []) as $path) {
                        // ✅ استخراج البيانات الوصفية (المهمة جداً)
                        $mime = Storage::disk($disk)->mimeType($path);
                        $size = Storage::disk($disk)->size($path);
                        $originalName = basename($path);

                        $reply->attachments()->create([
                            'ticket_id'     => $ticket->id, // ✅ ربط مباشر بالتذكرة (مهم لجدول المرفقات)
                            'company_id'    => $ticket->company_id,
                            'user_id'       => Auth::id(),
                            'uploaded_by'   => Auth::id(),
                            'disk'          => $disk,
                            'path'          => $path,
                            'file_path'     => $path,
                            'original_name' => $originalName,
                            'mime'          => $mime, // ✅ تم حفظ النوع
                            'size'          => $size, // ✅ تم حفظ الحجم
                            'category'      => 'ticket_reply',
                        ]);
                    }

                    // إشعار نجاح للمستخدم الحالي
                    Notification::make()
                        ->title('تم إرسال الرد')
                        ->success()
                        ->send();

                    // تحديث الصفحة لعرض الرد الجديد
                    $this->refreshFormData(['replies']);
                }),

            EditAction::make()->label('تعديل'),
        ];
    }

    // دالة التحقق من الصلاحية (كما هي في كودك)
    private function canCurrentUserReply(): bool
    {
        $user = Auth::user();
        if (! $user) {
            return false;
        }

        if (($this->record->status ?? null) === 'closed') {
            return false;
        }

        if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
            return true;
        }

        if (($user->role ?? null) === 'manager') {
            return true;
        }

        $ticketCompanyId = (int) ($this->record->company_id ?? 0);
        if ($ticketCompanyId <= 0) {
            return false;
        }

        return $user->companies()
            ->whereKey($ticketCompanyId)
            ->wherePivot('is_active', true)
            ->exists();
    }
}