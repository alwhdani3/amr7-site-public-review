<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CompanyResource\Pages;
use App\Filament\Resources\CompanyResource\RelationManagers\CompanyComplianceObligationsRelationManager;
use App\Filament\Resources\CompanyResource\RelationManagers\CompanyObligationPeriodsRelationManager;
use App\Filament\Resources\CompanyResource\RelationManagers\CompanySubscriptionsRelationManager;
use App\Filament\Resources\CompanyResource\RelationManagers\CompanyTaxReturnRequestsRelationManager;
use App\Models\Company;
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
use Filament\Tables\Table;
use Illuminate\Support\Facades\Schema as DbSchema;

/**
 * Phase 9C-6 — ملف الشركة التشغيلي الموحّد.
 *
 *  - منفصل تماماً عن CustomerResource (الذي يدير العملاء/leads).
 *  - يعرض حقول companies الموجودة فعلياً، مع guards للحقول الحديثة.
 *  - RelationManagers محروسة بـSchema::hasTable لكل واحدة منها.
 */
class CompanyResource extends Resource
{
    protected static ?string $model = Company::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-building-office-2';
    protected static ?string $navigationLabel = 'الشركات';
    protected static ?string $modelLabel = 'شركة';
    protected static ?string $pluralLabel = 'الشركات';
    protected static string|\UnitEnum|null $navigationGroup = 'الاشتراكات والالتزامات';
    protected static ?int $navigationSort = 60;

    public static function canViewAny(): bool
    {
        return DbSchema::hasTable('companies')
            && static::userHasAnyRole(auth()->user(), ['manager', 'support', 'accountant']);
    }

    public static function canCreate(): bool
    {
        return DbSchema::hasTable('companies')
            && static::userHasAnyRole(auth()->user(), ['manager']);
    }

    public static function canView($record): bool
    {
        return static::userHasAnyRole(auth()->user(), ['manager', 'support', 'accountant']);
    }

    public static function canEdit($record): bool
    {
        return static::userHasAnyRole(auth()->user(), ['manager']);
    }

    public static function canDelete($record): bool
    {
        return static::userIsAdmin(auth()->user());
    }

