<?php

namespace App\Filament\Resources\TaxReturnRequestResource\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components as F;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

/**
 * Phase 9C-4 — ملفات إقرار ضريبي (metadata only).
 *
 *  - يُسجَّل من TaxReturnRequestResource::getRelations() فقط إذا tax_return_files جاهز.
 *  - لا FileUpload فعلي — يدير metadata/path فقط (Phase لاحقة ستضيف رفع ملفات).
 */
class TaxReturnFilesRelationManager extends RelationManager
{
    protected static string $relationship = 'files';
    protected static ?string $title = 'الملفات';
    protected static ?string $modelLabel = 'ملف';
    protected static ?string $pluralModelLabel = 'الملفات';

    public static function fileKeyOptions(): array
    {
        return [
            'purchases' => 'المشتريات',
            'sales'     => 'المبيعات',
            'bank'      => 'كشوفات بنكية',
            'other'     => 'أخرى',
        ];
    }

    public static function visibilityOptions(): array
    {
        return [
            'private' => 'خاص (إداري)',
            'client'  => 'يظهر للعميل',
            'both'    => 'إداري + عميل',
        ];
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            F\Select::make('file_key')
                ->label('نوع الملف')
                ->options(static::fileKeyOptions())
                ->required(),

            F\Select::make('uploaded_by_user_id')
                ->label('مرفوع بواسطة')
                ->relationship('uploader', 'name')
                ->searchable()
                ->preload()
                ->nullable(),

            F\TextInput::make('disk')
                ->label('Disk')
                ->default('local')
                ->required()
                ->maxLength(30)
                ->helperText('اسم الـdisk المُعرّف في config/filesystems.php'),

            F\TextInput::make('path')
                ->label('المسار')
                ->required()
                ->maxLength(500)
                ->helperText('مسار الملف على الـdisk (سيستبدل لاحقاً بـFileUpload فعلي).'),

            F\TextInput::make('original_name')
                ->label('اسم الملف الأصلي')
                ->maxLength(255),

            F\TextInput::make('mime')
                ->label('MIME')
                ->maxLength(120)
                ->placeholder('application/pdf'),

            F\TextInput::make('size')
                ->label('الحجم (بايت)')
                ->numeric()
                ->minValue(0),

            F\Select::make('visibility')
                ->label('الظهور')
                ->options(static::visibilityOptions())
                ->default('private')
                ->required(),

            F\KeyValue::make('metadata')
                ->label('بيانات إضافية')
                ->reorderable()
                ->columnSpanFull(),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('original_name')
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('file_key')
                    ->label('النوع')
                    ->badge()
                    ->formatStateUsing(fn ($state) => static::fileKeyOptions()[$state] ?? $state)
                    ->color('info'),

                TextColumn::make('original_name')
                    ->label('اسم الملف')
                    ->searchable()
                    ->limit(40)
                    ->placeholder('—'),

                TextColumn::make('mime')
                    ->label('MIME')
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('size')
                    ->label('الحجم')
                    ->formatStateUsing(fn ($state) => $state ? number_format($state / 1024, 1) . ' KB' : '—')
                    ->toggleable(),

                TextColumn::make('uploader.name')
                    ->label('بواسطة')
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('updated_at')
                    ->label('آخر تحديث')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('file_key')
                    ->label('النوع')
                    ->options(static::fileKeyOptions()),
            ])
            ->headerActions([
                CreateAction::make()->label('إضافة ملف'),
            ])
            ->recordActions([
                EditAction::make()->label('تعديل'),
                DeleteAction::make()->label('حذف'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    // لا bulk delete — حذف ملفات قد يكون له آثار storage.
                ]),
            ]);
    }
}
