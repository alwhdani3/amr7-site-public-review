<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceCategoryResource\Pages;
use App\Models\ServiceCategory;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components as F;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;

class ServiceCategoryResource extends Resource
{
    protected static ?string $model = ServiceCategory::class;

    protected static string|\UnitEnum|null $navigationGroup = 'المحتوى';
    protected static ?string $navigationLabel = 'تصنيفات الخدمات';
    protected static ?string $modelLabel = 'تصنيف خدمة';
    protected static ?string $pluralModelLabel = 'تصنيفات الخدمات';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

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
            Section::make('بيانات التصنيف')
                ->schema([
                    F\Select::make('department_id')
                        ->label('القسم')
                        ->relationship('department', 'name')
                        ->searchable()
                        ->preload()
                        ->nullable(),

                    F\TextInput::make('name_ar')
                        ->label('اسم التصنيف (عربي)')
                        ->required()
                        ->maxLength(255),

                    F\TextInput::make('name_en')
                        ->label('اسم التصنيف (English)')
                        ->maxLength(255)
                        ->nullable(),

                    F\Toggle::make('is_active')
                        ->label('مفعل')
                        ->default(true),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name_ar')
                    ->label('الاسم')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('department.name')
                    ->label('القسم')
                    ->badge()
                    ->color('info')
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('is_active')
                    ->label('الحالة')
                    ->boolean(),
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

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListServiceCategories::route('/'),
            'create' => Pages\CreateServiceCategory::route('/create'),
            'edit'   => Pages\EditServiceCategory::route('/{record}/edit'),
        ];
    }
}
