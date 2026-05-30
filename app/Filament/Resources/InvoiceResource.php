<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvoiceResource\Pages;
use App\Models\Invoice;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Schema as DbSchema;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-currency-dollar';
    protected static ?string $navigationLabel = 'الفواتير';
    protected static ?string $modelLabel = 'فاتورة';
    protected static ?string $pluralLabel = 'الفواتير';
    protected static string|\UnitEnum|null $navigationGroup = 'المالية';
    protected static ?int $navigationSort = 10;

    public static function canViewAny(): bool
    {
        return DbSchema::hasTable('invoices') && static::userCanManage(auth()->user());
    }

    public static function canCreate(): bool
    {
        return DbSchema::hasTable('invoices') && static::userCanManage(auth()->user());
    }

    public static function canEdit($record): bool
    {
        return static::userCanManage(auth()->user());
    }

    public static function canDelete($record): bool
    {
        return static::userCanManage(auth()->user());
    }

    public static function shouldRegisterNavigation(): bool
    {
        return DbSchema::hasTable('invoices') && static::userCanManage(auth()->user());
    }

    protected static function userCanManage(?\App\Models\User $user): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->is_admin) {
            return true;
        }

        if (method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['manager', 'accountant'])) {
            return true;
        }

        return in_array(strtolower((string) ($user->role ?? '')), ['manager', 'accountant'], true);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('بيانات الفاتورة')
                    ->columns(2)
                    ->components([
                        Select::make('company_id')
                            ->label('الشركة')
                            ->relationship('company', 'name')
                            ->searchable()
                            ->required(),
                        Select::make('status')
                            ->label('الحالة')
                            ->options([
                                Invoice::STATUS_DRAFT     => 'مسودة',
                                Invoice::STATUS_ISSUED    => 'صادرة',
                                Invoice::STATUS_PAID      => 'مدفوعة',
                                Invoice::STATUS_OVERDUE   => 'متأخرة',
                                Invoice::STATUS_CANCELLED => 'ملغاة',
                            ])
                            ->default(Invoice::STATUS_DRAFT)
                            ->required(),
                        DatePicker::make('issue_date')->label('تاريخ الإصدار'),
                        DatePicker::make('due_date')->label('تاريخ الاستحقاق'),
                        TextInput::make('subtotal')->label('قبل الضريبة')->numeric()->default(0)->required(),
                        TextInput::make('vat_amount')->label('الضريبة')->numeric()->default(0)->required(),
                        TextInput::make('total')->label('الإجمالي')->numeric()->default(0)->required(),
                        TextInput::make('currency')->label('العملة')->default('SAR')->maxLength(3),
                        Textarea::make('notes')->label('ملاحظات')->rows(3)->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('invoice_number')->label('رقم الفاتورة')->searchable()->sortable(),
                TextColumn::make('company.name')->label('الشركة')->searchable()->limit(40),
                BadgeColumn::make('status')
                    ->label('الحالة')
                    ->colors([
                        'gray'    => Invoice::STATUS_DRAFT,
                        'primary' => Invoice::STATUS_ISSUED,
                        'success' => Invoice::STATUS_PAID,
                        'warning' => Invoice::STATUS_OVERDUE,
                        'danger'  => Invoice::STATUS_CANCELLED,
                    ])
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        Invoice::STATUS_DRAFT     => 'مسودة',
                        Invoice::STATUS_ISSUED    => 'صادرة',
                        Invoice::STATUS_PAID      => 'مدفوعة',
                        Invoice::STATUS_OVERDUE   => 'متأخرة',
                        Invoice::STATUS_CANCELLED => 'ملغاة',
                        default                   => $state,
                    }),
                TextColumn::make('total')->label('الإجمالي')->money('SAR', true),
                TextColumn::make('due_date')->label('استحقاق')->date()->sortable(),
                TextColumn::make('created_at')->label('أنشئت')->dateTime()->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->options([
                    Invoice::STATUS_DRAFT     => 'مسودة',
                    Invoice::STATUS_ISSUED    => 'صادرة',
                    Invoice::STATUS_PAID      => 'مدفوعة',
                    Invoice::STATUS_OVERDUE   => 'متأخرة',
                    Invoice::STATUS_CANCELLED => 'ملغاة',
                ]),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListInvoices::route('/'),
            'create' => Pages\CreateInvoice::route('/create'),
            'edit'   => Pages\EditInvoice::route('/{record}/edit'),
        ];
    }
}
