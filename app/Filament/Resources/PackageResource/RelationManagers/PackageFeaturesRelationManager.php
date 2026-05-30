<?php

namespace App\Filament\Resources\PackageResource\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components as F;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Phase 9C-1 — إدارة الميزات المهيكلة لكل باقة (package_features).
 *
 *  - يُسجَّل من PackageResource::getRelations() فقط إذا الجدول جاهز.
 *  - يبقى الـ features JSON القديم في Package كما هو، بلا حذف ولا تعارض.
 */
class PackageFeaturesRelationManager extends RelationManager
{
    protected static string $relationship = 'packageFeatures';
    protected static ?string $title = 'الميزات المهيكلة';
    protected static ?string $modelLabel = 'ميزة';
    protected static ?string $pluralModelLabel = 'الميزات';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            F\TextInput::make('feature_code')
                ->label('رمز الميزة')
                ->required()
                ->maxLength(80)
                ->helperText('رمز فريد لاستخدامه برمجياً، مثل: consultations, vat_filings, payroll_users')
                ->placeholder('consultations'),

            F\TextInput::make('label_ar')
                ->label('الاسم بالعربية')
                ->required()
                ->maxLength(191),

            F\TextInput::make('label_en')
                ->label('Label (English)')
                ->maxLength(191),

            F\TextInput::make('quota')
                ->label('الحصّة (Quota)')
                ->numeric()
                ->minValue(0)
                ->helperText('اتركه فارغاً للميزات بلا حد كمّي.'),

            F\Select::make('unit')
                ->label('الوحدة')
                ->options([
                    'مرة'      => 'مرة',
                    'استشارة' => 'استشارة',
                    'ملف'      => 'ملف',
                    'مستخدم'   => 'مستخدم',
                    'شهر'      => 'شهر',
                    'ربع سنة' => 'ربع سنة',
                    'سنة'      => 'سنة',
                ])
                ->searchable()
                ->nullable(),

            F\Textarea::make('description_ar')
                ->label('الوصف بالعربية')
                ->rows(3)
                ->columnSpanFull(),

            F\Textarea::make('description_en')
                ->label('Description (English)')
                ->rows(3)
                ->columnSpanFull(),

            F\Toggle::make('is_highlighted')
                ->label('إبراز هذه الميزة')
                ->default(false)
                ->inline(false),

            F\TextInput::make('sort_order')
                ->label('ترتيب العرض')
                ->numeric()
                ->default(0),

            F\KeyValue::make('metadata')
                ->label('بيانات إضافية')
                ->keyLabel('المفتاح')
                ->valueLabel('القيمة')
                ->reorderable()
                ->columnSpanFull(),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('label_ar')
            ->defaultSort('sort_order', 'asc')
            ->columns([
                TextColumn::make('feature_code')
                    ->label('الرمز')
                    ->searchable()
                    ->badge()
                    ->color('gray'),

                TextColumn::make('label_ar')
                    ->label('الاسم')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('quota')
                    ->label('الحصّة')
                    ->numeric()
                    ->placeholder('بلا حد')
                    ->sortable(),

                TextColumn::make('unit')
                    ->label('الوحدة')
                    ->placeholder('—'),

                IconColumn::make('is_highlighted')
                    ->label('مُبرَزة')
                    ->boolean(),

                TextColumn::make('sort_order')
                    ->label('ترتيب')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('updated_at')
                    ->label('آخر تحديث')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([
                CreateAction::make()->label('إضافة ميزة'),
            ])
            ->recordActions([
                EditAction::make()->label('تعديل'),
                DeleteAction::make()->label('حذف'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->reorderable('sort_order');
    }
}
