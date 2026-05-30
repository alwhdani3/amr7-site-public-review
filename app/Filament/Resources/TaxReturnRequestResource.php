<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TaxReturnRequestResource\Pages;
use App\Filament\Resources\TaxReturnRequestResource\RelationManagers\TaxReturnFilesRelationManager;
use App\Models\TaxReturnRequest;
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
 * Phase 9C-4 — Filament Resource للإقرارات الضريبية (VAT/Zakat/Withholding).
 *
 *  - يُخفى تلقائياً إذا tax_return_requests غير موجود.
 *  - لا تقديم تلقائي لـZATCA — كل actions يدوية.
 *  - لا notifications، لا scheduler، لا API.
 */
class TaxReturnRequestResource extends Resource
{
    protected static ?string $model = TaxReturnRequest::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'الإقرارات الضريبية';
    protected static ?string $modelLabel = 'إقرار ضريبي';
    protected static ?string $pluralLabel = 'الإقرارات الضريبية';
    protected static string|\UnitEnum|null $navigationGroup = 'الاشتراكات والالتزامات';
    protected static ?int $navigationSort = 50;

    public static function canViewAny(): bool
    {
        return DbSchema::hasTable('tax_return_requests')
            && static::userHasAnyRole(auth()->user(), ['manager', 'accountant']);
    }

