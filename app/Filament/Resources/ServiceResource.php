<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceResource\Pages;
use App\Models\Service;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ServiceResource extends Resource
{
    protected static ?string $model = Service::class;

    // ✅ Filament v4+: BackedEnum|string|null
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'الخدمات';
    protected static ?string $modelLabel = 'خدمة';
    protected static ?string $pluralModelLabel = 'الخدمات';

    // ✅ غالبًا يقبل string|UnitEnum|null
    protected static string|\UnitEnum|null $navigationGroup = 'المحتوى';

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
            Section::make('البيانات الأساسية')
                ->description('المعلومات التي تظهر في بطاقة الخدمة والقائمة الجانبية')
                ->schema([
                    TextInput::make('title_ar')
                        ->label('اسم الخدمة (عربي)')
                        ->required()
                        ->columnSpanFull(),

                    Select::make('service_platform_id')
                        ->relationship('platform', 'name_ar')
                        ->label('القسم التابع له')
                        ->required()
                        ->preload()
                        ->searchable(),

                    TextInput::make('price')
                        ->label('سعر الخدمة (ريال)')
                        ->numeric()
                        ->suffix('ر.س'),

                    TextInput::make('govt_fees')
                        ->label('الرسوم الحكومية')
                        ->placeholder('مثال: 2000 ريال (تدفع لاحقاً)'),

                    TextInput::make('duration')
                        ->label('مدة التنفيذ')
                        ->placeholder('مثال: 3 - 5 أيام عمل'),

                    FileUpload::make('icon')
                        ->label('أيقونة الخدمة')
                        ->directory('services/icons')
                        ->image()
                        ->columnSpanFull(),

                    Textarea::make('excerpt_ar')
                        ->label('وصف مختصر (يظهر في الكرت)')
                        ->rows(3)
                        ->columnSpanFull(),

                    Toggle::make('is_active')
                        ->label('تفعيل الخدمة في الموقع')
                        ->default(true)
                        ->inline(false),
                ])
                ->columns(2),

            Section::make('تفاصيل الصفحة (تصميم إتمام)')
                ->schema([
                    Repeater::make('features')
                        ->label('ماذا نقدم لك؟ (الميزات)')
                        ->schema([
                            TextInput::make('title')->label('العنوان')->required(),
                            Textarea::make('description')->label('الوصف البسيط'),
                        ])
                        ->columns(2)
                        ->itemLabel(fn (array $state): ?string => $state['title'] ?? null)
                        ->collapsed(),

                    Repeater::make('steps')
                        ->label('خطوات رحلة العميل')
                        ->schema([
                            TextInput::make('title')->label('اسم الخطوة')->required(),
                            Textarea::make('description')->label('شرح الخطوة'),
                        ])
                        ->itemLabel(fn (array $state): ?string => $state['title'] ?? null)
                        ->collapsed(),

                    Section::make('المتطلبات والشروط')
                        ->schema([
                            Repeater::make('requirements')
                                ->label('المستندات المطلوبة')
                                ->schema([
                                    TextInput::make('item')->label('المستند')->required(),
                                ]),

                            Repeater::make('conditions')
                                ->label('الشروط والأحكام')
                                ->schema([
                                    TextInput::make('item')->label('الشرط')->required(),
                                ]),
                        ])
                        ->columns(2),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('icon')
                    ->label('الأيقونة')
                    ->circular(),

                TextColumn::make('title_ar')
                    ->label('اسم الخدمة')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Bold),

                TextColumn::make('platform.name_ar')
                    ->label('القسم')
                    ->sortable()
                    ->badge()
                    ->color('info'),

                TextColumn::make('price')
                    ->label('السعر')
                    ->money('SAR')
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
                SelectFilter::make('service_platform_id')
                    ->label('تصفية حسب القسم')
                    ->relationship('platform', 'name_ar'),
            ])

            // ✅ بدل Tables\Actions\EditAction (المفقود عندك)
            // نعمل زر "تعديل" يوديك لصفحة edit الأساسية
            ->recordActions([
                Action::make('edit')
                    ->label('تعديل')
                    ->url(fn (Service $record): string => static::getUrl('edit', ['record' => $record])),

                DeleteAction::make()
                    ->label('حذف'),
            ])

            // ✅ Bulk delete
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
            'index'  => Pages\ListServices::route('/'),
            'create' => Pages\CreateService::route('/create'),
            'edit'   => Pages\EditService::route('/{record}/edit'),
        ];
    }
}
