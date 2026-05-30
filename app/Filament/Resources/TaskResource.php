<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TaskResource\Pages;
use App\Models\ComplianceObligation;
use App\Models\Department;
use App\Models\ObligationPeriod;
use App\Models\Subscription;
use App\Models\Task;
use App\Models\TaxReturnRequest;
use App\Models\Ticket;
use App\Models\User;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;

use Filament\Forms\Components as F;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema as DbSchema;

class TaskResource extends Resource
{
    protected static ?string $model = Task::class;

    protected static string|\UnitEnum|null $navigationGroup  = 'الإدارة';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationLabel = 'المهام';
    protected static ?string $modelLabel = 'مهمة';
    protected static ?string $pluralModelLabel = 'المهام';

    protected static function isAdmin(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        return method_exists($user, 'isAdmin')
            ? (bool) $user->isAdmin()
            : ($user->role === 'admin');
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

    public static function canDelete($record): bool
    {
        return static::isAdmin();
    }

    public static function canDeleteAny(): bool
    {
        return static::isAdmin();
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

    public static function getNavigationBadge(): ?string
    {
        // يعرض عدد المهام غير المكتملة في الـ badge
        $count = Task::query()
            ->whereNotIn('status', ['done', 'cancelled'])
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    /**
     * Phase 9C-7: ربط Task بسياقات تشغيلية اختيارية.
     * كل عمود محروس بـSchema::hasColumn — لن يظهر إذا الـmigration معلّقة.
     */
    protected static function operationalLinkColumns(): array
    {
        if (! DbSchema::hasTable('tasks')) {
            return [
                'subscription_id'          => false,
                'compliance_obligation_id' => false,
                'obligation_period_id'     => false,
                'tax_return_request_id'    => false,
            ];
        }

        return [
            'subscription_id'          => DbSchema::hasColumn('tasks', 'subscription_id'),
            'compliance_obligation_id' => DbSchema::hasColumn('tasks', 'compliance_obligation_id'),
            'obligation_period_id'     => DbSchema::hasColumn('tasks', 'obligation_period_id'),
            'tax_return_request_id'    => DbSchema::hasColumn('tasks', 'tax_return_request_id'),
        ];
    }

    protected static function fallbackLabel(string $prefix, mixed $id, ?string $label = null): string
    {
        $label = trim((string) $label);

        if ($label !== '') {
            return $label;
        }

        return filled($id) ? "{$prefix} #{$id}" : 'بدون عنوان';
    }

    protected static function userLabel(User $user): string
    {
        return static::fallbackLabel('مستخدم', $user->id, $user->name);
    }

    protected static function departmentLabel(Department $department): string
    {
        return static::fallbackLabel('قسم', $department->id, $department->name);
    }

    protected static function subscriptionLabel(Subscription $subscription): string
    {
        return static::fallbackLabel('اشتراك', $subscription->id);
    }

    protected static function complianceObligationLabel(ComplianceObligation $obligation): string
    {
        return static::fallbackLabel('التزام', $obligation->id, $obligation->obligation_type);
    }

    protected static function obligationPeriodLabel(ObligationPeriod $period): string
    {
        return static::fallbackLabel('فترة', $period->id, $period->period_label);
    }

    protected static function taxReturnRequestLabel(TaxReturnRequest $request): string
    {
        return static::fallbackLabel('إقرار', $request->id, $request->public_id);
    }

    protected static function ticketLabel(Ticket $ticket): string
    {
        $number = trim((string) $ticket->ticket_number);
        $subject = trim((string) $ticket->subject);

        if ($number !== '' && $subject !== '') {
            return "#{$number} - {$subject}";
        }

        return static::fallbackLabel('تذكرة', $ticket->id, $number ?: $subject);
    }

    public static function form(Schema $schema): Schema
    {
        $cols = static::operationalLinkColumns();

        // حقول الربط التشغيلي الاختيارية (Phase 9B).
        $operationalLinks = [];

        if ($cols['subscription_id']) {
            $operationalLinks[] = F\Select::make('subscription_id')
                ->label('الاشتراك المرتبط')
                ->relationship('subscription', 'id')
                ->getOptionLabelFromRecordUsing(fn (Subscription $record): string => static::subscriptionLabel($record))
                ->searchable()
                ->preload()
                ->nullable();
        }

        if ($cols['compliance_obligation_id']) {
            $operationalLinks[] = F\Select::make('compliance_obligation_id')
                ->label('الالتزام المرتبط')
                ->relationship('complianceObligation', 'obligation_type')
                ->getOptionLabelFromRecordUsing(fn (ComplianceObligation $record): string => static::complianceObligationLabel($record))
                ->searchable()
                ->preload()
                ->nullable();
        }

        if ($cols['obligation_period_id']) {
            $operationalLinks[] = F\Select::make('obligation_period_id')
                ->label('فترة الالتزام')
                ->relationship('obligationPeriod', 'period_label')
                ->getOptionLabelFromRecordUsing(fn (ObligationPeriod $record): string => static::obligationPeriodLabel($record))
                ->searchable()
                ->preload()
                ->nullable();
        }

        if ($cols['tax_return_request_id']) {
            $operationalLinks[] = F\Select::make('tax_return_request_id')
                ->label('الإقرار الضريبي')
                ->relationship('taxReturnRequest', 'public_id')
                ->getOptionLabelFromRecordUsing(fn (TaxReturnRequest $record): string => static::taxReturnRequestLabel($record))
                ->searchable()
                ->preload()
                ->nullable();
        }

        $components = [
            Section::make('تفاصيل المهمة')
                ->schema([
                    F\TextInput::make('title')
                        ->label('عنوان المهمة')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),

                    F\Textarea::make('description')
                        ->label('وصف المهمة')
                        ->rows(4)
                        ->nullable()
                        ->columnSpanFull(),

                    F\Select::make('assigned_to')
                        ->label('المسؤول عن التنفيذ')
                        ->options(fn () => User::query()->staff()->active()->get(['id', 'name'])->mapWithKeys(
                            fn (User $user) => [$user->id => static::userLabel($user)]
                        )->toArray())
                        ->searchable()
                        ->preload()
                        ->nullable(),

                    F\Select::make('department_id')
                        ->label('القسم')
                        ->options(fn () => Department::query()->active()->get(['id', 'name'])->mapWithKeys(
                            fn (Department $department) => [$department->id => static::departmentLabel($department)]
                        )->toArray())
                        ->searchable()
                        ->nullable(),

                    F\Select::make('priority')
                        ->label('الأولوية')
                        ->options(Task::priorityOptions())
                        ->default('normal')
                        ->required(),

                    F\Select::make('status')
                        ->label('الحالة')
                        ->options(Task::statusOptions())
                        ->default('pending')
                        ->required(),

                    F\DatePicker::make('due_date')
                        ->label('تاريخ الاستحقاق')
                        ->nullable(),

                    F\Select::make('related_ticket_id')
                        ->label('تذكرة مرتبطة (اختياري)')
                        ->searchable()
                        ->getSearchResultsUsing(fn (string $search) =>
                            Ticket::query()
                                ->where('ticket_number', 'like', "%{$search}%")
                                ->orWhere('subject', 'like', "%{$search}%")
                                ->limit(20)
                                ->get()
                                ->mapWithKeys(fn (Ticket $t) => [$t->id => static::ticketLabel($t)])
                                ->toArray()
                        )
                        ->getOptionLabelUsing(fn ($value): string => ($ticket = Ticket::find($value))
                            ? static::ticketLabel($ticket)
                            : static::fallbackLabel('تذكرة', $value)
                        )
                        ->nullable(),
                ])
                ->columns(2),
        ];

        if (! empty($operationalLinks)) {
            $components[] = Section::make('روابط تشغيلية (Phase 9B)')
                ->description('اختياري — اربط المهمة بسياقها التشغيلي.')
                ->columns(2)
                ->collapsible()
                ->collapsed()
                ->schema($operationalLinks);
        }

        $components[] = Section::make('ملاحظات')
            ->schema([
                F\Textarea::make('notes')
                    ->label('ملاحظات داخلية')
                    ->rows(3)
                    ->nullable()
                    ->columnSpanFull(),
            ])
            ->collapsible();

        return $schema->components($components);
    }

    public static function table(Table $table): Table
    {
        $cols = static::operationalLinkColumns();

        // الأعمدة الإضافية للربط التشغيلي — مخفية افتراضياً، يمكن إظهارها من toggle.
        $linkColumns = [];

        if ($cols['subscription_id']) {
            $linkColumns[] = Tables\Columns\TextColumn::make('subscription.id')
                ->label('اشتراك')
                ->placeholder('—')
                ->prefix('#')
                ->toggleable(isToggledHiddenByDefault: true);
        }

        if ($cols['compliance_obligation_id']) {
            $linkColumns[] = Tables\Columns\TextColumn::make('complianceObligation.obligation_type')
                ->label('التزام')
                ->placeholder('—')
                ->badge()
                ->color('info')
                ->toggleable(isToggledHiddenByDefault: true);
        }

        if ($cols['obligation_period_id']) {
            $linkColumns[] = Tables\Columns\TextColumn::make('obligationPeriod.period_label')
                ->label('فترة')
                ->placeholder('—')
                ->toggleable(isToggledHiddenByDefault: true);
        }

        if ($cols['tax_return_request_id']) {
            $linkColumns[] = Tables\Columns\TextColumn::make('taxReturnRequest.public_id')
                ->label('إقرار')
                ->placeholder('—')
                ->copyable()
                ->toggleable(isToggledHiddenByDefault: true);
        }

        // الفلاتر التشغيلية المحروسة (Phase 9B).
        $linkFilters = [];

        if ($cols['subscription_id']) {
            $linkFilters[] = Tables\Filters\SelectFilter::make('subscription_id')
                ->label('الاشتراك')
                ->relationship('subscription', 'id')
                ->getOptionLabelFromRecordUsing(fn (Subscription $record): string => static::subscriptionLabel($record))
                ->searchable()
                ->preload();
        }

        if ($cols['compliance_obligation_id']) {
            $linkFilters[] = Tables\Filters\SelectFilter::make('compliance_obligation_id')
                ->label('الالتزام')
                ->relationship('complianceObligation', 'obligation_type')
                ->getOptionLabelFromRecordUsing(fn (ComplianceObligation $record): string => static::complianceObligationLabel($record))
                ->searchable()
                ->preload();
        }

        if ($cols['obligation_period_id']) {
            $linkFilters[] = Tables\Filters\SelectFilter::make('obligation_period_id')
                ->label('فترة الالتزام')
                ->relationship('obligationPeriod', 'period_label')
                ->getOptionLabelFromRecordUsing(fn (ObligationPeriod $record): string => static::obligationPeriodLabel($record))
                ->searchable()
                ->preload();
        }

        if ($cols['tax_return_request_id']) {
            $linkFilters[] = Tables\Filters\SelectFilter::make('tax_return_request_id')
                ->label('الإقرار الضريبي')
                ->relationship('taxReturnRequest', 'public_id')
                ->getOptionLabelFromRecordUsing(fn (TaxReturnRequest $record): string => static::taxReturnRequestLabel($record))
                ->searchable()
                ->preload();
        }

        return $table
            ->defaultSort('created_at', 'desc')
            ->columns(array_merge([
                Tables\Columns\TextColumn::make('title')
                    ->label('المهمة')
                    ->searchable()
                    ->limit(45)
                    ->tooltip(fn ($state) => $state)
                    ->weight('semibold')
                    ->description(fn (Task $record) => $record->department?->name ?? ''),

                Tables\Columns\TextColumn::make('assignee.name')
                    ->label('المسؤول')
                    ->searchable()
                    ->placeholder('غير محدد')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => Task::statusOptions()[$state] ?? $state)
                    ->color(fn (string $state): string => Task::statusColors()[$state] ?? 'gray'),

                Tables\Columns\TextColumn::make('priority')
                    ->label('الأولوية')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => Task::priorityOptions()[$state] ?? $state)
                    ->color(fn (string $state): string => Task::priorityColors()[$state] ?? 'gray'),

                Tables\Columns\TextColumn::make('due_date')
                    ->label('الاستحقاق')
                    ->date('Y-m-d')
                    ->description(fn (Task $record) => $record->is_overdue ? '⚠️ متأخرة' : '')
                    ->color(fn (Task $record) => $record->is_overdue ? 'danger' : 'gray')
                    ->placeholder('-')
                    ->sortable(),

                Tables\Columns\TextColumn::make('creator.name')
                    ->label('أنشأها')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('completed_at')
                    ->label('تاريخ الإنجاز')
                    ->dateTime('Y-m-d')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('Y-m-d')
                    ->description(fn (Task $record) => $record->created_at?->diffForHumans() ?? '')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ], $linkColumns))
            ->filters(array_merge([
                Tables\Filters\SelectFilter::make('status')
                    ->label('الحالة')
                    ->options(Task::statusOptions()),

                Tables\Filters\SelectFilter::make('priority')
                    ->label('الأولوية')
                    ->options(Task::priorityOptions()),

                Tables\Filters\SelectFilter::make('assigned_to')
                    ->label('المسؤول')
                    ->relationship('assignee', 'name')
                    ->getOptionLabelFromRecordUsing(fn (User $record): string => static::userLabel($record)),

                Tables\Filters\SelectFilter::make('department_id')
                    ->label('القسم')
                    ->relationship('department', 'name')
                    ->getOptionLabelFromRecordUsing(fn (Department $record): string => static::departmentLabel($record)),

                Tables\Filters\Filter::make('overdue')
                    ->label('متأخرة فقط')
                    ->query(fn (Builder $query) => $query->overdue()),

                Tables\Filters\Filter::make('due_date')
                    ->form([
                        F\DatePicker::make('due_from')->label('استحقاق من'),
                        F\DatePicker::make('due_until')->label('استحقاق إلى'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['due_from'] ?? null, fn ($q, $d) => $q->whereDate('due_date', '>=', $d))
                            ->when($data['due_until'] ?? null, fn ($q, $d) => $q->whereDate('due_date', '<=', $d));
                    }),
            ], $linkFilters))
            ->actions([
                ActionGroup::make([
                    Action::make('edit')
                        ->label('تعديل')
                        ->icon('heroicon-m-pencil-square')
                        ->color('gray')
                        ->url(fn (Task $record) => static::getUrl('edit', ['record' => $record])),

                    Action::make('markDone')
                        ->label('تحديد كمكتملة')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->visible(fn (Task $record) => ! in_array($record->status, ['done', 'cancelled']))
                        ->requiresConfirmation()
                        ->action(function (Task $record) {
                            $record->update(['status' => 'done', 'completed_at' => now()]);
                            Notification::make()->title('تم إغلاق المهمة')->success()->send();
                        }),

                    Action::make('markInProgress')
                        ->label('تحويل لقيد التنفيذ')
                        ->icon('heroicon-o-play-circle')
                        ->color('info')
                        ->visible(fn (Task $record) => $record->status === 'pending')
                        ->action(function (Task $record) {
                            $record->update(['status' => 'in_progress']);
                            Notification::make()->title('تم تحديث الحالة')->success()->send();
                        }),

                    Action::make('cancel')
                        ->label('إلغاء المهمة')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->visible(fn (Task $record) => ! in_array($record->status, ['done', 'cancelled']))
                        ->requiresConfirmation()
                        ->action(function (Task $record) {
                            $record->update(['status' => 'cancelled']);
                            Notification::make()->title('تم إلغاء المهمة')->warning()->send();
                        }),

                    Action::make('delete')
                        ->label('حذف')
                        ->icon('heroicon-m-trash')
                        ->color('danger')
                        ->visible(fn () => static::isAdmin())
                        ->requiresConfirmation()
                        ->action(fn (Task $record) => $record->delete()),
                ])
                    ->tooltip('الإجراءات')
                    ->icon('heroicon-m-ellipsis-vertical'),
            ])
            ->emptyStateHeading('لا توجد مهام بعد')
            ->emptyStateDescription('ابدأ بإضافة مهام جديدة لفريقك من زر "إنشاء" أعلى الصفحة.');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListTasks::route('/'),
            'create' => Pages\CreateTask::route('/create'),
            'edit'   => Pages\EditTask::route('/{record}/edit'),
        ];
    }
}
