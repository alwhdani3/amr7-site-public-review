<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PackageResource\Pages;
use App\Filament\Resources\PackageResource\RelationManagers\PackageFeaturesRelationManager;
use App\Models\Package;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components as F;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Illuminate\Support\Facades\Schema as DbSchema;

class PackageResource extends Resource
{
    protected static ?string $model = Package::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-gift';
    protected static ?string $navigationLabel = 'الباقات';
    protected static ?string $pluralLabel = 'الباقات';
    protected static string|\UnitEnum|null $navigationGroup = 'الاشتراكات والالتزامات';
    protected static ?int $navigationSort = 10;

    // Packages are pricing config — restrict write to admin/manager or to the
    // explicit `package_features.manage` permission. Delete stays admin-only
    // because removing a package can break in-flight subscriptions.

    public static function canViewAny(): bool
    {
        return static::userCanManagePackages(auth()->user());
    }

    public static function canCreate(): bool
    {
        return static::userCanManagePackages(auth()->user());
    }

    public static function canEdit($record): bool
    {
        return static::userCanManagePackages(auth()->user());
    }

    public static function canDelete($record): bool
    {
        return static::userIsAdmin(auth()->user());
    }

    public static function canDeleteAny(): bool
    {
        return static::userIsAdmin(auth()->user());
    }