    public static function canDeleteAny(): bool
    {
        return static::userIsAdmin(auth()->user());
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

    protected static function userIsAdmin(?\App\Models\User $user): bool
    {
        return (bool) ($user?->is_admin);
    }

    public static function statusOptions(): array
    {
        return [
            'active'    => 'نشطة',
            'inactive'  => 'غير نشطة',
            'suspended' => 'موقوفة',
        ];
    }

    public static function taxFilingPeriodOptions(): array
    {
        return [
            'monthly'   => 'شهري',
            'quarterly' => 'ربع سنوي',
        ];
    }

    public static function form(Schema $schema): Schema
    {
        $hasTaxFilingPeriod = DbSchema::hasColumn('companies', 'tax_filing_period');

        $taxSection = [
            F\TextInput::make('tax_number')
                ->label('الرقم الضريبي')
                ->maxLength(191),
        ];

        if ($hasTaxFilingPeriod) {
            $taxSection[] = F\Select::make('tax_filing_period')
                ->label('دورة التقديم الضريبي')
                ->options(static::taxFilingPeriodOptions())
                ->nullable()
                ->helperText('شهري إذا الإيراد ≥ 40 مليون ر.س — تابع متطلبات زاتكا.');
        }

        return $schema->components([
            Section::make('بيانات المنشأة')
                ->columns(2)
                ->schema([
                    F\TextInput::make('name')
                        ->label('اسم المنشأة')
                        ->required()
                        ->maxLength(255),

                    F\TextInput::make('commercial_name')
                        ->label('الاسم التجاري')
                        ->maxLength(255),

                    F\TextInput::make('cr_number')
                        ->label('رقم السجل التجاري')
                        ->maxLength(50),

                    F\TextInput::make('unified_number')
                        ->label('الرقم الموحد 700')
                        ->helperText('أدخل أرقامًا فقط، بدون مسافات أو رموز.')
                        ->rules(['nullable', 'regex:/^[0-9]+$/'])
                        ->maxLength(50),

                    F\Select::make('status')
                        ->label('الحالة')
                        ->options(static::statusOptions())
                        ->default('active'),

                    F\TextInput::make('activity')
                        ->label('النشاط')
                        ->maxLength(191),
                ]),

            Section::make('السجل التجاري')
                ->columns(2)
                ->collapsible()
                ->schema([
                    F\DatePicker::make('cr_issue_date')
                        ->label('تاريخ إصدار السجل'),

                    F\DatePicker::make('cr_expiry_date')
                        ->label('تاريخ انتهاء السجل'),

                    F\TextInput::make('entity_size')
                        ->label('حجم المنشأة')
                        ->maxLength(80),

                    F\TextInput::make('employees_count')
                        ->label('عدد الموظفين')
                        ->numeric()
                        ->minValue(0),

                    F\TextInput::make('city')
                        ->label('المدينة')
                        ->maxLength(120),

                    F\Textarea::make('address')
                        ->label('العنوان الوطني')
                        ->rows(2)
                        ->columnSpanFull(),
                ]),

            Section::make('البيانات الضريبية')
                ->columns(2)
                ->collapsible()
                ->schema($taxSection),

            Section::make('التأمينات الاجتماعية والصحية')
                ->columns(2)
                ->collapsible()
                ->collapsed()
                ->schema([
                    F\TextInput::make('gosi_subscription_number')
                        ->label('رقم اشتراك التأمينات')
                        ->maxLength(120),

                    F\TextInput::make('gosi_establishment_id')
                        ->label('رقم منشأة GOSI')
                        ->maxLength(60),

                    F\TextInput::make('medical_insurance_company')
                        ->label('شركة التأمين الطبي')
                        ->maxLength(191),

                    F\TextInput::make('medical_insurance_policy_number')
                        ->label('رقم وثيقة التأمين')
                        ->maxLength(120),
                ]),

            Section::make('ملاحظات إدارية')
                ->collapsible()
                ->collapsed()
                ->schema([
                    F\Textarea::make('internal_notes')
                        ->label('ملاحظات داخلية')
                        ->rows(3)
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        $hasTaxFilingPeriod = DbSchema::hasColumn('companies', 'tax_filing_period');

        $columns = [
            TextColumn::make('id')
                ->label('#')
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),

            TextColumn::make('name')
                ->label('اسم المنشأة')
                ->searchable()
                ->sortable()
                ->weight('semibold'),

            TextColumn::make('cr_number')
                ->label('السجل التجاري')
                ->searchable()
                ->placeholder('—'),

            TextColumn::make('unified_number')
                ->label('الرقم الموحد')
                ->searchable()
                ->placeholder('—')
                ->toggleable(),
        ];

        if ($hasTaxFilingPeriod) {
            $columns[] = TextColumn::make('tax_filing_period')
                ->label('دورة التقديم')
                ->badge()
                ->formatStateUsing(fn ($state) => static::taxFilingPeriodOptions()[$state] ?? ($state ?: '—'))
                ->color('info')
                ->toggleable();
        }

        $columns[] = TextColumn::make('status')
            ->label('الحالة')
            ->badge()
            ->formatStateUsing(fn ($state) => static::statusOptions()[$state] ?? $state)
            ->color(fn ($state) => match ($state) {
                'active'    => 'success',
                'suspended' => 'warning',
                'inactive'  => 'gray',
                default     => 'gray',
            });

        $columns[] = TextColumn::make('updated_at')
            ->label('آخر تحديث')
            ->dateTime()
            ->sortable()
            ->toggleable(isToggledHiddenByDefault: true);

        $filters = [
            SelectFilter::make('status')
                ->label('الحالة')
                ->options(static::statusOptions()),
        ];

        if ($hasTaxFilingPeriod) {
            $filters[] = SelectFilter::make('tax_filing_period')
                ->label('دورة التقديم الضريبي')
                ->options(static::taxFilingPeriodOptions());
        }

        return $table
            ->defaultSort('id', 'desc')
            ->columns($columns)
            ->filters($filters)
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    // لا bulk delete — مخاطرة على بيانات تشغيلية.
                ]),
            ])
            ->emptyStateHeading('لا توجد شركات بعد');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCompanies::route('/'),
            'create' => Pages\CreateCompany::route('/create'),
            'edit'   => Pages\EditCompany::route('/{record}/edit'),
        ];
    }

    /**
     * Phase 9C-6: RelationManagers محروسة بـSchema::hasTable.
     * كل واحدة تظهر فقط بعد توفر جدولها.
     */
    public static function getRelations(): array
    {
        $relations = [];

        if (DbSchema::hasTable('subscriptions')) {
            $relations[] = CompanySubscriptionsRelationManager::class;
        }

        if (DbSchema::hasTable('compliance_obligations')) {
            $relations[] = CompanyComplianceObligationsRelationManager::class;
        }

        if (DbSchema::hasTable('obligation_periods')) {
            $relations[] = CompanyObligationPeriodsRelationManager::class;
        }

        if (DbSchema::hasTable('tax_return_requests')) {
            $relations[] = CompanyTaxReturnRequestsRelationManager::class;
        }

        // ملاحظة Phase 9C-6: لا توجد علاقة tasks مباشرة على Company لأن
        // tasks.company_id غير موجود في الـschema. الربط يتم عبر
        // subscription_id / compliance_obligation_id / obligation_period_id /
        // tax_return_request_id. CompanyTasksRelationManager مؤجَّل لمرحلة
        // لاحقة إن أُريد إضافة العمود أو dataset غير قياسي.

        return $relations;
    }
}
