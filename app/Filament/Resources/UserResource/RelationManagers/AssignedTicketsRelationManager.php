<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use App\Filament\Resources\TicketResource;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

// ✅ الصحيح: استيراد Actions من Tables\Actions داخل RelationManagers
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;

use Filament\Forms\Components as F;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class AssignedTicketsRelationManager extends RelationManager
{
    protected static string $relationship = 'assignedTickets';
    protected static ?string $title       = 'التذاكر المسندة للموظف';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('ticket_number')
            ->recordUrl(fn (Model $record): string => TicketResource::getUrl('edit', ['record' => $record]))
            ->defaultSort('created_at', 'desc')

            ->columns([
                Tables\Columns\TextColumn::make('ticket_number')
                    ->label('رقم التذكرة')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('تم النسخ')
                    ->weight('bold')
                    ->color('primary'),

                Tables\Columns\TextColumn::make('company.name')
                    ->label('الشركة')
                    ->placeholder('-')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('subject')
                    ->label('العنوان')
                    ->limit(35)
                    ->tooltip(fn ($state) => $state)
                    ->searchable(),

                Tables\Columns\TextColumn::make('priority')
                    ->label('الأولوية')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'high'   => 'عالي',
                        'medium' => 'متوسط',
                        'low'    => 'منخفض',
                        default  => $state ?? '-',
                    })
                    ->color(fn (?string $state) => match ($state) {
                        'high'   => 'danger',
                        'medium' => 'warning',
                        'low'    => 'success',
                        default  => 'gray',
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'open'             => 'مفتوحة',
                        'in_progress'      => 'قيد التنفيذ',
                        'pending_customer' => 'بانتظار العميل',
                        'pending_agent'    => 'بانتظار الموظف',
                        'closed'           => 'مغلقة',
                        default            => $state ?? '-',
                    })
                    ->color(fn (?string $state) => match ($state) {
                        'open', 'pending_agent' => 'warning',
                        'in_progress'           => 'info',
                        'pending_customer'      => 'primary',
                        'closed'                => 'success',
                        default                 => 'gray',
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('Y-m-d h:i A')
                    ->description(fn (Model $record) => $record->created_at?->diffForHumans() ?? '')
                    ->sortable(),
            ])

            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'open'             => 'مفتوحة',
                        'in_progress'      => 'قيد التنفيذ',
                        'pending_customer' => 'بانتظار العميل',
                        'pending_agent'    => 'بانتظار الموظف',
                        'closed'           => 'مغلقة',
                    ]),

                Tables\Filters\SelectFilter::make('priority')
                    ->label('الأولوية')
                    ->options([
                        'high'   => 'عالي',
                        'medium' => 'متوسط',
                        'low'    => 'منخفض',
                    ]),

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        F\DatePicker::make('created_from')->label('من تاريخ'),
                        F\DatePicker::make('created_until')->label('إلى تاريخ'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'] ?? null,
                                fn (Builder $query, $date) => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'] ?? null,
                                fn (Builder $query, $date) => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])

            ->recordActions([
                ActionGroup::make([
                    Action::make('openTicket')
                        ->label('فتح التذكرة')
                        ->icon('heroicon-m-arrow-top-right-on-square')
                        ->url(fn (Model $record) => TicketResource::getUrl('edit', ['record' => $record]))
                        ->openUrlInNewTab()
                        ->color('gray'),
                ])
                    ->tooltip('الإجراءات')
                    ->icon('heroicon-m-ellipsis-vertical'),
            ])

            ->emptyStateHeading('لا توجد تذاكر مسندة')
            ->emptyStateDescription('عند إسناد تذاكر لهذا الموظف ستظهر هنا.')
            ->emptyStateIcon('heroicon-o-ticket');
    }
}