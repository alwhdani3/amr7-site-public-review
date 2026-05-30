<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CompanyFileResource\Pages;
use App\Models\Company;
use App\Models\CompanyFile;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components as F;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema as DbSchema;
use Illuminate\Support\Facades\Storage;

class CompanyFileResource extends Resource
{
    protected static ?string $model = CompanyFile::class;

    protected static string|\UnitEnum|null $navigationGroup = 'الوثائق والتذاكر';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-folder-open';
    protected static ?int $navigationSort = 4;
    protected static ?string $navigationLabel = 'ملفات المنشآت';
    protected static ?string $modelLabel = 'ملف منشأة';
    protected static ?string $pluralModelLabel = 'ملفات المنشآت';

    public static function canViewAny(): bool
    {
        return DbSchema::hasTable('company_files')
            && static::userHasAnyRole(auth()->user(), ['manager', 'support', 'accountant']);
    }

    public static function canCreate(): bool
    {
        return DbSchema::hasTable('company_files')
            && static::userHasAnyRole(auth()->user(), ['manager', 'support']);
    }

    public static function canView($record): bool
    {
        return static::userHasAnyRole(auth()->user(), ['manager', 'support', 'accountant']);
    }

    public static function canEdit($record): bool
    {
        return static::userHasAnyRole(auth()->user(), ['manager', 'support']);
    }

    public static function canDelete($record): bool
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
            Section::make('بيانات الملف')
                ->schema([
                    F\Select::make('company_id')
                        ->label('الشركة')
                        ->options(fn () => Company::query()->orderBy('name')->pluck('name', 'id')->toArray())
                        ->searchable()
                        ->preload()
                        ->required(),

                    F\TextInput::make('title')
                        ->label('عنوان الملف')
                        ->maxLength(255)
                        ->nullable(),

                    F\Select::make('category')
                        ->label('التصنيف')
                        ->options([
                            'cr' => 'سجل تجاري',
                            'tax' => 'شهادة ضريبية',
                            'contract' => 'عقود',
                            'hr' => 'ملفات HR',
                            'finance' => 'مالية',
                            'legal' => 'قانونية',
                            'other' => 'أخرى',
                        ])
                        ->searchable()
                        ->nullable(),

                    F\Toggle::make('is_public')
                        ->label('مرئي للعميل')
                        ->helperText('عند التفعيل: يستطيع العميل رؤية وتحميل الملف من بوابته.')
                        ->default(false),

                    F\FileUpload::make('path')
                        ->label('الملف')
                        ->disk('private')
                        ->directory('company-files')
                        ->preserveFilenames()
                        ->maxSize(20480)
                        ->acceptedFileTypes([
                            'application/pdf',
                            'image/jpeg',
                            'image/png',
                            'application/msword',
                            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                            'application/vnd.ms-excel',
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        ])
                        ->required()
                        ->columnSpanFull()
                        ->afterStateUpdated(function ($state, $set) {
                            if ($state) {
                                $set('original_name', $state->getClientOriginalName());
                                $set('mime', $state->getMimeType());
                                $set('size', $state->getSize());
                            }
                        }),

                    F\Hidden::make('original_name'),
                    F\Hidden::make('mime'),
                    F\Hidden::make('size'),
                    F\Hidden::make('disk')->default('private'),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('company.name')
                    ->label('الشركة')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold'),

                Tables\Columns\TextColumn::make('title')
                    ->label('العنوان')
                    ->searchable()
                    ->placeholder('—')
                    ->limit(35),

                Tables\Columns\TextColumn::make('category')
                    ->label('التصنيف')
                    ->badge()
                    ->color('primary')
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'cr' => 'سجل تجاري',
                        'tax' => 'شهادة ضريبية',
                        'contract' => 'عقود',
                        'hr' => 'ملفات HR',
                        'finance' => 'مالية',
                        'legal' => 'قانونية',
                        'other' => 'أخرى',
                        default => $state ?? '—',
                    })
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('original_name')
                    ->label('اسم الملف')
                    ->limit(40)
                    ->icon('heroicon-m-document')
                    ->tooltip(fn ($state) => $state),