    protected static function userCanManagePackages(?\App\Models\User $user): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->is_admin) {
            return true;
        }

        if (method_exists($user, 'can') && $user->can('package_features.manage')) {
            return true;
        }

        if (method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['manager'])) {
            return true;
        }

        return strtolower((string) ($user->role ?? '')) === 'manager';
    }

    protected static function userIsAdmin(?\App\Models\User $user): bool
    {
        return (bool) ($user?->is_admin);
    }

    public static function form(Schema $schema): Schema
    {
        // Phase 9C-1: ملاحظة عن الميزات المهيكلة — تظهر فقط إذا الجدول جاهز.
        // إذا migrations Pending، نطلب من المسؤول تشغيلها أولاً.
        $structuredNote = DbSchema::hasTable('package_features')
            ? 'الميزات المهيكلة (الجديدة) تُدار من تبويب «الميزات» بعد حفظ الباقة.'
            : 'الميزات المهيكلة غير مفعّلة بعد. شغّل migrations الخاصة بـ package_features لتفعيل التبويب.';

        return $schema->components([
            // قسم 1: بيانات الباقة الأساسية
            Section::make('بيانات الباقة')
                ->description('الاسم والسعر وحد الاستشارات.')
                ->schema([
                    F\TextInput::make('name')
                        ->label('اسم الباقة')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),

                    F\TextInput::make('price')
                        ->label('السعر السنوي (ريال)')
                        ->numeric()
                        ->suffix('ر.س')
                        ->required(),

                    F\TextInput::make('consultation_limit')
                        ->label('عدد الاستشارات المتاحة')
                        ->numeric()
                        ->default(0)
                        ->required(),

                    F\Textarea::make('description')
                        ->label('وصف مختصر')
                        ->rows(4)
                        ->columnSpanFull(),
                ])
                ->columns(2),

            // قسم 2: الميزات القديمة (JSON) — نمط Phase ما قبل 9B، يبقى كما هو.
            Section::make('المميزات القديمة (JSON)')
                ->description('قائمة الميزات الظاهرة في صفحة الباقات بالموقع.')
                ->collapsible()
                ->schema([
                    F\Repeater::make('features')
                        ->label('مميزات الباقة حسب الأقسام')
                        ->schema([
                            F\TextInput::make('title')
                                ->label('اسم القسم')
                                ->placeholder('مثال: منصة قوى')
                                ->required(),

                            F\TagsInput::make('items')
                                ->label('العناصر')
                                ->placeholder('أضف عنصر واضغط Enter')
                                ->required()
                                ->columnSpanFull(),
                        ])
                        ->columns(1)
                        ->defaultItems(0)
                        ->reorderable()
                        ->collapsible()
                        ->cloneable()
                        ->addActionLabel('إضافة قسم جديد')
                        ->columnSpanFull()
                        ->dehydrateStateUsing(function ($state) {
                            if (! is_array($state)) {
                                return [];
                            }

                            $result = [];

                            foreach ($state as $group) {
                                $title = trim($group['title'] ?? '');
                                $items = $group['items'] ?? [];

                                if ($title === '' || ! is_array($items)) {
                                    continue;
                                }

                                $cleanItems = collect($items)
                                    ->map(fn ($item) => is_string($item) ? trim($item) : '')
                                    ->filter(fn ($item) => $item !== '')
                                    ->values()
                                    ->all();

                                if (! empty($cleanItems)) {
                                    $result[$title] = $cleanItems;
                                }
                            }

                            return $result;
                        })
                        ->formatStateUsing(function ($state) {
                            if (! is_array($state)) {
                                return [];
                            }

                            $formatted = [];

                            foreach ($state as $title => $items) {
                                if (is_string($title) && is_array($items)) {
                                    $formatted[] = [
                                        'title' => $title,
                                        'items' => array_values(array_filter($items, fn ($item) => is_string($item) && trim($item) !== '')),
                                    ];
                                }
                            }

                            return $formatted;
                        }),
                ]),

            // قسم 3: الحالة والظهور
            Section::make('الحالة والظهور')
                ->description('التحكم في عرض الباقة وتمييزها.')
                ->schema([
                    F\Toggle::make('is_active')
                        ->label('مفعّلة للعرض بالموقع')
                        ->default(true)
                        ->inline(false)
                        ->helperText('إذا تم إيقافها لن تظهر في صفحة الباقات ولن تُعرض تفاصيلها للزوار.'),

                    F\Toggle::make('is_featured')
                        ->label('باقة مميزة')
                        ->default(false)
                        ->inline(false),
                ])
                ->columns(2),

            // قسم 4: ملاحظة الميزات المهيكلة (Phase 9B).
            Section::make('الميزات المهيكلة')
                ->description($structuredNote)
                ->collapsed()
                ->schema([
                    F\Placeholder::make('structured_features_hint')
                        ->label('')
                        ->content($structuredNote),
                ]),

            // الباقات المحاسبية — حقول إضافية محروسة بـSchema::hasColumn.
            // تظهر فقط بعد تشغيل migration 2026_05_24_120000.
            ...static::accountingSections(),
        ]);
    }

    /**
     * Accounting-specific sections (slug/kind/agreement template/JSON config).
     * Returns an empty array if the columns have not been migrated yet, so
     * the form keeps working in either state.
     */
    protected static function accountingSections(): array
    {
        $hasSlug   = DbSchema::hasColumn('packages', 'slug');
        $hasKind   = DbSchema::hasColumn('packages', 'kind');
        $hasAtFk   = DbSchema::hasColumn('packages', 'agreement_template_id');
        $hasConfig = DbSchema::hasColumn('packages', 'accounting_config');
        $hasTplTbl = DbSchema::hasTable('agreement_templates');

        if (! $hasSlug && ! $hasKind && ! $hasConfig && ! $hasAtFk) {
            return [];
        }

        $identityFields = [];
        if ($hasSlug) {
            $identityFields[] = F\TextInput::make('slug')
                ->label('المعرّف (slug)')
                ->maxLength(80)
                ->unique(ignoreRecord: true)
                ->regex('/^[a-z0-9-]+$/')
                ->helperText('إنجليزي صغير وأرقام وشَرطة. يُستخدم من المُثبِّت لإعادة التطبيق بأمان.')
                ->placeholder('accounting-basic');
        }
        if ($hasKind) {
            $identityFields[] = F\Select::make('kind')
                ->label('نوع الباقة')
                ->options([
                    'monthly'   => 'شهرية',
                    'quarterly' => 'ربع سنوية',
                    'yearly'    => 'سنوية',
                    'custom'    => 'مخصصة',
                ])
                ->nullable();
        }
        if ($hasAtFk && $hasTplTbl) {
            $identityFields[] = F\Select::make('agreement_template_id')
                ->label('قالب الاتفاقية')
                ->relationship('agreementTemplate', 'name')
                ->searchable()
                ->preload()
                ->nullable()
                ->helperText('القالب الذي سيُولَّد منه نص الاتفاقية للعميل عند الاشتراك.');
        }

        $configFields = [];
        if ($hasConfig) {
            $configFields = [
                F\TextInput::make('accounting_config.base_price')
                    ->label('السعر الأساسي (قبل الضريبة)')
                    ->numeric()
                    ->minValue(0)
                    ->suffix('ر.س'),

                F\TextInput::make('accounting_config.vat_rate')
                    ->label('نسبة الضريبة %')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->default(15)
                    ->suffix('٪'),

                F\TextInput::make('accounting_config.invoice_sales_limit')
                    ->label('حد فواتير المبيعات')
                    ->numeric()
                    ->minValue(0)
                    ->helperText('عدد فواتير المبيعات الشهرية المشمولة.'),

                F\TextInput::make('accounting_config.invoice_purchase_limit')
                    ->label('حد فواتير المشتريات')
                    ->numeric()
                    ->minValue(0),

                F\Toggle::make('accounting_config.includes_vat')
                    ->label('يشمل إعداد ضريبة القيمة المضافة'),

                F\Toggle::make('accounting_config.includes_zakat')
                    ->label('يشمل الزكاة'),

                F\Toggle::make('accounting_config.includes_financial_statements')
                    ->label('يشمل القوائم المالية'),

                F\Toggle::make('accounting_config.includes_monthly_reports')
                    ->label('يشمل تقارير شهرية'),

                F\Toggle::make('accounting_config.includes_quarterly_reports')
                    ->label('يشمل تقارير ربع سنوية'),

                F\Textarea::make('accounting_config.included_services')
                    ->label('الخدمات المشمولة')
                    ->rows(4)
                    ->columnSpanFull()
                    ->helperText('سطر لكل خدمة.'),

                F\Textarea::make('accounting_config.excluded_services')
                    ->label('الخدمات غير المشمولة')
                    ->rows(3)
                    ->columnSpanFull(),

                F\Textarea::make('accounting_config.client_requirements')
                    ->label('متطلبات العميل')
                    ->rows(3)
                    ->columnSpanFull(),

                F\TextInput::make('accounting_config.agreement_duration_months')
                    ->label('مدة الاتفاقية (شهور)')
                    ->numeric()
                    ->minValue(1)
                    ->default(12),

                F\TextInput::make('accounting_config.renewal_notice_days')
                    ->label('تنبيه قبل الانتهاء (أيام)')
                    ->numeric()
                    ->minValue(0)
                    ->default(30),

                F\Textarea::make('accounting_config.payment_terms')
                    ->label('شروط الدفع')
                    ->rows(2)
                    ->columnSpanFull(),
            ];
        }

        $sections = [];

        if (! empty($identityFields)) {
            $sections[] = Section::make('الباقة المحاسبية — المعرّفات')
                ->description('المعرّفات والنوع وقالب الاتفاقية.')
                ->columns(2)
                ->collapsible()
                ->schema($identityFields);
        }

        if (! empty($configFields)) {
            $sections[] = Section::make('الباقة المحاسبية — التسعير والمحتوى')
                ->description('السعر الأساسي والضريبة والخدمات المشمولة وحدود الفواتير.')
                ->columns(2)
                ->collapsible()
                ->schema($configFields);
        }

        return $sections;
    }

    public static function table(Table $table): Table
    {
        // Phase 9C-1: نضيف عداد الميزات المهيكلة فقط إذا الجدول موجود.
        $packageFeaturesReady = DbSchema::hasTable('package_features');

        $columns = [
            TextColumn::make('id')
                ->label('#')
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),

            TextColumn::make('name')
                ->label('الباقة')
                ->searchable()
                ->sortable()
                ->badge(),

            TextColumn::make('price')
                ->label('السعر')
                ->money('SAR')
                ->sortable(),

            TextColumn::make('consultation_limit')
                ->label('الاستشارات')
                ->suffix(' استشارة')
                ->sortable(),

            TextColumn::make('is_featured')
                ->label('مميزة')
                ->badge()
                ->formatStateUsing(fn ($state) => $state ? 'نعم' : 'لا')
                ->colors([
                    'success' => fn ($state) => (bool) $state,
                    'gray' => fn ($state) => ! $state,
                ]),

            TextColumn::make('features')
                ->label('المميزات (JSON)')
                ->formatStateUsing(function ($state) {
                    if (! is_array($state)) {
                        return '-';
                    }

                    $count = collect($state)
                        ->filter(fn ($items, $title) => is_string($title) && is_array($items))
                        ->sum(fn ($items) => count($items));

                    return $count > 0 ? $count . ' عنصر' : '-';
                })
                ->badge()
                ->color('gray'),
        ];

        // Phase 9C-1: عمود اختياري لعدد الميزات المهيكلة عند توفر الجدول.
        if ($packageFeaturesReady) {
            $columns[] = TextColumn::make('package_features_count')
                ->label('ميزات مهيكلة')
                ->counts('packageFeatures')
                ->badge()
                ->color('info');
        }

        $columns = array_merge($columns, [
            ToggleColumn::make('is_active')
                ->label('مفعلة؟')
                ->sortable(),

            TextColumn::make('created_at')
                ->label('تاريخ الإنشاء')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
        ]);

        return $table
            ->defaultSort('id', 'desc')
            ->columns($columns)
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('حالة التفعيل')
                    ->trueLabel('مفعلة')
                    ->falseLabel('غير مفعلة'),

                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label('مميزة')
                    ->trueLabel('مميزة')
                    ->falseLabel('غير مميزة'),
            ])
            ->recordActions([
                EditAction::make(),

                Action::make('toggleActive')
                    ->label(fn (Package $record) => $record->is_active ? 'إيقاف' : 'تفعيل')
                    ->icon(fn (Package $record) => $record->is_active ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                    ->color(fn (Package $record) => $record->is_active ? 'danger' : 'success')
                    ->requiresConfirmation()
                    ->action(fn (Package $record) => $record->update(['is_active' => ! $record->is_active])),

                // Phase 9C-1: نسخ باقة مع ميزاتها المهيكلة إن وُجد الجدول.
                Action::make('duplicate')
                    ->label('نسخ الباقة')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->modalHeading('نسخ الباقة')
                    ->modalDescription('سيتم إنشاء باقة جديدة كنسخة من هذه الباقة. النسخة تبدأ غير مفعّلة.')
                    ->action(function (Package $record) {
                        $copy = $record->replicate(['created_at', 'updated_at']);
                        $copy->name = $record->name . ' (نسخة)';
                        $copy->is_active = false;
                        $copy->is_featured = false;
                        $copy->save();

                        // نسخ الميزات المهيكلة فقط إذا الجدول جاهز.
                        if (DbSchema::hasTable('package_features')) {
                            foreach ($record->packageFeatures as $feature) {
                                $featureCopy = $feature->replicate(['created_at', 'updated_at']);
                                $featureCopy->package_id = $copy->id;
                                $featureCopy->save();
                            }
                        }
                    }),

                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('activate')
                        ->label('تفعيل المحدد')
                        ->icon('heroicon-o-check')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->update(['is_active' => true])),

                    BulkAction::make('deactivate')
                        ->label('إيقاف المحدد')
                        ->icon('heroicon-o-x-mark')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->update(['is_active' => false])),

                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('لا توجد باقات بعد')
            ->emptyStateDescription('ابدأ بإضافة باقات لعرضها للعملاء.');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPackages::route('/'),
            'create' => Pages\CreatePackage::route('/create'),
            'edit'   => Pages\EditPackage::route('/{record}/edit'),
        ];
    }

    /**
     * Phase 9C-1: تُسجَّل علاقة الميزات المهيكلة فقط إذا الجدول مُهيّأ في DB.
     * هذا يمنع كسر صفحة الباقات قبل تشغيل Phase 9B migrations.
     */
    public static function getRelations(): array
    {
        if (! DbSchema::hasTable('package_features')) {
            return [];
        }

        return [
            PackageFeaturesRelationManager::class,
        ];
    }
}