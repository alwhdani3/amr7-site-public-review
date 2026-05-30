<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SubscriptionResource\Pages;
use App\Filament\Resources\SubscriptionResource\RelationManagers\SubscriptionItemsRelationManager;
use App\Filament\Resources\SubscriptionResource\RelationManagers\SubscriptionStatusLogsRelationManager;
use App\Models\Subscription;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components as F;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema as DbSchema;

/**
 * Phase 9C-2 — Admin Filament Resource for managing company subscriptions.
 *
 *  - يحافظ على الحقول القديمة: company_id, package_id, status, starts_at,
 *    expires_at, remaining_consultations.
 *  - يضيف حقول التشغيل الجديدة (Phase 9B) فقط إذا الأعمدة موجودة في DB.
 *  - لا يكسر إذا migrations ما زالت Pending — كل عمود/جدول جديد محمي
 *    بـSchema::hasColumn / Schema::hasTable.
 */
class SubscriptionResource extends Resource
{
    protected static ?string $model = Subscription::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-credit-card';
    protected static ?string $navigationLabel = 'اشتراكات العملاء';
    protected static ?string $modelLabel = 'اشتراك';
    protected static ?string $pluralLabel = 'اشتراكات العملاء';
    protected static string|\UnitEnum|null $navigationGroup = 'الاشتراكات والالتزامات';
    protected static ?int $navigationSort = 20;

    /**
     * خيارات الحالة المتسقة مع Subscription::getStatusLabelAttribute().
     */
    public static function statusOptions(): array
    {
        return [
            'active'   => 'نشط',
            'pending'  => 'في انتظار الدفع',
            'expired'  => 'منتهي',
            'canceled' => 'ملغي',
        ];
    }

    // ── Authorization ──────────────────────────────────────────────────────────

    public static function canViewAny(): bool
    {
        return static::userHasAnyRole(auth()->user(), ['super_admin', 'admin', 'manager']);
    }

