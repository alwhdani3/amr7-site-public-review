<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerResource\Pages;
use App\Models\Customer;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;

use Filament\Tables;
use Filament\Tables\Table;

use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\BulkActionGroup;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-users';
    protected static \UnitEnum|string|null $navigationGroup = 'العملاء والخدمات';
    protected static ?int $navigationSort = 10;
    protected static ?string $navigationLabel = 'العملاء';
    protected static ?string $modelLabel = 'عميل';
    protected static ?string $pluralModelLabel = 'العملاء';

    // ── Authorization ──────────────────────────────────────────────────────────

    public static function canViewAny(): bool
    {
        return static::userHasAnyRole(auth()->user(), ['super_admin', 'admin', 'manager']);
    }

    public static function canCreate(): bool
    {
        return static::userHasAnyRole(auth()->user(), ['super_admin', 'admin', 'manager']);
    }

    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return static::userHasAnyRole(auth()->user(), ['super_admin', 'admin', 'manager']);
    }

    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return static::userHasAnyRole(auth()->user(), ['super_admin', 'admin']);
    }

    public static function canDeleteAny(): bool
    {
        return static::userHasAnyRole(auth()->user(), ['super_admin', 'admin']);
    }

    protected static function userHasAnyRole(?\App\Models\User $user, array $roles): bool
    {
        if (! $user) {
            return false;
        }
        if ($user->is_admin) {
            return true;
        }
        if (method_exists($user, 'hasAnyRole') && $user->hasAnyRole($roles)) {
            return true;
        }
        return in_array(strtolower((string) ($user->role ?? '')), $roles, true);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('company_id')
                ->label('المنشأة التابعة')
                ->relationship('company', 'name')
                ->searchable()
                ->preload()
                ->required(),

            TextInput::make('name')
                ->label('اسم العميل')
                ->required()
                ->maxLength(255),

            TextInput::make('email')
                ->label('البريد الإلكتروني')
                ->email()
                ->maxLength(255)
                ->nullable()
                ->unique(ignoreRecord: true),

            TextInput::make('phone')
                ->label('رقم الهاتف')
                ->tel()
                ->maxLength(30)
                ->nullable(),

            TextInput::make('national_id')
                ->label('رقم الهوية')
                ->maxLength(20)
                ->nullable(),

            Toggle::make('is_active')
                ->label('حساب نشط')
                ->default(true),

            Textarea::make('notes')
                ->label('ملاحظات')
                ->rows(4)
                ->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('company.name')
                    ->label('المنشأة')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('الاسم')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('phone')
                    ->label('الجوال')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('البريد')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('نشط')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإضافة')
                    ->since()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn () => static::canDeleteAny()),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            'edit'   => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }
}
