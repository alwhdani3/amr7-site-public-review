<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SettingResource\Pages;
use App\Models\Setting;
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

class SettingResource extends Resource
{
    protected static ?string $model = Setting::class;

    protected static string|\UnitEnum|null $navigationGroup = 'النظام';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?int $navigationSort = 50;
    protected static ?string $navigationLabel = 'الإعدادات';
    protected static ?string $modelLabel = 'إعداد';
    protected static ?string $pluralModelLabel = 'الإعدادات';

    // Settings touch site-wide behaviour. View + edit are open to admins and
    // anyone with the explicit settings.edit permission; create + delete are
    // admin-only because adding/removing a settings key is a structural change.

    public static function canViewAny(): bool
    {
        return static::userCanEditSettings(auth()->user());
    }

    public static function canEdit($record): bool
    {
        return static::userCanEditSettings(auth()->user());
    }

    public static function canCreate(): bool
    {
        return static::userIsAdmin(auth()->user());
    }

    public static function canDelete($record): bool
    {
        return static::userIsAdmin(auth()->user());
    }

    public static function canDeleteAny(): bool
    {
        return static::userIsAdmin(auth()->user());
    }

    protected static function userCanEditSettings(?\App\Models\User $user): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->is_admin) {
            return true;
        }

        if (method_exists($user, 'can') && $user->can('settings.edit')) {
            return true;
        }

        return false;
    }

    protected static function userIsAdmin(?\App\Models\User $user): bool
    {
        return (bool) ($user?->is_admin);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('إعدادات النظام')
                ->description('إدارة مفاتيح الإعدادات والقيم المستخدمة في الموقع ولوحة التحكم.')
                ->schema([
                    F\TextInput::make('key')
                        ->label('اسم الإعداد (Key)')
                        ->required()
                        ->maxLength(100)
                        ->unique(ignoreRecord: true)
                        ->disabled(fn (string $context) => $context === 'edit')
                        ->helperText('مثال: whatsapp_number أو phone_number أو gtm_id'),

                    F\Textarea::make('value')
                        ->label('القيمة (Value)')
                        ->rows(3)
                        ->columnSpanFull()
                        ->helperText('القيمة الفعلية للإعداد (رقم، رابط، كود...)'),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('key')
                    ->label('الإعداد')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('value')
                    ->label('القيمة')
                    ->limit(60)
                    ->wrap(),
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
            ->emptyStateHeading('لا توجد إعدادات بعد')
            ->emptyStateDescription('أضف مفاتيح إعدادات النظام من زر "إنشاء".');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListSettings::route('/'),
            'create' => Pages\CreateSetting::route('/create'),
            'edit'   => Pages\EditSetting::route('/{record}/edit'),
        ];
    }
}
