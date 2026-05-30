<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServicePlatformResource\Pages;
use App\Models\ServicePlatform;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components as F;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;

class ServicePlatformResource extends Resource
{
    protected static ?string $model = ServicePlatform::class;

    protected static string|\UnitEnum|null $navigationGroup = 'المحتوى';
    protected static ?string $navigationLabel = 'منصات الخدمات';
    protected static ?string $modelLabel = 'منصة خدمة';
    protected static ?string $pluralModelLabel = 'منصات الخدمات';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-square-3-stack-3d';

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
            Section::make('بيانات المنصة')
                ->schema([
                    F\Select::make('service_category_id')
                        ->label('القسم الرئيسي')
                        ->relationship('category', 'name_ar')
                        ->searchable()
                        ->preload()
                        ->required(),

                    F\TextInput::make('name_ar')
                        ->label('اسم المنصة (عربي)')
                        ->required()
                        ->maxLength(255),

                    F\TextInput::make('name_en')
                        ->label('اسم المنصة (إنجليزي)')
                        ->required()
                        ->maxLength(255),

                    F\Textarea::make('description_ar')
                        ->label('وصف المنصة (يظهر في الهيدر)')
                        ->rows(3)
                        ->columnSpanFull(),

                    F\FileUpload::make('image')
                        ->label('شعار المنصة (أيقونة صغيرة)')
                        ->disk('public')
                        ->directory('platforms/logos')
                        ->image()
                        ->columnSpanFull(),

                    F\FileUpload::make('hero_image')
                        ->label('صورة الهيدر العريضة (خلفية الصفحة)')
                        ->disk('public')
                        ->directory('platforms/hero')
                        ->image()
                        ->imageEditor()
                        ->columnSpanFull(),

                    F\Toggle::make('is_active')
                        ->label('تفعيل المنصة')
                        ->default(true),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->sortable(),

                ImageColumn::make('image')
                    ->label('الشعار')
                    ->circular(),

                TextColumn::make('name_ar')
                    ->label('اسم المنصة')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Bold),

                TextColumn::make('category.name_ar')
                    ->label('القسم الرئيسي')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('الحالة')
                    ->boolean(),

                TextColumn::make('created_at')
                    ->label('تاريخ الإضافة')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('service_category_id')
                    ->label('القسم الرئيسي')
                    ->relationship('category', 'name_ar'),
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
            'index'  => Pages\ListServicePlatforms::route('/'),
            'create' => Pages\CreateServicePlatform::route('/create'),
            'edit'   => Pages\EditServicePlatform::route('/{record}/edit'),
        ];
    }
}