    public static function canCreate(): bool
    {
        return static::userHasAnyRole(auth()->user(), ['super_admin', 'admin', 'manager']);
    }

    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return static::userHasAnyRole(auth()->user(), ['super_admin', 'admin', 'manager']);
    }

    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return static::userHasAnyRole(auth()->user(), ['super_admin', 'admin']);
    }

    public static function canDeleteAny(): bool
    {
        return static::userHasAnyRole(auth()->user(), ['super_admin', 'admin']);
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

    public static function form(Schema $schema): Schema
    {
        // Phase 9C-2: تخزين نتائج Schema::hasColumn مرة واحدة لتقليل الاستعلامات.
        $cols = static::availableOperationalColumns();

        $operationalFields = [];

        if ($cols['plan_code']) {
            $operationalFields[] = F\TextInput::make('plan_code')
                ->label('رمز الخطة')
                ->maxLength(60)
                ->placeholder('مثال: pro-yearly');
        }

        if ($cols['billing_period']) {
            $operationalFields[] = F\Select::make('billing_period')
                ->label('دورة الفوترة')
                ->options([
                    'monthly'   => 'شهري',
                    'quarterly' => 'ربع سنوي',
                    'yearly'    => 'سنوي',
                    'once'      => 'مرة واحدة',
                ])
                ->nullable();
        }

        if ($cols['auto_renew']) {
            $operationalFields[] = F\Toggle::make('auto_renew')
                ->label('تجديد تلقائي')
                ->default(false)
                ->inline(false);
        }

        if ($cols['billing_cycle_anchor_at']) {
            $operationalFields[] = F\DateTimePicker::make('billing_cycle_anchor_at')
                ->label('نقطة بدء دورة الفوترة')
                ->seconds(false);
        }

        if ($cols['last_renewed_at']) {
            $operationalFields[] = F\DateTimePicker::make('last_renewed_at')
                ->label('آخر تجديد')
                ->seconds(false);
        }

        if ($cols['next_renewal_at']) {
            $operationalFields[] = F\DateTimePicker::make('next_renewal_at')
                ->label('التجديد القادم')
                ->seconds(false);
        }

        if ($cols['grace_ends_at']) {
            $operationalFields[] = F\DateTimePicker::make('grace_ends_at')
                ->label('انتهاء مهلة السماح')
                ->seconds(false);
        }

        if ($cols['cancelled_at']) {
            $operationalFields[] = F\DateTimePicker::make('cancelled_at')
                ->label('تاريخ الإلغاء')
                ->seconds(false);
        }

        if ($cols['cancellation_reason']) {
            $operationalFields[] = F\Textarea::make('cancellation_reason')
                ->label('سبب الإلغاء')
                ->rows(3)
                ->columnSpanFull();
        }

        if ($cols['metadata']) {
            $operationalFields[] = F\KeyValue::make('metadata')
                ->label('بيانات إضافية')
                ->keyLabel('المفتاح')
                ->valueLabel('القيمة')
                ->reorderable()
                ->columnSpanFull();
        }

        $components = [
            // قسم 1: الأطراف
            Section::make('الأطراف')
                ->description('الشركة والباقة المرتبطة بالاشتراك.')
                ->columns(2)
                ->schema([
                    F\Select::make('company_id')
                        ->label('الشركة')
                        ->relationship('company', 'name')
                        ->searchable()
                        ->preload()
                        ->required(),

                    F\Select::make('package_id')
                        ->label('الباقة')
                        ->relationship('package', 'name')
                        ->searchable()
                        ->preload()
                        ->required(),
                ]),

            // قسم 2: الفترة والاستهلاك
            Section::make('الفترة والاستهلاك')
                ->columns(2)
                ->schema([
                    F\DateTimePicker::make('starts_at')
                        ->label('تاريخ البدء')
                        ->seconds(false)
                        ->required(),

                    F\DateTimePicker::make('expires_at')
                        ->label('تاريخ الانتهاء')
                        ->seconds(false)
                        ->required(),

                    F\Select::make('status')
                        ->label('الحالة')
                        ->options(static::statusOptions())
                        ->default('active')
                        ->required(),

                    F\TextInput::make('remaining_consultations')
                        ->label('الاستشارات المتبقية')
                        ->numeric()
                        ->minValue(0)
                        ->default(0),
                ]),
        ];

        if (! empty($operationalFields)) {
            $components[] = Section::make('حقول التشغيل المتقدمة')
                ->description('Phase 9B operational fields — اجتزائها يظهر بحسب الأعمدة المتوفرة في DB.')
                ->columns(2)
                ->collapsible()
                ->schema($operationalFields);
        } else {
            $components[] = Section::make('حقول التشغيل المتقدمة')
                ->collapsed()
                ->schema([
                    F\Placeholder::make('phase_9b_notice')
                        ->label('')
                        ->content('حقول التشغيل المتقدمة (التجديد التلقائي، دورة الفوترة، مهلة السماح، إلخ) تظهر بعد تشغيل migrations الخاصة بـ Phase 9B.'),
                ]);
        }

        return $schema->components($components);
    }

    public static function table(Table $table): Table
    {
        $cols = static::availableOperationalColumns();

        $columns = [
            TextColumn::make('id')
                ->label('#')
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),

            TextColumn::make('company.name')
                ->label('الشركة')
                ->searchable()
                ->sortable(),

            TextColumn::make('package.name')
                ->label('الباقة')
                ->searchable()
                ->sortable()
                ->badge()
                ->color('gray'),

            TextColumn::make('status')
                ->label('الحالة')
                ->badge()
                ->formatStateUsing(fn ($state) => static::statusOptions()[$state] ?? $state)
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
                ->sortable()
                ->toggleable(),

            TextColumn::make('expires_at')
                ->label('انتهاء')
                ->date()
                ->sortable(),

            TextColumn::make('remaining_consultations')
                ->label('الاستشارات')
                ->numeric()
                ->suffix(' متبقية')
                ->toggleable(),
        ];

        if ($cols['billing_period']) {
            $columns[] = TextColumn::make('billing_period')
                ->label('دورة الفوترة')
                ->formatStateUsing(fn ($state) => match ($state) {
                    'monthly'   => 'شهري',
                    'quarterly' => 'ربع سنوي',
                    'yearly'    => 'سنوي',
                    'once'      => 'مرة واحدة',
                    default     => $state ?: '—',
                })
                ->badge()
                ->color('info')
                ->toggleable();
        }

        if ($cols['next_renewal_at']) {
            $columns[] = TextColumn::make('next_renewal_at')
                ->label('التجديد القادم')
                ->date()
                ->sortable()
                ->toggleable();
        }

        if ($cols['auto_renew']) {
            $columns[] = IconColumn::make('auto_renew')
                ->label('تجديد تلقائي')
                ->boolean()
                ->toggleable();
        }

        $columns[] = TextColumn::make('updated_at')
            ->label('آخر تحديث')
            ->dateTime()
            ->sortable()
            ->toggleable(isToggledHiddenByDefault: true);

        // Filters
        $filters = [
            SelectFilter::make('status')
                ->label('الحالة')
                ->options(static::statusOptions()),

            SelectFilter::make('package_id')
                ->label('الباقة')
                ->relationship('package', 'name')
                ->searchable()
                ->preload(),

            SelectFilter::make('company_id')
                ->label('الشركة')
                ->relationship('company', 'name')
                ->searchable()
                ->preload(),

            Tables\Filters\Filter::make('active')
                ->label('نشطة فقط')
                ->query(fn (Builder $query) => $query->where('status', 'active')->where('expires_at', '>', now())),

            Tables\Filters\Filter::make('expired')
                ->label('منتهية')
                ->query(fn (Builder $query) => $query->where(function (Builder $q) {
                    $q->where('status', 'expired')->orWhere('expires_at', '<', now());
                })),

            Tables\Filters\Filter::make('expiring_in_30_days')
                ->label('قريبة الانتهاء (30 يوم)')
                ->query(fn (Builder $query) => $query->where('status', 'active')
                    ->whereBetween('expires_at', [now(), now()->addDays(30)])),
        ];

        if ($cols['auto_renew']) {
            $filters[] = TernaryFilter::make('auto_renew')
                ->label('تجديد تلقائي')
                ->trueLabel('مفعّل')
                ->falseLabel('معطّل');
        }

        // Record actions
        $recordActions = [
            EditAction::make(),
        ];

        // Phase 9C-2: Renew action — يعمل دائماً (يحدّث expires_at)، ويُحدّث
        // الحقول التشغيلية الإضافية فقط إذا الأعمدة موجودة.
        $recordActions[] = Action::make('renew')
            ->label('تجديد')
            ->icon('heroicon-o-arrow-path')
            ->color('success')
            ->visible(fn (Subscription $record) => in_array($record->status, ['active', 'expired', 'pending'], true))
            ->form([
                F\DateTimePicker::make('new_expires_at')
                    ->label('تاريخ الانتهاء الجديد')
                    ->seconds(false)
                    ->required()
                    ->default(fn (Subscription $record) => $record->expires_at
                        ? $record->expires_at->copy()->addYear()
                        : now()->addYear()),
            ])
            ->action(function (Subscription $record, array $data) use ($cols) {
                $updates = [
                    'expires_at' => $data['new_expires_at'],
                    'status'     => 'active',
                ];

                if ($cols['last_renewed_at']) {
                    $updates['last_renewed_at'] = now();
                }

                if ($cols['next_renewal_at']) {
                    $updates['next_renewal_at'] = $data['new_expires_at'];
                }

                if ($cols['cancelled_at']) {
                    $updates['cancelled_at'] = null;
                }

                $record->update($updates);
            });

        // Cancel action — يحفظ سبب الإلغاء إذا العمود متوفر.
        $recordActions[] = Action::make('cancel')
            ->label('إلغاء الاشتراك')
            ->icon('heroicon-o-x-circle')
            ->color('danger')
            ->requiresConfirmation()
            ->visible(fn (Subscription $record) => $record->status !== 'canceled')
            ->form([
                F\Textarea::make('reason')
                    ->label('سبب الإلغاء')
                    ->rows(3)
                    ->required(),
            ])
            ->action(function (Subscription $record, array $data) use ($cols) {
                $updates = ['status' => 'canceled'];

                if ($cols['cancelled_at']) {
                    $updates['cancelled_at'] = now();
                }

                if ($cols['cancellation_reason']) {
                    $updates['cancellation_reason'] = $data['reason'];
                }

                $record->update($updates);
            });

        return $table
            ->defaultSort('id', 'desc')
            ->columns($columns)
            ->filters($filters)
            ->recordActions($recordActions)
            ->toolbarActions([
                BulkActionGroup::make([
                    // Phase 9C-2: لا bulk delete/cancel — أكشن خطر يحتاج مرحلة لاحقة.
                ]),
            ])
            ->emptyStateHeading('لا توجد اشتراكات بعد')
            ->emptyStateDescription('بمجرد إنشاء اشتراك لشركة، سيظهر هنا.');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListSubscriptions::route('/'),
            'create' => Pages\CreateSubscription::route('/create'),
            'edit'   => Pages\EditSubscription::route('/{record}/edit'),
        ];
    }

    /**
     * Phase 9C-2: لا تُسجَّل RelationManagers إلا بعد توفر جداولها في DB.
     */
    public static function getRelations(): array
    {
        $relations = [];

        if (DbSchema::hasTable('subscription_items')) {
            $relations[] = SubscriptionItemsRelationManager::class;
        }

        if (DbSchema::hasTable('subscription_status_logs')) {
            $relations[] = SubscriptionStatusLogsRelationManager::class;
        }

        return $relations;
    }

    /**
     * Phase 9C-2: حساب أعمدة Phase 9B المتاحة مرة واحدة وإرجاعها كـmap.
     * تقلّل عدد استعلامات information_schema لكل render.
     */
    protected static function availableOperationalColumns(): array
    {
        if (! DbSchema::hasTable('subscriptions')) {
            return array_fill_keys([
                'plan_code', 'billing_period', 'auto_renew', 'cancelled_at',
                'cancellation_reason', 'billing_cycle_anchor_at', 'last_renewed_at',
                'next_renewal_at', 'grace_ends_at', 'metadata',
            ], false);
        }

        $check = fn (string $col) => DbSchema::hasColumn('subscriptions', $col);

        return [
            'plan_code'               => $check('plan_code'),
            'billing_period'          => $check('billing_period'),
            'auto_renew'              => $check('auto_renew'),
            'cancelled_at'            => $check('cancelled_at'),
            'cancellation_reason'     => $check('cancellation_reason'),
            'billing_cycle_anchor_at' => $check('billing_cycle_anchor_at'),
            'last_renewed_at'         => $check('last_renewed_at'),
            'next_renewal_at'         => $check('next_renewal_at'),
            'grace_ends_at'           => $check('grace_ends_at'),
            'metadata'                => $check('metadata'),
        ];
    }
}
