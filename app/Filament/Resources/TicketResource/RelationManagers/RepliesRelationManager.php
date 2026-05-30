<?php

declare(strict_types=1);

namespace App\Filament\Resources\TicketResource\RelationManagers;

use App\Models\Ticket;
use Filament\Actions\Action; // ✅ Filament v5: Actions هنا
use Filament\Forms\Components as F;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

class RepliesRelationManager extends RelationManager
{
    protected static string $relationship = 'replies';

    protected static ?string $title = 'المحادثة والردود';

    // ✅ Filament v5: النوع لازم BackedEnum|string|null
    protected static \BackedEnum|string|null $icon = 'heroicon-o-chat-bubble-left-right';

    protected static bool $isLazy = false;

    public function table(Table $table): Table
    {
        return $table
            ->paginated(false)
            ->defaultSort('created_at', 'asc')
            ->modifyQueryUsing(fn (Builder $query) => $query->with([
                'user',
                'customer',
                'attachments',
            ]))
            ->columns([
                Tables\Columns\ViewColumn::make('message')
                    ->label('الرسائل')
                    ->view('filament.tickets.reply-bubble'),
            ])
            ->headerActions([
                Action::make('createReply')
                    ->label('إضافة رد جديد')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->color('primary')
                    ->modalHeading('إضافة رد على التذكرة')
                    ->modalWidth('lg')
                    ->form([
                        F\Textarea::make('message')
                            ->label('نص الرد')
                            ->required()
                            ->rows(4)
                            ->columnSpanFull(),

                        F\FileUpload::make('attachments')
                            ->label('المرفقات')
                            ->helperText('صور، ملفات PDF، مستندات')
                            ->disk('private') // غيّرها إلى private لو تبغى حماية أعلى
                            ->directory(fn () => 'tickets/' . $this->getOwnerRecord()->getKey() . '/replies')
                            ->multiple()
                            ->preserveFilenames()
                            ->maxSize(10240)
                            ->columnSpanFull(),
                    ])
                    ->action(function (array $data): void {
                        /** @var Ticket $ticket */
                        $ticket = $this->getOwnerRecord();

                        $disk = 'private';

                        $reply = $ticket->replies()->create([
                            'user_id'     => auth()->id(),
                            'customer_id' => null,
                            'message'     => (string) $data['message'],
                        ]);

                        foreach (($data['attachments'] ?? []) as $path) {
                            $path = (string) $path;

                            $mime = Storage::disk($disk)->mimeType($path);
                            $size = Storage::disk($disk)->size($path);
                            $originalName = basename($path);

                            $reply->attachments()->create([
                                'ticket_id'     => $ticket->getKey(),
                                'company_id'    => $ticket->company_id,
                                'uploaded_by'   => auth()->id(),
                                'user_id'       => auth()->id(),
                                'disk'          => $disk,
                                'path'          => $path,
                                'file_path'     => $path,
                                'original_name' => $originalName,
                                'mime'          => $mime,
                                'size'          => $size,
                                'category'      => 'ticket_reply',
                            ]);
                        }

                        $ticket->update([
                            'last_reply_at' => now(),
                            'status'        => 'pending_customer',
                        ]);

                        Notification::make()
                            ->title('تم إضافة الرد')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (): bool =>
                        auth()->check()
                        && !(method_exists(auth()->user(), 'hasRole') && auth()->user()->hasRole('customer'))
                    ),
            ])
            ->emptyStateHeading('لا توجد ردود بعد')
            ->emptyStateDescription('ابدأ المحادثة بإضافة رد جديد.')
            ->emptyStateIcon('heroicon-o-chat-bubble-oval-left');
    }
}
