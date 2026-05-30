<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

use Filament\Actions\Action;              // ✅ استخدم Actions (مش Tables Actions)
use Filament\Actions\ActionGroup;         // ✅ موجود عندك (اشتغل في UserResource)

use Filament\Forms\Components as F;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Model;

class UploadedAttachmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'uploadedAttachments';
    protected static ?string $title = 'مرفقات العضو';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('#')
                    ->sortable(),

                Tables\Columns\TextColumn::make('title')
                    ->label('العنوان')
                    ->searchable()
                    ->placeholder('-')
                    ->limit(30)
                    ->tooltip(fn ($state) => $state),

                Tables\Columns\TextColumn::make('category')
                    ->label('التصنيف')
                    ->badge()
                    ->searchable()
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('original_name')
                    ->label('اسم الملف')
                    ->searchable()
                    ->limit(40)
                    ->icon('heroicon-m-document'),

                Tables\Columns\TextColumn::make('mime')
                    ->label('النوع')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الرفع')
                    ->dateTime('Y-m-d h:i A')
                    ->description(fn (Model $record) => $record->created_at?->diffForHumans() ?? '')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        F\DatePicker::make('created_from')->label('من تاريخ'),
                        F\DatePicker::make('created_until')->label('إلى تاريخ'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'] ?? null,
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'] ?? null,
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->recordActions([
                ActionGroup::make([
                    Action::make('open')
                        ->label('فتح')
                        ->icon('heroicon-m-eye')
                        ->color('info')
                        ->url(fn (Model $record) => $this->fileUrl($record))
                        ->openUrlInNewTab(),

                    Action::make('download')
                        ->label('تحميل')
                        ->icon('heroicon-m-arrow-down-tray')
                        ->color('success')
                        // ✅ بدل download() response: نخليها رابط مباشر للملف
                        ->url(fn (Model $record) => $this->fileUrl($record))
                        ->openUrlInNewTab(),

                    Action::make('delete')
                        ->label('حذف')
                        ->icon('heroicon-m-trash')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function (Model $record): void {
                            // حذف الملف من التخزين (إذا موجود)
                            $disk = $record->disk ?? 'public';
                            $path = $record->path ?? $record->file_path ?? null;

                            if ($path && Storage::disk($disk)->exists($path)) {
                                Storage::disk($disk)->delete($path);
                            }

                            // حذف السجل
                            $record->delete();
                        }),
                ])
                    ->tooltip('الإجراءات')
                    ->icon('heroicon-m-ellipsis-vertical'),
            ])
            ->emptyStateHeading('لا توجد مرفقات')
            ->emptyStateDescription('لا توجد ملفات مرفقة لهذا المستخدم حالياً.')
            ->emptyStateIcon('heroicon-o-paper-clip');
    }

    private function fileUrl(Model $record): string
    {
        $disk = $record->disk ?? 'public';
        $path = $record->path ?? $record->file_path ?? null;

        if (! $path) {
            return '#';
        }

        return Storage::disk($disk)->url($path);
    }
}
