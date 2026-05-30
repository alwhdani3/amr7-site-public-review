<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Subscription;
use Filament\Actions\Action;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Schema as DbSchema;

/**
 * Phase 9C-5 — Subscriptions expiring within 30 days.
 *
 * يُخفى الكامل إذا جدول subscriptions غير موجود (نظرياً موجود قديم).
 */
class ExpiringSubscriptionsWidget extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 6;

    protected static ?string $heading = 'اشتراكات قريبة الانتهاء (30 يوم)';

    public static function canView(): bool
    {
        return DbSchema::hasTable('subscriptions');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Subscription::query()
                    ->with(['company', 'package'])
                    ->whereBetween('expires_at', [now(), now()->addDays(30)])
                    ->when(
                        DbSchema::hasColumn('subscriptions', 'status'),
                        fn ($q) => $q->where('status', 'active')
                    )
                    ->orderBy('expires_at', 'asc')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('company.name')
                    ->label('الشركة')
                    ->searchable(),

                Tables\Columns\TextColumn::make('package.name')
                    ->label('الباقة')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('expires_at')
                    ->label('تاريخ الانتهاء')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('days_remaining')
                    ->label('الأيام المتبقية')
                    ->getStateUsing(function (Subscription $record): int {
                        return max(0, (int) now()->startOfDay()->diffInDays($record->expires_at?->startOfDay() ?? now(), false));
                    })
                    ->badge()
                    ->color(fn ($state) => match (true) {
                        $state <= 7  => 'danger',
                        $state <= 14 => 'warning',
                        default      => 'info',
                    })
                    ->suffix(' يوم'),

                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'active'   => 'success',
                        'pending'  => 'warning',
                        'expired'  => 'danger',
                        'canceled' => 'gray',
                        default    => 'gray',
                    }),
            ])
            ->actions([
                Action::make('view')
                    ->label('فتح')
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->color('gray')
                    ->url(fn (Subscription $record): string =>
                        route('filament.amr7.resources.subscriptions.edit', ['record' => $record])
                    ),
            ])
            ->paginated(false)
            ->emptyStateHeading('لا توجد اشتراكات قريبة الانتهاء');
    }
}
