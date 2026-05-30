<?php

declare(strict_types=1);

namespace App\Filament\Resources\FinancialStatementRequestResource\Pages;

use App\Enums\FinancialStatementStatus;
use App\Filament\Resources\FinancialStatementRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Components as I;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;

class ViewFinancialStatementRequest extends ViewRecord
{
    protected static string $resource = FinancialStatementRequestResource::class;

    public function getTitle(): string
    {
        return 'تفاصيل طلب القوائم المالية';
    }

    public function getBreadcrumb(): string
    {
        return 'عرض الطلب';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()->label('تعديل / معالجة'),
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->record($this->record)
            ->components([
                Section::make('ملخص الطلب')
                    ->columns(2)
                    ->schema([
                        I\TextEntry::make('public_id')
                            ->label('رقم الطلب')
                            ->weight(FontWeight::Bold)
                            ->copyable(),

                        I\TextEntry::make('company_name')
                            ->label('المنشأة'),

                        I\TextEntry::make('user.name')
                            ->label('العميل'),

                        I\TextEntry::make('status')
                            ->label('الحالة')
                            ->badge()
                            ->color(fn (?string $state): string => match ($state) {
                                'new'                                          => 'gray',
                                'waiting_docs', 'files_uploaded'               => 'warning',
                                'in_review', 'under_review'                    => 'primary',
                                'client_approval'                              => 'info',
                                'moc_approval', 'moci_approval', 'moci_pending'=> 'info',
                                'internal_approved', 'moci_approved',
                                'approved', 'completed'                        => 'success',
                                'rejected', 'cancelled'                        => 'danger',
                                'closed'                                       => 'gray',
                                default                                        => 'gray',
                            })
                            ->formatStateUsing(
                                fn ($state) => FinancialStatementStatus::tryFrom((string) $state)?->label() ?? (string) $state
                            ),

                        I\TextEntry::make('created_at')
                            ->label('تاريخ الإنشاء')
                            ->dateTime('d/m/Y h:i A'),
                    ]),

                Section::make('الملاحظات')
                    ->schema([
                        I\TextEntry::make('client_notes')
                            ->label('ملاحظات العميل')
                            ->placeholder('لا توجد ملاحظات')
                            ->columnSpanFull(),

                        I\TextEntry::make('admin_notes')
                            ->label('ملاحظات الإدارة')
                            ->placeholder('لا توجد ملاحظات')
                            ->columnSpanFull(),
                    ]),

                Section::make('الملفات والمرفقات')
                    ->schema([
                        I\RepeatableEntry::make('files')
                            ->label('')
                            ->schema([
                                I\TextEntry::make('original_name')
                                    ->label('اسم الملف')
                                    ->icon('heroicon-m-document-text')
                                    ->color('primary')
                                    ->url(fn ($record) => $record?->url)
                                    ->openUrlInNewTab(),

                                I\TextEntry::make('file_key')
                                    ->label('النوع')
                                    ->badge(),

                                I\IconEntry::make('is_final')
                                    ->label('نهائي؟')
                                    ->boolean(),
                            ])
                            ->grid(2)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}