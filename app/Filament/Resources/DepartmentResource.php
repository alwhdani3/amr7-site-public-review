<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DepartmentResource\Pages;
use App\Models\Department;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components as F;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class DepartmentResource extends Resource
{
    protected static ?string $model = Department::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-building-office';
    protected static string|\UnitEnum|null $navigationGroup = 'الإدارة';
    protected static ?string $navigationLabel = 'الأقسام';
    protected static ?string $modelLabel = 'قسم';
    protected static ?string $pluralModelLabel = 'الأقسام';
    protected static ?int $navigationSort = 2;

    protected static function isAdmin(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        return method_exists($user, 'isAdmin')
            ? (bool) $user->isAdmin()
            : ($user->role === 'admin');
    }

    public static function canViewAny(): bool
    {
        return static::isAdmin();
    }

    public static function canCreate(): bool
    {
        return static::isAdmin();
    }

    public static function canEdit(Model $record): bool
    {
        return static::isAdmin();
    }

    public static function canDelete(Model $record): bool
    {
        return static::isAdmin();
    }

    public static function canDeleteAny(): bool
    {
        return static::isAdmin();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('بيانات القسم')
                ->schema([
                    F\TextInput::make('name')
                        ->label('اسم القسم')
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function ($state, callable $set, callable $get): void {
                            if (blank($get('slug')) && filled($state)) {
                                $set('slug', Str::slug($state));
                            }
                        }),

                    F\TextInput::make('slug')
                        ->label('الـ Slug (الرابط)')
                        ->required()
                        ->maxLength(255)
                        ->helperText('يُملأ تلقائياً من الاسم. غيّره فقط إذا احتجت.')
                        ->rules(['alpha_dash'])
                        ->unique(ignoreRecord: true),

                    F\Textarea::make('description')
                        ->label('وصف القسم')
                        ->rows(3)
                        ->maxLength(1000)
                        ->placeholder('وصف مختصر يظهر للموظفين والعملاء...')
                        ->nullable()
                        ->columnSpanFull(),

                    F\Toggle::make('is_active')
                        ->label('القسم مفعّل')
                        ->helperText('القسم المعطّل لا يظهر في قوائم الاختيار.')
                        ->default(true),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('#')
                    ->sortable()
                    ->width('50px'),

                Tables\Columns\TextColumn::make('name')
                    ->label('اسم القسم')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold')
                    ->description(fn (Department $record): ?string => $record->description
                        ? Str::limit($record->description, 60)
                        : null),

                Tables\Columns\TextColumn::make('slug')
                    ->label('الـ Slug')
                    ->copyable()
                    ->copyMessage('تم النسخ')
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('users_count')
                    ->label('الموظفون')
                    ->counts('users')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('tasks_count')
                    ->label('المهام')
                    ->counts('tasks')
                    ->badge()
                    ->color('warning'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('مفعّل')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('Y-m-d')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('الحالة')
                    ->trueLabel('نشط فقط')
                    ->falseLabel('معطّل فقط')
                    ->placeholder('الكل'),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make()
                        ->label('تعديل'),

                    Action::make('toggleActive')
                        ->label(fn (Department $record): string => $record->is_active ? 'تعطيل القسم' : 'تفعيل القسم')
                        ->icon(fn (Department $record): string => $record->is_active ? 'heroicon-o-no-symbol' : 'heroicon-o-check-circle')
                        ->color(fn (Department $record): string => $record->is_active ? 'warning' : 'success')
                        ->requiresConfirmation()
                        ->action(function (Department $record): void {
                            $newState = ! $record->is_active;

                            $record->update([
                                'is_active' => $newState,
                            ]);

                            Notification::make()
                                ->title($newState ? 'تم تفعيل القسم' : 'تم تعطيل القسم')
                                ->success()
                                ->send();
                        }),

                    DeleteAction::make()
                        ->label('حذف')
                        ->modalDescription('سيُلغى ربط الموظفين والمهام التابعة لهذا القسم. متأكد؟'),
                ])
                    ->tooltip('الإجراءات')
                    ->icon('heroicon-m-ellipsis-vertical'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('لا توجد أقسام')
            ->emptyStateDescription('أضف قسمك الأول لتنظيم الموظفين والتذاكر.')
            ->emptyStateIcon('heroicon-o-building-office');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDepartments::route('/'),
            'create' => Pages\CreateDepartment::route('/create'),
            'edit' => Pages\EditDepartment::route('/{record}/edit'),
        ];
    }
}