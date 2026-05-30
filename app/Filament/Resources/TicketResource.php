<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TicketResource\Pages;
use App\Filament\Resources\TicketResource\RelationManagers\AllAttachmentsRelationManager;
use App\Models\Department;
use App\Models\Ticket;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Resources\Resource;
use Filament\Forms\Components as F;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Tables\Table;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Support\Enums\FontWeight;

class TicketResource extends Resource
{
    protected static ?string $model = Ticket::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-ticket';
    protected static string|\UnitEnum|null $navigationGroup = 'الوثائق والتذاكر';
    protected static ?string $navigationLabel = 'التذاكر';
    protected static ?string $modelLabel = 'تذكرة';
    protected static ?string $pluralModelLabel = 'التذاكر';

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
        $user = auth()->user();
        $isAdmin = auth()->check() && $user?->role === 'admin';

        return $schema->components([
            // ✅ Section الصحيح في Filament v5
            Section::make('تفاصيل التذكرة')
                ->description('بيانات الطلب الأساسية')
                ->schema([
                    F\Select::make('company_id')
                        ->label('المنشأة')
                        ->relationship('company', 'name')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->visible(fn () => (bool) $isAdmin),

                    // لو مو Admin نخفي الحقل ونعبيه تلقائيًا
                    F\Hidden::make('company_id')
                        ->visible(fn () => ! $isAdmin)
                        ->default(fn () => $user?->companies()->first()?->id),

                    F\TextInput::make('subject')
                        ->label('عنوان الطلب')
                        ->required()
                        ->maxLength(255),

                    F\Select::make('department_id')
                        ->label('القسم')
                        ->options(fn () => Department::query()->pluck('name', 'id')->all())
                        ->searchable()
                        ->required(),

                    F\Select::make('priority')
                        ->label('الأولوية')
                        ->options([
                            'low'    => 'منخفض',
                            'medium' => 'متوسط',
                            'high'   => 'عالي',
                        ])
                        ->default('medium')
                        ->required(),

                    F\Textarea::make('description')
                        ->label('الوصف التفصيلي')
                        ->rows(6)
                        ->required()
                        ->columnSpanFull(),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                Split::make([
                    Stack::make([
                        TextColumn::make('subject')
                            ->label('التذكرة')
                            ->weight(FontWeight::Bold)
                            ->wrap()
                            ->searchable(),

                        TextColumn::make('ticket_number')
                            ->label('رقم')
                            ->badge()
                            ->copyable()
                            ->color('gray')
                            ->searchable(),

                        TextColumn::make('company.name')
                            ->label('المنشأة')
                            ->toggleable(isToggledHiddenByDefault: true)
                            ->searchable(),
                    ])->space(1),

                    Stack::make([
                        TextColumn::make('status')
                            ->label('الحالة')
                            ->badge()
                            ->formatStateUsing(fn (?string $state) => match ($state) {
                                'open'             => 'مفتوحة',
                                'in_progress'      => 'قيد المعالجة',
                                'pending_customer' => 'بانتظار العميل',
                                'pending_agent'    => 'بانتظار الموظف',
                                'closed'           => 'مغلقة',
                                default            => $state ?? '—',
                            })
                            ->color(fn (string $state) => match ($state) {
                                'open'             => 'warning',
                                'in_progress'      => 'info',
                                'pending_customer' => 'primary',
                                'pending_agent'    => 'danger',
                                'closed'           => 'success',
                                default            => 'gray',
                            }),

                        TextColumn::make('department.name')
                            ->label('القسم')
                            ->badge()
                            ->toggleable(isToggledHiddenByDefault: true),
                    ])->space(1),

                    Stack::make([
                        TextColumn::make('assignedUser.name')
                            ->label('المسؤول')
                            ->placeholder('غير مسند')
                            ->icon('heroicon-m-user'),

                        TextColumn::make('created_at')
                            ->label('تاريخ الإنشاء')
                            ->tooltip(fn ($record) => $record->created_at?->format('Y-m-d H:i') ?? '')
                            ->sortable(),

                        TextColumn::make('sla_deadline')
                            ->label('موعد الرد (SLA)')
                            ->since()
                            ->badge()
                            ->color(fn ($record) => blank($record->sla_deadline)
                                ? 'gray'
                                : ($record->sla_deadline->isPast()
                                    ? 'danger'
                                    : ($record->sla_deadline->diffInMinutes(now()) <= 120 ? 'warning' : 'success')
                                )
                            )
                            ->tooltip(fn ($record) => $record->sla_deadline?->format('Y-m-d H:i') ?? '')
                            ->sortable()
                            ->toggleable(isToggledHiddenByDefault: true),
                    ])->space(1),
                ])->from('md'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'open'             => 'مفتوحة',
                        'in_progress'      => 'قيد المعالجة',
                        'pending_customer' => 'بانتظار العميل',
                        'pending_agent'    => 'بانتظار الموظف',
                        'closed'           => 'مغلقة',
                    ]),

                SelectFilter::make('company_id')
                    ->label('المنشأة')
                    ->relationship('company', 'name')
                    ->visible(fn () => auth()->check() && auth()->user()->role === 'admin'),
            ], layout: FiltersLayout::AboveContent)
            ->recordActions([
                \Filament\Actions\ViewAction::make()->label('عرض'),
                \Filament\Actions\EditAction::make()->label('تعديل'),

                Action::make('assign')
                    ->label('إسناد')
                    ->icon('heroicon-o-user-plus')
                    ->color('info')
                    ->form([
                        F\Select::make('assigned_to')
                            ->label('الموظف')
                            // ✅ عدلت roles لتناسب نظامك (admin/manager/employee)
                            ->options(
                                User::query()
                                    ->whereIn('role', ['admin', 'manager', 'employee'])
                                    ->pluck('name', 'id')
                                    ->all()
                            )
                            ->searchable()
                            ->required(),
                    ])
                    ->action(function (Ticket $record, array $data) {
                        $record->update([
                            'assigned_to' => $data['assigned_to'],
                            'assigned_by' => auth()->id(),
                            'assigned_at' => now(),
                            'status'      => 'in_progress',
                        ]);
                    })
                    ->visible(fn () => auth()->check() && auth()->user()->role === 'admin'),

                Action::make('close')
                    ->label('إغلاق')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(fn (Ticket $record) => $record->update(['status' => 'closed']))
                    ->visible(fn (Ticket $record) => $record->status !== 'closed'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn () => static::canDeleteAny()),
                ]),
            ])
            ->emptyStateHeading('لا توجد تذاكر بعد')
            ->emptyStateDescription('عند إنشاء العميل تذكرة دعم ستظهر هنا.');
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\Resources\TicketResource\RelationManagers\RepliesRelationManager::class,
            AllAttachmentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListTickets::route('/'),
            'create' => Pages\CreateTicket::route('/create'),
            'view'   => Pages\ViewTicket::route('/{record}'),
            'edit'   => Pages\EditTicket::route('/{record}/edit'),
        ];
    }
}
