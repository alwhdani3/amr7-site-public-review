<?php

namespace App\Filament\Resources\CompanyResource\RelationManagers;

use App\Filament\Resources\ObligationPeriodResource;
use Filament\Actions\Action;
use Filament\Forms\Components as F;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Phase 9C-6 — فترات التزام الشركة (read + view link).
 */
class CompanyObligationPeriodsRelationManager extends RelationManager
{
    protected static string $relationship = 'obligationPeriods';
    protected static ?string $title = 'فترات الالتزام';
    protected static ?string $modelLabel = 'فترة';
    protected static ?string $pluralModelLabel = 'فترات الالتزام';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            F\Placeholder::make('redirect_notice')
                ->label('')
                ->content('تحرير الفترات وإجراءات الإغلاق من شاشة فترات الالتزام الرئيسية.'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('period_label')
            ->defaultSort('due_date', 'asc')
            ->columns([
                TextColumn::make('period_label')
                    ->label('الفترة')
                    ->searchable(),

                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn ($state) => ObligationPeriodResource::statusOptions()[$state] ?? $state)
                    ->color(fn ($state) => match ($state) {
                        'open', 'client_uploading', 'files_uploaded', 'under_review', 'client_approval' => 'warning',
                        'filed'                                                                         => 'success',
                        'late', 'missed'                                                                => 'danger',
                        'cancelled'                                                                     => 'gray',
                        default                                                                         => 'info',
                    }),

                TextColumn::make('due_date')
                    ->label('الاستحقاق')
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
                    ->url(fn ($record) => ObligationPeriodResource::getUrl('edit', ['record' => $record])),
            ])
            ->toolbarActions([])
            ->emptyStateHeading('لا توجد فترات لهذه الشركة');
    }

    public function isReadOnly(): bool
    {
        return true;
    }
}
