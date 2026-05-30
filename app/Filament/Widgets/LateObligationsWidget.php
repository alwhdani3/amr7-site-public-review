<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\ObligationPeriod;
use Filament\Actions\Action;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Schema as DbSchema;

/**
 * Phase 9C-5 — Obligation periods that are late (past due_date and unfiled).
 *
 * يُخفى إذا جدول obligation_periods غير موجود.
 */
class LateObligationsWidget extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 7;

    protected static ?string $heading = 'فترات الالتزام المتأخرة';

    public static function canView(): bool
    {
        return DbSchema::hasTable('obligation_periods');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                ObligationPeriod::query()
                    ->with(['company', 'obligation'])
                    ->where(function ($q) {
                        $q->where('status', 'late')
                          ->orWhere(function ($q2) {
                              $q2->whereIn('status', [
                                  'open', 'client_uploading', 'files_uploaded', 'under_review', 'client_approval',
                              ])->where('due_date', '<', now()->toDateString());
                          });
                    })
                    ->orderBy('due_date', 'asc')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('company.name')
                    ->label('الشركة')
                    ->searchable(),

                Tables\Columns\TextColumn::make('period_label')
                    ->label('الفترة')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('due_date')
                    ->label('تاريخ الاستحقاق')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('days_late')
                    ->label('أيام التأخير')
                    ->getStateUsing(function (ObligationPeriod $record): int {
                        if (! $record->due_date) {
                            return 0;
                        }
                        return max(0, (int) $record->due_date->startOfDay()->diffInDays(now()->startOfDay(), false));
                    })
                    ->badge()
                    ->color(fn ($state) => match (true) {
                        $state >= 30 => 'danger',
                        $state >= 7  => 'warning',
                        default      => 'info',
                    })
                    ->suffix(' يوم'),

                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'late'             => 'متأخر',
                        'open'             => 'مفتوح',
                        'client_uploading' => 'بانتظار رفع العميل',
                        'files_uploaded'   => 'تم رفع الملفات',
                        'under_review'     => 'قيد المراجعة',
                        'client_approval'  => 'بانتظار اعتماد العميل',
                        default            => $state,
                    })
                    ->color('danger'),
            ])
            ->actions([
                Action::make('view')
                    ->label('فتح')
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->color('gray')
                    ->url(fn (ObligationPeriod $record): string =>
                        route('filament.amr7.resources.obligation-periods.edit', ['record' => $record])
                    ),
            ])
            ->paginated(false)
            ->emptyStateHeading('لا توجد فترات متأخرة');
    }
}
