<?php

namespace App\Filament\Resources\SubscriptionResource\RelationManagers;

use Filament\Forms\Components as F;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Phase 9C-2 — سجل تحولات حالة الاشتراك (read-only).
 *
 *  - يُسجَّل من SubscriptionResource::getRelations() فقط إذا subscription_status_logs جاهز.
 *  - لا create/edit/delete من الواجهة — السجلات تُكتب برمجياً من service لاحقاً.
 */
class SubscriptionStatusLogsRelationManager extends RelationManager
{
    protected static string $relationship = 'statusLogs';
    protected static ?string $title = 'سجل تغيّر الحالة';
    protected static ?string $modelLabel = 'سجل';
    protected static ?string $pluralModelLabel = 'سجل تغيّر الحالة';

    public function form(Schema $schema): Schema
    {
        // النموذج مطلوب لـRelationManager API لكنه لا يُستخدم (لا create/edit).
        return $schema->components([
            F\Placeholder::make('readonly_notice')
                ->label('')
                ->content('سجلات حالة الاشتراك للقراءة فقط. تُكتب تلقائياً عند تنفيذ Actions مثل التجديد والإلغاء في الإصدارات القادمة.'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('to_status')
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('created_at')
                    ->label('التاريخ')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('from_status')
                    ->label('من حالة')
                    ->badge()
                    ->color('gray')
                    ->placeholder('—'),

                TextColumn::make('to_status')
                    ->label('إلى حالة')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'active'   => 'success',
                        'pending'  => 'warning',
                        'expired'  => 'danger',
                        'canceled' => 'gray',
                        default    => 'gray',
                    }),

                TextColumn::make('changedBy.name')
                    ->label('بواسطة')
                    ->placeholder('النظام'),

                TextColumn::make('reason')
                    ->label('السبب')
                    ->wrap()
                    ->limit(80)
                    ->placeholder('—'),
            ])
            ->headerActions([])
            ->recordActions([])
            ->toolbarActions([])
            ->emptyStateHeading('لا يوجد سجل تغيّرات بعد');
    }

    public function isReadOnly(): bool
    {
        return true;
    }
}
