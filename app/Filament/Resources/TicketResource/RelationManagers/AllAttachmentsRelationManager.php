<?php

declare(strict_types=1);

namespace App\Filament\Resources\TicketResource\RelationManagers;

use App\Filament\Resources\TicketResource\Pages\ViewTicket;
use App\Models\Attachment;
use App\Models\Ticket;
use App\Models\TicketReply;
use Filament\Forms\Components as F;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AllAttachmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'allAttachments';

    protected static ?string $title = 'كل المرفقات (التذكرة + الردود)';

    // ✅ Filament v5 يتطلب BackedEnum|string|null
    protected static \BackedEnum|string|null $icon = 'heroicon-o-paper-clip';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return $pageClass === ViewTicket::class;
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('original_name')
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('original_name')
                    ->label('اسم الملف')
                    ->searchable()
                    ->limit(50)
                    ->tooltip(fn (Attachment $record) => $record->original_name),

                Tables\Columns\TextColumn::make('attachable_type')
                    ->label('المصدر')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        Ticket::class      => 'التذكرة الرئيسية',
                        TicketReply::class => 'رد',
                        default            => 'أخرى',
                    })
                    ->color(fn (string $state) => match ($state) {
                        Ticket::class      => 'info',
                        TicketReply::class => 'warning',
                        default            => 'gray',
                    }),

                Tables\Columns\TextColumn::make('reply_excerpt')
                    ->label('سياق الرد')
                    ->getStateUsing(function (Attachment $record) {
                        if ($record->attachable_type === TicketReply::class) {
                            return Str::limit($record->attachable?->message ?? '', 40);
                        }
                        return '—';
                    })
                    ->color('gray')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('uploadedByUser.name')
                    ->label('المستخدم')
                    ->icon('heroicon-m-user')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('size')
                    ->label('الحجم')
                    ->formatStateUsing(fn ($state) => $state ? number_format(((float) $state) / 1024, 1) . ' KB' : '-'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الرفع')
                    ->date('Y-m-d h:i A')
                    ->sortable(),
            ])
            ->headerActions([
                Tables\Actions\Action::make('uploadTicketAttachments')
                    ->label('إرفاق ملف')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->button()
                    ->modalHeading('رفع مرفق جديد للتذكرة')
                    ->modalWidth('lg')
                    ->form([
                        F\FileUpload::make('files')
                            ->label('الملفات')
                            ->helperText('يمكنك رفع صور أو مستندات PDF')
                            ->disk('private') // غيّرها إلى public إذا تبي رابط مباشر
                            ->directory(fn () => 'tickets/' . $this->getOwnerRecord()->getKey())
                            ->multiple()
                            ->preserveFilenames()
                            ->maxSize(10240)
                            ->required(),
                    ])
                    ->action(function (array $data): void {
                        /** @var Ticket $ticket */
                        $ticket = $this->getOwnerRecord();

                        $disk = 'private';

                        foreach (($data['files'] ?? []) as $path) {
                            $path = (string) $path;

                            $mime = Storage::disk($disk)->mimeType($path);
                            $size = Storage::disk($disk)->size($path);
                            $originalName = basename($path);

                            $ticket->attachments()->create([
                                'ticket_id'     => $ticket->getKey(),
                                'company_id'    => $ticket->company_id,
                                'user_id'       => auth()->id(),
                                'uploaded_by'   => auth()->id(),
                                'disk'          => $disk,
                                'path'          => $path,
                                'file_path'     => $path,
                                'original_name' => $originalName,
                                'mime'          => $mime,
                                'size'          => $size,
                                'category'      => 'ticket',
                            ]);
                        }

                        Notification::make()
                            ->title('تم رفع المرفقات بنجاح')
                            ->success()
                            ->send();
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('download')
                    ->label('تحميل')
                    ->icon('heroicon-o-arrow-down-tray')
                    // Always route through the authorized download endpoint —
                    // it enforces tenant + role checks and never exposes the
                    // underlying storage path. Direct Storage::url() leaked
                    // signed URLs for files on the private disk.
                    ->url(fn (Attachment $record) => route('attachments.download', $record->id))
                    ->openUrlInNewTab(),

                Tables\Actions\DeleteAction::make(),
            ])
            ->emptyStateHeading('لا توجد مرفقات')
            ->emptyStateDescription('جميع المرفقات الخاصة بالتذكرة وردودها ستظهر هنا.');
    }
}
