<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\ServiceRequest;
use Filament\Actions\Action;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class LatestOrdersWidget extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 2;

    protected static ?string $heading = 'أحدث طلبات الخدمات';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                ServiceRequest::query()
                    ->with(['user', 'service'])   // ✅ مهم لتفادي N+1
                    ->latest()
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('#')
                    ->sortable(),

                Tables\Columns\TextColumn::make('customer_display')
                    ->label('العميل')
                    ->getStateUsing(fn (ServiceRequest $record): string =>
                        $record->user?->name ?? (($record->guest_name ?: 'زائر') . ' (زائر)')
                    )
                    ->description(fn (ServiceRequest $record): ?string =>
                        $record->user?->phone ?? ($record->guest_phone ?: null)
                    ),

                Tables\Columns\TextColumn::make('service.title_ar')
                    ->label('الخدمة')
                    ->limit(20),

                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'processing' => 'info',
                        'completed' => 'success',
                        'cancelled', 'rejected' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('منذ')
                    ->since(),
            ])
            ->actions([
                Action::make('edit')
                    ->label('عرض')
                    ->icon('heroicon-m-eye')
                    ->color('gray')
                    ->url(fn (ServiceRequest $record): string =>
                        route('filament.amr7.resources.service-requests.edit', ['record' => $record])
                    ),
            ])
            ->paginated(false);
    }
}
