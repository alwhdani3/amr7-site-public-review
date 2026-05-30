<?php

namespace App\Filament\Resources\ComplianceObligationResource\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components as F;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Phase 9C-3 — فترات التزام معيّن (obligation_periods).
 *
 *  - يُسجَّل من ComplianceObligationResource::getRelations() فقط إذا الجدول جاهز.
 */
class ObligationPeriodsRelationManager extends RelationManager
{
    protected static string $relationship = 'periods';
    protected static ?string $title = 'فترات الالتزام';
    protected static ?string $modelLabel = 'فترة';
    protected static ?string $pluralModelLabel = 'الفترات';

    public static function statusOptions(): array
    {
        return [
            'upcoming'         => 'قادم',
            'open'             => 'مفتوح',
            'client_uploading' => 'بانتظار رفع العميل',
            'files_uploaded'   => 'تم رفع الملفات',
            'under_review'     => 'قيد المراجعة',
            'client_approval'  => 'بانتظار اعتماد العميل',
            'filed'            => 'تم التقديم',
            'late'             => 'متأخر',
            'missed'           => 'فائت',
            'cancelled'        => 'ملغي',
        ];
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            F\TextInput::make('period_label')
                ->label('وسم الفترة')
                ->required()
                ->maxLength(60)
                ->placeholder('Q1-2026'),

            F\Select::make('status')
                ->label('الحالة')
                ->options(static::statusOptions())
                ->default('upcoming')
                ->required(),

            F\DatePicker::make('period_start')
                ->label('بداية الفترة'),

            F\DatePicker::make('period_end')
                ->label('نهاية الفترة'),

            F\DatePicker::make('opens_at')
                ->label('تاريخ الفتح'),

            F\DatePicker::make('due_date')
                ->label('تاريخ الاستحقاق'),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('period_label')
            ->defaultSort('due_date', 'asc')
            ->columns([
                TextColumn::make('period_label')
                    ->label('الفترة')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn ($state) => static::statusOptions()[$state] ?? $state)
                    ->color(fn ($state) => match ($state) {
                        'open', 'client_uploading', 'files_uploaded', 'under_review', 'client_approval' => 'warning',
                        'filed'                                                                         => 'success',
                        'late', 'missed'                                                                => 'danger',
                        'cancelled'                                                                     => 'gray',
                        default                                                                         => 'info',
                    }),

                TextColumn::make('opens_at')
                    ->label('يفتح')
                    ->date()
                    ->toggleable(),

                TextColumn::make('due_date')
                    ->label('الاستحقاق')
                    ->date()
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->label('آخر تحديث')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([
                CreateAction::make()->label('إضافة فترة'),
            ])
            ->recordActions([
                EditAction::make()->label('تعديل'),
                DeleteAction::make()->label('حذف'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    // لا bulk delete — مخاطرة مع الـlogs المرتبطة.
                ]),
            ]);
    }
}