    public static function canCreate(): bool
    {
        return DbSchema::hasTable('tax_return_requests')
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

    public static function kindOptions(): array
    {
        return [
            'vat'         => 'ضريبة القيمة المضافة',
            'zakat'       => 'زكاة',
            'withholding' => 'استقطاع',
            'other'       => 'أخرى',
        ];
    }

    public static function statusOptions(): array
    {
        return [
            'draft'           => 'مسودة',
            'files_pending'   => 'بانتظار الملفات',
            'under_review'    => 'قيد المراجعة',
            'client_approval' => 'بانتظار اعتماد العميل',
            'filed'           => 'تم التقديم',
            'rejected'        => 'مرفوض',
            'cancelled'       => 'ملغي',
        ];
    }

    public static function form(Schema $schema): Schema
    {
        $hasObligationPeriods = DbSchema::hasTable('obligation_periods');

        $linkFields = [
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
        ];

        if ($hasObligationPeriods) {
            $linkFields[] = F\Select::make('obligation_period_id')
                ->label('فترة الالتزام (اختياري)')
                ->relationship('obligationPeriods', 'period_label')
                ->searchable()
                ->preload()
                ->nullable()
                ->helperText('اربط الإقرار بفترة التزام معيّنة إن وُجدت.');
        }

        return $schema->components([
            Section::make('التعريف')
                ->columns(2)
                ->schema([
                    F\TextInput::make('public_id')
                        ->label('رقم الإقرار العام')
                        ->maxLength(32)
                        ->helperText('اتركه فارغاً إذا سيُولَّد لاحقاً.'),

                    F\Select::make('kind')
                        ->label('نوع الإقرار')
                        ->options(static::kindOptions())
                        ->default('vat')
                        ->required(),

                    F\Select::make('status')
                        ->label('الحالة')
                        ->options(static::statusOptions())
                        ->default('files_pending')
                        ->required(),
                ]),

            Section::make('الأطراف')
                ->columns(2)
                ->schema($linkFields),

            Section::make('الفترة المالية')
                ->columns(2)
                ->schema([
                    F\DatePicker::make('fiscal_period_start')
                        ->label('بداية الفترة المالية'),

                    F\DatePicker::make('fiscal_period_end')
                        ->label('نهاية الفترة المالية'),
                ]),

            Section::make('الأرقام')
                ->description('قيم العملة بالريال السعودي.')
                ->columns(3)
                ->schema([
                    F\TextInput::make('total_sales')
                        ->label('إجمالي المبيعات')
                        ->numeric()
                        ->prefix('SAR'),

                    F\TextInput::make('total_purchases')
                        ->label('إجمالي المشتريات')
                        ->numeric()
                        ->prefix('SAR'),

                    F\TextInput::make('computed_tax_due')
                        ->label('الضريبة المحسوبة')
                        ->numeric()
                        ->prefix('SAR'),
                ]),

            Section::make('تقديم زاتكا (يدوي)')
                ->description('سجّل التقديم يدوياً — لا يوجد تكامل API تلقائي.')
                ->columns(2)
                ->collapsed()
                ->schema([
                    F\DateTimePicker::make('submitted_to_zatca_at')
                        ->label('تاريخ التقديم لزاتكا')
                        ->seconds(false),

                    F\TextInput::make('zatca_reference')
                        ->label('مرجع زاتكا')
                        ->maxLength(191),
                ]),

            Section::make('ملاحظات')
                ->columns(1)
                ->collapsed()
                ->schema([
                    F\Textarea::make('client_notes')
                        ->label('ملاحظات العميل')
                        ->rows(3),

                    F\Textarea::make('admin_notes')
                        ->label('ملاحظات الإدارة')
                        ->rows(3),
                ]),

            Section::make('سجل المراجعة والاعتماد')
                ->description('تُحدَّث تلقائياً عند تنفيذ Actions.')
                ->columns(2)
                ->collapsed()
                ->schema([
                    F\Placeholder::make('reviewed_at_display')
                        ->label('تاريخ المراجعة')
                        ->content(fn (?TaxReturnRequest $record) => $record?->reviewed_at?->format('Y-m-d H:i') ?? '—'),

                    F\Placeholder::make('reviewer_display')
                        ->label('المُراجع')
                        ->content(fn (?TaxReturnRequest $record) => $record?->reviewedBy?->name ?? '—'),

                    F\Placeholder::make('client_approved_at_display')
                        ->label('تاريخ اعتماد العميل')
                        ->content(fn (?TaxReturnRequest $record) => $record?->client_approved_at?->format('Y-m-d H:i') ?? '—'),

                    F\Placeholder::make('client_approver_display')
                        ->label('المعتمِد')
                        ->content(fn (?TaxReturnRequest $record) => $record?->clientApprovedBy?->name ?? '—'),
                ]),

            Section::make('بيانات إضافية')
                ->collapsed()
                ->schema([
                    F\KeyValue::make('metadata')
                        ->label('بيانات إضافية')
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
                TextColumn::make('public_id')
                    ->label('الرقم')
                    ->searchable()
                    ->copyable()
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('company.name')
                    ->label('الشركة')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('kind')
                    ->label('النوع')
                    ->badge()
                    ->formatStateUsing(fn ($state) => static::kindOptions()[$state] ?? $state)
                    ->color('info'),

                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn ($state) => static::statusOptions()[$state] ?? $state)
                    ->color(fn ($state) => match ($state) {
                        'filed'                                   => 'success',
                        'under_review', 'client_approval'         => 'warning',
                        'rejected', 'cancelled'                   => 'danger',
                        'files_pending'                           => 'info',
                        default                                   => 'gray',
                    }),

                TextColumn::make('fiscal_period_start')
                    ->label('بداية الفترة')
                    ->date()
                    ->toggleable(),

                TextColumn::make('fiscal_period_end')
                    ->label('نهاية الفترة')
                    ->date()
                    ->sortable(),

                TextColumn::make('computed_tax_due')
                    ->label('الضريبة')
                    ->money('SAR')
                    ->toggleable(),

                TextColumn::make('updated_at')
                    ->label('آخر تحديث')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('kind')
                    ->label('النوع')
                    ->options(static::kindOptions()),

                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options(static::statusOptions()),

                SelectFilter::make('company_id')
                    ->label('الشركة')
                    ->relationship('company', 'name')
                    ->searchable()
                    ->preload(),

                Filter::make('files_pending')
                    ->label('بانتظار الملفات')
                    ->query(fn (Builder $query) => $query->where('status', 'files_pending')),

                Filter::make('under_review')
                    ->label('قيد المراجعة')
                    ->query(fn (Builder $query) => $query->where('status', 'under_review')),

                Filter::make('client_approval')
                    ->label('بانتظار اعتماد العميل')
                    ->query(fn (Builder $query) => $query->where('status', 'client_approval')),

                Filter::make('filed')
                    ->label('تم التقديم')
                    ->query(fn (Builder $query) => $query->where('status', 'filed')),
            ])
            ->recordActions([
                EditAction::make(),

                Action::make('mark_under_review')
                    ->label('بدء المراجعة')
                    ->icon('heroicon-o-eye')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn (TaxReturnRequest $record) => static::canEdit($record) && in_array($record->status, ['draft', 'files_pending'], true))
                    ->action(function (TaxReturnRequest $record) {
                        $updates = ['status' => 'under_review'];

                        if (DbSchema::hasColumn('tax_return_requests', 'reviewed_at')) {
                            $updates['reviewed_at'] = now();
                        }

                        if (DbSchema::hasColumn('tax_return_requests', 'reviewed_by_user_id') && auth()->id()) {
                            $updates['reviewed_by_user_id'] = auth()->id();
                        }

                        $record->update($updates);
                    }),

                Action::make('request_client_approval')
                    ->label('طلب اعتماد العميل')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('info')
                    ->requiresConfirmation()
                    ->visible(fn (TaxReturnRequest $record) => static::canEdit($record) && $record->status === 'under_review')
                    ->action(fn (TaxReturnRequest $record) => $record->update(['status' => 'client_approval'])),

                Action::make('mark_filed')
                    ->label('تأكيد التقديم')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (TaxReturnRequest $record) => static::canEdit($record) && ! in_array($record->status, ['filed', 'cancelled', 'rejected'], true))
                    ->form([
                        F\DateTimePicker::make('submitted_to_zatca_at')
                            ->label('تاريخ التقديم لزاتكا')
                            ->seconds(false)
                            ->default(now())
                            ->required(),
                        F\TextInput::make('zatca_reference')
                            ->label('مرجع زاتكا')
                            ->maxLength(191)
                            ->required(),
                    ])
                    ->action(function (TaxReturnRequest $record, array $data) {
                        $record->update([
                            'status'                => 'filed',
                            'submitted_to_zatca_at' => $data['submitted_to_zatca_at'],
                            'zatca_reference'       => $data['zatca_reference'],
                        ]);
                    }),

                Action::make('reject')
                    ->label('رفض')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (TaxReturnRequest $record) => static::canEdit($record) && ! in_array($record->status, ['filed', 'cancelled', 'rejected'], true))
                    ->form([
                        F\Textarea::make('admin_notes')
                            ->label('سبب الرفض')
                            ->rows(3)
                            ->required(),
                    ])
                    ->action(function (TaxReturnRequest $record, array $data) {
                        $record->update([
                            'status'      => 'rejected',
                            'admin_notes' => $data['admin_notes'],
                        ]);
                    }),

                Action::make('cancel_request')
                    ->label('إلغاء')
                    ->icon('heroicon-o-x-circle')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->visible(fn (TaxReturnRequest $record) => static::canEdit($record) && ! in_array($record->status, ['filed', 'cancelled'], true))
                    ->action(fn (TaxReturnRequest $record) => $record->update(['status' => 'cancelled'])),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    // لا bulk delete — مخاطرة.
                ]),
            ])
            ->emptyStateHeading('لا توجد إقرارات ضريبية بعد');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListTaxReturnRequests::route('/'),
            'create' => Pages\CreateTaxReturnRequest::route('/create'),
            'edit'   => Pages\EditTaxReturnRequest::route('/{record}/edit'),
        ];
    }

    public static function getRelations(): array
    {
        if (! DbSchema::hasTable('tax_return_files')) {
            return [];
        }

        return [
            TaxReturnFilesRelationManager::class,
        ];
    }
}
