<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QuoteResource\Pages;
use App\Models\Quote;
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

class QuoteResource extends Resource
{
    protected static ?string $model = Quote::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'عروض الأسعار';
    protected static ?string $modelLabel = 'عرض سعر';
    protected static ?string $pluralLabel = 'عروض الأسعار';
    protected static string|\UnitEnum|null $navigationGroup = 'المالية';
    protected static ?int $navigationSort = 5;

    public static function canViewAny(): bool
    {
        return DbSchema::hasTable('quotes') && static::userCanManage(auth()->user());
    }

    public static function canCreate(): bool
    {
        return DbSchema::hasTable('quotes') && static::userCanManage(auth()->user());
    }

    public static function shouldRegisterNavigation(): bool
    {
        return DbSchema::hasTable('quotes') && static::userCanManage(auth()->user());
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
                Section::make('بيانات عرض السعر')
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
                                Quote::STATUS_DRAFT    => 'مسودة',
                                Quote::STATUS_SENT     => 'مرسل',
                                Quote::STATUS_ACCEPTED => 'مقبول',
                                Quote::STATUS_REJECTED => 'مرفوض',
                                Quote::STATUS_EXPIRED  => 'منتهي',
                            ])
                            ->default(Quote::STATUS_DRAFT)
                            ->required(),
                        DatePicker::make('valid_until')->label('صالح حتى'),
                        TextInput::make('currency')->label('العملة')->default('SAR')->maxLength(3),
                        TextInput::make('subtotal')->label('قبل الضريبة')->numeric()->default(0)->required(),
                        TextInput::make('vat_amount')->label('الضريبة')->numeric()->default(0)->required(),
                        TextInput::make('total')->label('الإجمالي')->numeric()->default(0)->required(),
                        Textarea::make('notes')->label('ملاحظات')->rows(3)->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('quote_number')->label('رقم العرض')->searchable()->sortable(),
                TextColumn::make('company.name')->label('الشركة')->searchable()->limit(40),
                BadgeColumn::make('status')
                    ->label('الحالة')
                    ->colors([
                        'gray'    => Quote::STATUS_DRAFT,
                        'primary' => Quote::STATUS_SENT,
                        'success' => Quote::STATUS_ACCEPTED,
                        'danger'  => Quote::STATUS_REJECTED,
                        'warning' => Quote::STATUS_EXPIRED,
                    ])
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        Quote::STATUS_DRAFT    => 'مسودة',
                        Quote::STATUS_SENT     => 'مرسل',
                        Quote::STATUS_ACCEPTED => 'مقبول',
                        Quote::STATUS_REJECTED => 'مرفوض',
                        Quote::STATUS_EXPIRED  => 'منتهي',
                        default                => $state,
                    }),
                TextColumn::make('total')->label('الإجمالي')->money('SAR', true),
                TextColumn::make('valid_until')->label('صالح حتى')->date()->sortable(),
                TextColumn::make('created_at')->label('أنشئ')->dateTime()->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->options([
                    Quote::STATUS_DRAFT    => 'مسودة',
                    Quote::STATUS_SENT     => 'مرسل',
                    Quote::STATUS_ACCEPTED => 'مقبول',
                    Quote::STATUS_REJECTED => 'مرفوض',
                    Quote::STATUS_EXPIRED  => 'منتهي',
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
            'index'  => Pages\ListQuotes::route('/'),
            'create' => Pages\CreateQuote::route('/create'),
            'edit'   => Pages\EditQuote::route('/{record}/edit'),
        ];
    }
}
