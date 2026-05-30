<?php

namespace App\Filament\Resources\CompanyResource\RelationManagers;

use App\Filament\Resources\ComplianceObligationResource;
use Filament\Actions\Action;
use Filament\Forms\Components as F;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Phase 9C-6 — التزامات الشركة (read + view link).
 */
class CompanyComplianceObligationsRelationManager extends RelationManager
{
    protected static string $relationship = 'complianceObligations';
    protected static ?string $title = 'الالتزامات';
    protected static ?string $modelLabel = 'التزام';
    protected static ?string $pluralModelLabel = 'الالتزامات';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            F\Placeholder::make('redirect_notice')
                ->label('')
                ->content('تحرير الالتزامات الكامل من شاشة الالتزامات الرئيسية.'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('obligation_type')
            ->defaultSort('next_due_at', 'asc')
            ->columns([
                TextColumn::make('obligation_type')
                    ->label('النوع')
                    ->badge()
                    ->formatStateUsing(fn ($state) => ComplianceObligationResource::obligationTypeOptions()[$state] ?? $state)
                    ->color('info'),

                TextColumn::make('recurrence')
                    ->label('الدورية')
                    ->badge()
                    ->formatStateUsing(fn ($state) => ComplianceObligationResource::recurrenceOptions()[$state] ?? ($state ?: '—'))
                    ->color('gray'),

                TextColumn::make('next_due_at')
                    ->label('الاستحقاق القادم')
                    ->date()
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('نشط')
                    ->boolean(),
            ])
            ->headerActions([])
            ->recordActions([
                Action::make('open')
                    ->label('فتح')
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->color('gray')
                    ->url(fn ($record) => ComplianceObligationResource::getUrl('edit', ['record' => $record])),
            ])
            ->toolbarActions([])
            ->emptyStateHeading('لا توجد التزامات لهذه الشركة');
    }

    public function isReadOnly(): bool
    {
        return true;
    }
}
