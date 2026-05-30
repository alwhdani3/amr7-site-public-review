<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Models\Payment;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
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

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'المدفوعات';
    protected static ?string $modelLabel = 'دفعة';
    protected static ?string $pluralLabel = 'المدفوعات';
    protected static string|\UnitEnum|null $navigationGroup = 'المالية';
    protected static ?int $navigationSort = 20;

    public static function canViewAny(): bool
    {
        return DbSchema::hasTable('payments') && static::userCanManage(auth()->user());
    }

    public static function canCreate(): bool
    {
        return DbSchema::hasTable('payments') && static::userCanManage(auth()->user());
    }

    public static function shouldRegisterNavigation(): bool
    {
        return DbSchema::hasTable('payments') && static::userCanManage(auth()->user());
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
                Section::make('بيانات الدفعة')
                    ->columns(2)
                    ->components([
                        Select::make('invoice_id')
                            ->label('الفاتورة')
                            ->relationship('invoice', 'invoice_number')
                            ->searchable()
                            ->required(),
                        Select::make('status')
                            ->label('الحالة')
                            ->options([
                                Payment::STATUS_PENDING  => 'قيد التحصيل',
                                Payment::STATUS_PAID     => 'مدفوعة',
                                Payment::STATUS_MANUAL   => 'يدوي/تحويل بنكي',
                                Payment::STATUS_FAILED   => 'فشلت',
                                Payment::STATUS_REFUNDED => 'مسترجعة',
                            ])
                            ->default(Payment::STATUS_PENDING)
                            ->required(),
                        TextInput::make('amount')->label('المبلغ')->numeric()->required(),
                        TextInput::make('currency')->label('العملة')->default('SAR')->maxLength(3),
                        TextInput::make('provider')->label('مزود الدفع')->placeholder('manual / moyasar / hyperpay'),
                        TextInput::make('provider_reference')->label('مرجع المزود')->maxLength(191),
                        DateTimePicker::make('paid_at')->label('تاريخ الدفع'),
                        Textarea::make('notes')->label('ملاحظات')->rows(3)->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('invoice.invoice_number')->label('الفاتورة')->searchable()->sortable(),
                TextColumn::make('amount')->label('المبلغ')->money('SAR', true),
                BadgeColumn::make('status')
                    ->label('الحالة')
                    ->colors([
                        'warning' => Payment::STATUS_PENDING,
                        'success' => Payment::STATUS_PAID,
                        'primary' => Payment::STATUS_MANUAL,
                        'danger'  => Payment::STATUS_FAILED,
                        'gray'    => Payment::STATUS_REFUNDED,
                    ])
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        Payment::STATUS_PENDING  => 'قيد التحصيل',
                        Payment::STATUS_PAID     => 'مدفوعة',
                        Payment::STATUS_MANUAL   => 'يدوي',
                        Payment::STATUS_FAILED   => 'فشلت',
                        Payment::STATUS_REFUNDED => 'مسترجعة',
                        default                  => $state,
                    }),
                TextColumn::make('provider')->label('المزود'),
                TextColumn::make('paid_at')->label('تاريخ الدفع')->dateTime()->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->options([
                    Payment::STATUS_PENDING  => 'قيد التحصيل',
                    Payment::STATUS_PAID     => 'مدفوعة',
                    Payment::STATUS_MANUAL   => 'يدوي',
                    Payment::STATUS_FAILED   => 'فشلت',
                    Payment::STATUS_REFUNDED => 'مسترجعة',
                ]),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPayments::route('/'),
            'create' => Pages\CreatePayment::route('/create'),
            'edit'   => Pages\EditPayment::route('/{record}/edit'),
        ];
    }
}