                Tables\Columns\TextColumn::make('human_size')
                    ->label('الحجم')
                    ->state(fn (CompanyFile $record) => $record->human_size),

                Tables\Columns\IconColumn::make('is_public')
                    ->label('مرئي للعميل')
                    ->boolean()
                    ->trueIcon('heroicon-o-eye')
                    ->falseIcon('heroicon-o-eye-slash'),

                Tables\Columns\TextColumn::make('uploader.name')
                    ->label('رفعه')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الرفع')
                    ->dateTime('Y-m-d')
                    ->description(fn (CompanyFile $record) => $record->created_at?->diffForHumans() ?? '')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('company_id')
                    ->label('الشركة')
                    ->relationship('company', 'name')
                    ->searchable(),

                Tables\Filters\SelectFilter::make('category')
                    ->label('التصنيف')
                    ->options([
                        'cr' => 'سجل تجاري',
                        'tax' => 'شهادة ضريبية',
                        'contract' => 'عقود',
                        'hr' => 'ملفات HR',
                        'finance' => 'مالية',
                        'legal' => 'قانونية',
                        'other' => 'أخرى',
                    ]),

                Tables\Filters\TernaryFilter::make('is_public')
                    ->label('مرئي للعميل؟'),

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        F\DatePicker::make('created_from')->label('من تاريخ'),
                        F\DatePicker::make('created_until')->label('إلى تاريخ'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['created_from'] ?? null, fn ($q, $d) => $q->whereDate('created_at', '>=', $d))
                            ->when($data['created_until'] ?? null, fn ($q, $d) => $q->whereDate('created_at', '<=', $d));
                    }),
            ])
            ->actions([
                ActionGroup::make([
                    Action::make('download')
                        ->label('تحميل')
                        ->icon('heroicon-m-arrow-down-tray')
                        ->color('success')
                        ->url(fn (CompanyFile $record) => route('company.files.download', $record))
                        ->openUrlInNewTab(),

                    Action::make('toggleVisibility')
                        ->label(fn (CompanyFile $record) => $record->is_public ? 'إخفاء عن العميل' : 'إظهار للعميل')
                        ->icon(fn (CompanyFile $record) => $record->is_public ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                        ->color(fn (CompanyFile $record) => $record->is_public ? 'warning' : 'success')
                        ->visible(fn (CompanyFile $record) => static::canDelete($record))
                        ->action(function (CompanyFile $record) {
                            $record->update(['is_public' => ! $record->is_public]);

                            Notification::make()
                                ->title($record->is_public ? 'تم إظهار الملف للعميل' : 'تم إخفاء الملف عن العميل')
                                ->success()
                                ->send();
                        }),

                    Action::make('delete')
                        ->label('حذف')
                        ->icon('heroicon-m-trash')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->visible(fn (CompanyFile $record) => static::canDelete($record))
                        ->action(function (CompanyFile $record) {
                            $disk = $record->disk ?? 'private';

                            if ($record->path && Storage::disk($disk)->exists($record->path)) {
                                Storage::disk($disk)->delete($record->path);
                            }

                            $record->delete();

                            Notification::make()
                                ->title('تم حذف الملف')
                                ->success()
                                ->send();
                        }),
                ])
                    ->tooltip('الإجراءات')
                    ->icon('heroicon-m-ellipsis-vertical'),
            ])
            ->emptyStateHeading('لا توجد ملفات بعد')
            ->emptyStateDescription('ابدأ برفع ملفات المنشأة من زر "إنشاء" أعلى الصفحة.');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCompanyFiles::route('/'),
            'create' => Pages\CreateCompanyFile::route('/create'),
        ];
    }
}
