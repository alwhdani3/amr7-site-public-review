<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ObligationPeriodResource\Pages;
use App\Models\ObligationPeriod;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Forms\Components as F;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema as DbSchema;

/**
 * Phase 9C-3 — استعراض مسطّح لفترات الالتزام عبر جميع الشركات.
 *
 *  - يُخفى تلقائياً إذا obligation_periods غير موجود.
 *  - حقول financial_statement_request_id و tax_return_request_id محروسة
 *    لأن جداولها قد تكون غير موجودة بعد.
 */
class ObligationPeriodResource extends Resource
{
    protected static ?string $model = ObligationPeriod::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationLabel = 'فترات الالتزام';
    protected static ?string $modelLabel = 'فترة التزام';
    protected static ?string $pluralLabel = 'فترات الالتزام';
    protected static string|\UnitEnum|null $navigationGroup = 'الاشتراكات والالتزامات';
    protected static ?int $navigationSort = 40;

    public static function canViewAny(): bool
    {
        return DbSchema::hasTable('obligation_periods')
            && static::userHasAnyRole(auth()->user(), ['manager', 'accountant']);
    }

    public static function canCreate(): bool
    {
        return DbSchema::hasTable('obligation_periods')
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

    public static function form(Schema $schema): Schema
    {
        $hasFinancialRequests = DbSchema::hasTable('financial_statement_requests');
        $hasTaxReturnRequests = DbSchema::hasTable('tax_return_requests');

        $linkFields = [];

        if ($hasFinancialRequests) {
            $linkFields[] = F\Select::make('financial_statement_request_id')
                ->label('ربط بطلب قوائم مالية')
                ->relationship('financialStatementRequest', 'public_id')
                ->getOptionLabelFromRecordUsing(fn ($record) => $record->public_id ?: 'بدون رقم #' . $record->id)
                ->searchable()
                ->preload()
                ->nullable();
        }

        if ($hasTaxReturnRequests) {
            $linkFields[] = F\Select::make('tax_return_request_id')
                ->label('ربط بإقرار ضريبي')
                ->relationship('taxReturnRequest', 'public_id')
                ->getOptionLabelFromRecordUsing(fn ($record) => $record->public_id ?: 'بدون رقم #' . $record->id)
                ->searchable()
                ->preload()
                ->nullable();
        }

        $components = [
            Section::make('الالتزام والشركة')
                ->columns(2)
                ->schema([
                    F\Select::make('compliance_obligation_id')
                        ->label('الالتزام')
                        ->relationship('obligation', 'obligation_type')
                        ->searchable()
                        ->preload()
                        ->required(),

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
                        ->nullable(),
                ]),

            Section::make('تفاصيل الفترة')
                ->columns(2)
                ->schema([
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
                ]),
        ];

        if (! empty($linkFields)) {
            $components[] = Section::make('روابط الفترة')
                ->description('ربط الفترة بطلب قوائم مالية أو إقرار ضريبي.')
                ->columns(2)
                ->collapsible()
                ->schema($linkFields);
        }

        $components[] = Section::make('الإغلاق')
            ->columns(2)
            ->collapsed()
            ->schema([
                F\DateTimePicker::make('closed_at')
                    ->label('تاريخ الإغلاق')
                    ->seconds(false),

                F\Select::make('closed_by_user_id')
                    ->label('أُغلقت بواسطة')
                    ->relationship('closedByUser', 'name')
                    ->searchable()
                    ->preload()
                    ->nullable(),

                F\KeyValue::make('metadata')
                    ->label('بيانات إضافية')
                    ->reorderable()
                    ->columnSpanFull(),
            ]);

        return $schema->components($components);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('due_date', 'asc')
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('company.name')
                    ->label('الشركة')
                    ->searchable()
                    ->sortable(),

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

                TextColumn::make('period_start')
                    ->label('بداية')
                    ->date()
                    ->toggleable(),

                TextColumn::make('period_end')
                    ->label('نهاية')
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
            ->filters([
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options(static::statusOptions()),

                SelectFilter::make('company_id')
                    ->label('الشركة')
                    ->relationship('company', 'name')
                    ->searchable()
                    ->preload(),

                Filter::make('upcoming')
                    ->label('قادمة فقط')
                    ->query(fn (Builder $query) => $query->where('status', 'upcoming')),

                Filter::make('open')
                    ->label('مفتوحة')
                    ->query(fn (Builder $query) => $query->whereIn('status', [
                        'open', 'client_uploading', 'files_uploaded', 'under_review', 'client_approval',
                    ])),

                Filter::make('late')
                    ->label('متأخرة')
                    ->query(fn (Builder $query) => $query->where(function (Builder $q) {
                        $q->where('status', 'late')
                          ->orWhere(function (Builder $q2) {
                              $q2->whereIn('status', [
                                  'open', 'client_uploading', 'files_uploaded', 'under_review', 'client_approval',
                              ])->where('due_date', '<', now()->toDateString());
                          });
                    })),

                Filter::make('filed')
                    ->label('تم التقديم')
                    ->query(fn (Builder $query) => $query->where('status', 'filed')),

                Filter::make('missed')
                    ->label('فائتة')
                    ->query(fn (Builder $query) => $query->where('status', 'missed')),
            ])
            ->recordActions([
                EditAction::make(),

                Action::make('open_period')
                    ->label('فتح')
                    ->icon('heroicon-o-lock-open')
                    ->color('info')
                    ->requiresConfirmation()
                    ->visible(fn (ObligationPeriod $record) => static::canEdit($record) && $record->status === 'upcoming')
                    ->action(fn (ObligationPeriod $record) => $record->update(['status' => 'open'])),

                Action::make('mark_late')
                    ->label('وضع متأخر')
                    ->icon('heroicon-o-exclamation-triangle')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn (ObligationPeriod $record) => static::canEdit($record) && ! in_array($record->status, ['filed', 'cancelled', 'late', 'missed'], true))
                    ->action(fn (ObligationPeriod $record) => $record->update(['status' => 'late'])),

                Action::make('close_as_filed')
                    ->label('إغلاق كمُقدّم')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (ObligationPeriod $record) => static::canEdit($record) && ! in_array($record->status, ['filed', 'cancelled'], true))
                    ->action(fn (ObligationPeriod $record) => $record->update([
                        'status'    => 'filed',
                        'closed_at' => now(),
                    ])),

                Action::make('cancel_period')
                    ->label('إلغاء')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (ObligationPeriod $record) => static::canEdit($record) && $record->status !== 'cancelled')
                    ->action(fn (ObligationPeriod $record) => $record->update(['status' => 'cancelled'])),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    // لا bulk delete — مخاطرة مع logs.
                ]),
            ])
            ->emptyStateHeading('لا توجد فترات بعد');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListObligationPeriods::route('/'),
            'create' => Pages\CreateObligationPeriod::route('/create'),
            'edit'   => Pages\EditObligationPeriod::route('/{record}/edit'),
        ];
    }
}
