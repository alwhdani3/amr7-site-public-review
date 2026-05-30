<?php

namespace App\Filament\Resources\CompanyResource\RelationManagers;

use App\Filament\Resources\TaxReturnRequestResource;
use Filament\Actions\Action;
use Filament\Forms\Components as F;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Phase 9C-6 — إقرارات الشركة الضريبية (read + view link).
 */
class CompanyTaxReturnRequestsRelationManager extends RelationManager
{
    protected static string $relationship = 'taxReturnRequests';
    protected static ?string $title = 'الإقرارات الضريبية';
    protected static ?string $modelLabel = 'إقرار';
    protected static ?string $pluralModelLabel = 'الإقرارات الضريبية';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            F\Placeholder::make('redirect_notice')
                ->label('')
                ->content('تحرير الإقرار الكامل (مراجعة/تقديم/إلغاء) من شاشة الإقرارات الضريبية الرئيسية.'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('public_id')
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('public_id')
                    ->label('الرقم')
                    ->placeholder('—')
                    ->copyable(),

                TextColumn::make('kind')
                    ->label('النوع')
                    ->badge()
                    ->formatStateUsing(fn ($state) => TaxReturnRequestResource::kindOptions()[$state] ?? $state)
                    ->color('info'),

                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn ($state) => TaxReturnRequestResource::statusOptions()[$state] ?? $state)
                    ->color(fn ($state) => match ($state) {
                        'filed'                           => 'success',
                        'under_review', 'client_approval' => 'warning',
                        'rejected', 'cancelled'           => 'danger',
                        'files_pending'                   => 'info',
                        default                           => 'gray',
                    }),

                TextColumn::make('fiscal_period_start')
                    ->label('بداية الفترة')
                    ->date()
                    ->toggleable(),

                TextColumn::make('fiscal_period_end')
                    ->label('نهاية الفترة')
                    ->date()
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->label('آخر تحديث')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([])
            ->recordActions([
                Action::make('open')
                    ->label('فتح')
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->color('gray')
                    ->url(fn ($record) => TaxReturnRequestResource::getUrl('edit', ['record' => $record])),
            ])
            ->toolbarActions([])
            ->emptyStateHeading('لا توجد إقرارات لهذه الشركة');
    }

    public function isReadOnly(): bool
    {
        return true;
    }
}
