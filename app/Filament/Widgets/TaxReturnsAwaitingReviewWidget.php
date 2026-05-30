<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\TaxReturnRequest;
use Filament\Actions\Action;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Schema as DbSchema;

/**
 * Phase 9C-5 — Tax returns awaiting admin or client action.
 *
 * يُخفى إذا جدول tax_return_requests غير موجود.
 */
class TaxReturnsAwaitingReviewWidget extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 8;

    protected static ?string $heading = 'إقرارات ضريبية بانتظار المراجعة';

    public static function canView(): bool
    {
        return DbSchema::hasTable('tax_return_requests');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                TaxReturnRequest::query()
                    ->with(['company'])
                    ->whereIn('status', ['files_pending', 'under_review', 'client_approval'])
                    ->orderBy('updated_at', 'desc')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('public_id')
                    ->label('الرقم')
                    ->placeholder('—')
                    ->copyable(),

                Tables\Columns\TextColumn::make('company.name')
                    ->label('الشركة')
                    ->searchable(),

                Tables\Columns\TextColumn::make('kind')
                    ->label('النوع')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'vat'         => 'القيمة المضافة',
                        'zakat'       => 'زكاة',
                        'withholding' => 'استقطاع',
                        'other'       => 'أخرى',
                        default       => $state,
                    })
                    ->color('info'),

                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'files_pending'   => 'بانتظار الملفات',
                        'under_review'    => 'قيد المراجعة',
                        'client_approval' => 'بانتظار اعتماد العميل',
                        default           => $state,
                    })
                    ->color(fn ($state) => match ($state) {
                        'files_pending'   => 'info',
                        'under_review'    => 'warning',
                        'client_approval' => 'success',
                        default           => 'gray',
                    }),

                Tables\Columns\TextColumn::make('fiscal_period_end')
                    ->label('نهاية الفترة')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('آخر تحديث')
                    ->since(),
            ])
            ->actions([
                Action::make('view')
                    ->label('فتح')
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->color('gray')
                    ->url(fn (TaxReturnRequest $record): string =>
                        route('filament.amr7.resources.tax-return-requests.edit', ['record' => $record])
                    ),
            ])
            ->paginated(false)
            ->emptyStateHeading('لا توجد إقرارات بانتظار المراجعة');
    }
}
