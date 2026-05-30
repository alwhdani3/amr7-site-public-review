<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Ticket;
use Carbon\Carbon;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Support\Enums\FontWeight;

class SlaAlerts extends BaseWidget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = '⚠️ تنبيهات تجاوز SLA (الأكثر خطورة)';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Ticket::query()
                    ->where('status', '!=', 'closed')
                    ->whereNotNull('sla_deadline')
                    ->where('sla_deadline', '<=', now()->addHour())
                    ->orderBy('sla_deadline', 'asc')
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('ticket_number')
                    ->label('رقم التذكرة')
                    ->weight(FontWeight::Bold)
                    ->icon('heroicon-m-ticket'),

                Tables\Columns\TextColumn::make('subject')
                    ->label('العنوان')
                    ->limit(50)
                    ->tooltip(fn (Ticket $record): string => (string) $record->subject),

                Tables\Columns\TextColumn::make('sla_deadline')
                    ->label('حالة الوقت')
                    ->dateTime('d/m/Y h:i A')
                    ->description(function (Ticket $record): string {
                        $deadline = $record->sla_deadline
                            ? Carbon::parse($record->sla_deadline)
                            : null;

                        if (! $deadline) {
                            return '—';
                        }

                        if ($deadline->isPast()) {
                            return 'تجاوز الوقت: ' . $deadline->diffForHumans(now(), [
                                'parts' => 2,
                                'short' => true,
                                'syntax' => Carbon::DIFF_ABSOLUTE,
                            ]);
                        }

                        return 'متبقي: ' . now()->diffForHumans($deadline, [
                            'parts' => 2,
                            'short' => true,
                            'syntax' => Carbon::DIFF_ABSOLUTE,
                        ]);
                    })
                    ->color('danger')
                    ->badge()
                    ->icon('heroicon-m-clock'),
            ])
            ->recordUrl(fn (Ticket $record): string =>
                route('filament.amr7.resources.tickets.edit', ['record' => $record])
            )
            ->paginated(false)
            ->emptyStateHeading('مرحى! لا توجد تجاوزات SLA')
            ->emptyStateIcon('heroicon-o-check-badge');
    }
}
