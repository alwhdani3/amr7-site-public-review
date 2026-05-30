<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ComplianceObligationResource\Pages;
use App\Filament\Resources\ComplianceObligationResource\RelationManagers\ObligationPeriodsRelationManager;
use App\Models\ComplianceObligation;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Forms\Components as F;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Schema as DbSchema;

/**
 * Phase 9C-3 — Filament Resource لإدارة الالتزامات الدورية على الشركات.
 *
 *  - يُخفى تلقائياً إذا compliance_obligations لم يُهيّأ بعد (Phase 9B migrations Pending).
 *  - لا notifications، لا scheduler، لا API خارجي.
 */
class ComplianceObligationResource extends Resource
{
    protected static ?string $model = ComplianceObligation::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-shield-check';
    protected static ?string $navigationLabel = 'الالتزامات';
    protected static ?string $modelLabel = 'التزام';
    protected static ?string $pluralLabel = 'الالتزامات';
    protected static string|\UnitEnum|null $navigationGroup = 'الاشتراكات والالتزامات';
    protected static ?int $navigationSort = 30;

    public static function canViewAny(): bool
    {
        return DbSchema::hasTable('compliance_obligations')
            && static::userHasAnyRole(auth()->user(), ['manager', 'accountant']);
    }

    public static function canCreate(): bool
    {
        return DbSchema::hasTable('compliance_obligations')
            && static::userHasAnyRole(auth()->user(), ['manager', 'accountant']);
    }

    public static function canView($record): bool
    {
        return static::userHasAnyRole(auth()->user(), ['manager', 'accountant']);
    }

    public static function canEdit($record): bool
    {
        return static::userHasAnyRole(auth()->user(), ['manager', 'accountant']);
    }

    public static function canDelete($record): bool
    {
        return static::userHasAnyRole(auth()->user(), ['manager']);
    }

    public static function canDeleteAny(): bool
    {
        return static::userHasAnyRole(auth()->user(), ['manager']);
    }

    protected static function userHasAnyRole(?\App\Models\User $user, array $roles): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->is_admin) {
            return true;
        }

        if (method_exists($user, 'hasAnyRole') && $user->hasAnyRole($roles)) {
            return true;
        }

        return in_array(strtolower((string) ($user->role ?? '')), $roles, true);
    }

    public static function obligationTypeOptions(): array
    {
        return [
            'quarterly_vat'              => 'إقرار ضريبي ربع سنوي',
            'monthly_vat'                => 'إقرار ضريبي شهري',
            'annual_financial_statement' => 'قوائم مالية سنوية',
            'monthly_payroll'            => 'رواتب شهرية',
            'gosi_certificate'           => 'شهادة التأمينات',
            'cr_renewal'                 => 'تجديد السجل التجاري',
            'document_expiry'            => 'انتهاء وثيقة',
        ];
    }

    public static function recurrenceOptions(): array
    {
        return [
            'monthly'   => 'شهري',
            'quarterly' => 'ربع سنوي',
            'yearly'    => 'سنوي',
            'once'      => 'مرة واحدة',
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('الأطراف')
                ->description('الشركة والاشتراك المرتبط بالالتزام.')
                ->columns(2)
                ->schema([
                    F\Select::make('company_id')
                        ->label('الشركة')
                        ->relationship('company', 'name')
                        ->searchable()
                        ->preload()
                        ->required(),

                    F\Select::make('subscription_id')
                        ->label('الاشتراك (اختياري)')
                        ->relationship('subscription', 'id')
                        ->searchable()
                        ->preload()
                        ->nullable()
                        ->helperText('اختياري — اربط بالاشتراك المسؤول عن الالتزام إن وُجد.'),
                ]),

            Section::make('تعريف الالتزام')
                ->columns(2)
                ->schema([
                    F\Select::make('obligation_type')
                        ->label('نوع الالتزام')
                        ->options(static::obligationTypeOptions())
                        ->required(),

                    F\Select::make('recurrence')
                        ->label('الدورية')
                        ->options(static::recurrenceOptions())
                        ->nullable(),

                    F\TextInput::make('title_ar')
                        ->label('العنوان بالعربية')
                        ->maxLength(191),

                    F\TextInput::make('title_en')
                        ->label('Title (English)')
                        ->maxLength(191),
                ]),

            Section::make('التواريخ والحالة')
                ->columns(2)
                ->schema([
                    F\DatePicker::make('next_due_at')
                        ->label('الاستحقاق القادم'),

                    F\DatePicker::make('starts_at')
                        ->label('تاريخ البدء'),

                    F\DatePicker::make('ends_at')
                        ->label('تاريخ الانتهاء'),

                    F\Toggle::make('is_active')
                        ->label('نشط')
                        ->default(true)
                        ->inline(false),
                ]),

            Section::make('بيانات إضافية')
                ->collapsed()
                ->schema([
                    F\KeyValue::make('metadata')
                        ->label('بيانات إضافية')
                        ->keyLabel('المفتاح')
                        ->valueLabel('القيمة')
                        ->reorderable()
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('company.name')
                    ->label('الشركة')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('obligation_type')
                    ->label('النوع')
                    ->badge()
                    ->formatStateUsing(fn ($state) => static::obligationTypeOptions()[$state] ?? $state)
                    ->color('info'),

                TextColumn::make('recurrence')
                    ->label('الدورية')
                    ->badge()
                    ->formatStateUsing(fn ($state) => static::recurrenceOptions()[$state] ?? $state ?: '—')
                    ->color('gray'),

                TextColumn::make('next_due_at')
                    ->label('الاستحقاق القادم')
                    ->date()
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('نشط')
                    ->boolean(),

                TextColumn::make('updated_at')
                    ->label('آخر تحديث')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('نشط')
                    ->trueLabel('نشط')
                    ->falseLabel('موقوف'),

                SelectFilter::make('obligation_type')
                    ->label('النوع')
                    ->options(static::obligationTypeOptions()),

                SelectFilter::make('recurrence')
                    ->label('الدورية')
                    ->options(static::recurrenceOptions()),

                SelectFilter::make('company_id')
                    ->label('الشركة')
                    ->relationship('company', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                EditAction::make(),

                Action::make('pause')
                    ->label('إيقاف')
                    ->icon('heroicon-o-pause-circle')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn (ComplianceObligation $record) => static::canEdit($record) && $record->is_active)
                    ->action(fn (ComplianceObligation $record) => $record->update(['is_active' => false])),

                Action::make('resume')
                    ->label('استئناف')
                    ->icon('heroicon-o-play-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (ComplianceObligation $record) => static::canEdit($record) && ! $record->is_active)
                    ->action(fn (ComplianceObligation $record) => $record->update(['is_active' => true])),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    // لا bulk delete/cancel — أكشن خطر مؤجل.
                ]),
            ])
            ->emptyStateHeading('لا توجد التزامات بعد');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListComplianceObligations::route('/'),
            'create' => Pages\CreateComplianceObligation::route('/create'),
            'edit'   => Pages\EditComplianceObligation::route('/{record}/edit'),
        ];
    }

    public static function getRelations(): array
    {
        if (! DbSchema::hasTable('obligation_periods')) {
            return [];
        }

        return [
            ObligationPeriodsRelationManager::class,
        ];
    }
}
