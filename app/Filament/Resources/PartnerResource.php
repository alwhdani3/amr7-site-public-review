<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PartnerResource\Pages;
use App\Models\Partner;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components as F;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Schema as DbSchema;

class PartnerResource extends Resource
{
    protected static ?string $model = Partner::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'شركاء النجاح';
    protected static ?string $modelLabel = 'شريك';
    protected static ?string $pluralModelLabel = 'الشركاء';
    protected static string|\UnitEnum|null $navigationGroup = 'المحتوى';

    public static function canViewAny(): bool
    {
        return DbSchema::hasTable('partners')
            && static::userHasAnyRole(auth()->user(), ['manager']);
    }

    public static function canCreate(): bool
    {
        return DbSchema::hasTable('partners')
            && static::userHasAnyRole(auth()->user(), ['manager']);
    }

    public static function canView($record): bool
    {
        return static::userHasAnyRole(auth()->user(), ['manager']);
    }

    public static function canEdit($record): bool
    {
        return static::userHasAnyRole(auth()->user(), ['manager']);
    }

    public static function canDelete($record): bool
    {
        return static::userHasAnyRole(auth()->user(), ['manager']);
    }

    public static function canDeleteAny(): bool
    {
        return static::userHasAnyRole(auth()->user(), ['manager']);
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
            Section::make('بيانات الشريك / العميل')
                ->schema([
                    F\TextInput::make('name')
                        ->label('اسم الشركة/العميل')
                        ->required(),

                    F\TextInput::make('url')
                        ->label('رابط الموقع (اختياري)')
                        ->url()
                        ->suffixIcon('heroicon-m-globe-alt'),

                    F\FileUpload::make('logo')
                        ->label('شعار الشركة')
                        ->disk('public')
                        ->directory('partners')
                        ->image()
                        ->required()
                        ->columnSpanFull(),

                    F\Toggle::make('is_active')
                        ->label('عرض في الموقع')
                        ->default(true),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('logo')
                    ->label('الشعار')
                    ->circular(),

                TextColumn::make('name')
                    ->label('الاسم')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Bold),

                IconColumn::make('is_active')
                    ->label('الحالة')
                    ->boolean(),

                TextColumn::make('created_at')
                    ->label('تاريخ الإضافة')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
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
            'index'  => Pages\ListPartners::route('/'),
            'create' => Pages\CreatePartner::route('/create'),
            'edit'   => Pages\EditPartner::route('/{record}/edit'),
        ];
    }
}
