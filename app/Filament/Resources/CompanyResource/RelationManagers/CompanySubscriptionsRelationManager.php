<?php

namespace App\Filament\Resources\CompanyResource\RelationManagers;

use App\Filament\Resources\SubscriptionResource;
use Filament\Actions\Action;
use Filament\Forms\Components as F;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Phase 9C-6 — اشتراكات الشركة (read + view link).
 *
 *  - الإدارة الكاملة في SubscriptionResource (renew/cancel/items/logs).
 *  - هنا نعرض القائمة + رابط فتح للتحرير الكامل.
 */
class CompanySubscriptionsRelationManager extends RelationManager
{
    protected static string $relationship = 'subscriptions';
    protected static ?string $title = 'الاشتراكات';
    protected static ?string $modelLabel = 'اشتراك';
    protected static ?string $pluralModelLabel = 'الاشتراكات';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            F\Placeholder::make('redirect_notice')
                ->label('')
                ->content('تحرير الاشتراك الكامل (التجديد/الإلغاء/الميزات) يتم من شاشة الاشتراكات الرئيسية.'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->defaultSort('expires_at', 'desc')
            ->columns([
                TextColumn::make('package.name')
                    ->label('الباقة')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'active'   => 'نشط',
                        'expired'  => 'منتهي',
                        'canceled' => 'ملغي',
                        'pending'  => 'في انتظار الدفع',
                        default    => $state,
                    })
                    ->color(fn ($state) => match ($state) {
                        'active'   => 'success',
                        'pending'  => 'warning',
                        'expired'  => 'danger',
                        'canceled' => 'gray',
                        default    => 'gray',
                    }),

                TextColumn::make('starts_at')
                    ->label('بدء')
                    ->date()
                    ->toggleable(),

                TextColumn::make('expires_at')
                    ->label('انتهاء')
                    ->date()
                    ->sortable(),

                TextColumn::make('remaining_consultations')
                    ->label('الاستشارات المتبقية')
                    ->numeric()
                    ->placeholder('—')
                    ->toggleable(),
            ])
            ->headerActions([])
            ->recordActions([
                Action::make('open')
                    ->label('فتح')
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->color('gray')
                    ->url(fn ($record) => SubscriptionResource::getUrl('edit', ['record' => $record])),
            ])
            ->toolbarActions([])
            ->emptyStateHeading('لا توجد اشتراكات لهذه الشركة');
    }

    public function isReadOnly(): bool
    {
        return true;
    }
}
