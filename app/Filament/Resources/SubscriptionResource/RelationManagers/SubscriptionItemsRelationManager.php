<?php

namespace App\Filament\Resources\SubscriptionResource\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components as F;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Schema as DbSchema;

/**
 * Phase 9C-2 — استهلاك الميزات لكل اشتراك (subscription_items).
 *
 *  - يُسجَّل من SubscriptionResource::getRelations() فقط إذا الجدول جاهز.
 *  - package_feature_id Select يظهر فقط إذا package_features جاهز (Phase 9B).
 */
class SubscriptionItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';
    protected static ?string $title = 'استهلاك الميزات';
    protected static ?string $modelLabel = 'ميزة مستهلكة';
    protected static ?string $pluralModelLabel = 'الميزات المستهلكة';

    public function form(Schema $schema): Schema
    {
        $packageFeaturesReady = DbSchema::hasTable('package_features');

        $fields = [
            F\TextInput::make('feature_code')
                ->label('رمز الميزة')
                ->required()
                ->maxLength(80)
                ->placeholder('consultations'),
        ];

        if ($packageFeaturesReady) {
            $fields[] = F\Select::make('package_feature_id')
                ->label('ربط بميزة باقة')
                ->relationship('packageFeature', 'label_ar')
                ->searchable()
                ->preload()
                ->nullable()
                ->helperText('اختياري — للربط بميزة معرّفة على الباقة.');
        }

        $fields[] = F\TextInput::make('quota_limit')
            ->label('الحدّ الأقصى (Quota)')
            ->numeric()
            ->minValue(0)
            ->nullable()
            ->helperText('اتركه فارغاً للميزات بلا حد.');

        $fields[] = F\TextInput::make('quota_used')
            ->label('المستهلك')
            ->numeric()
            ->minValue(0)
            ->default(0)
            ->required();

        $fields[] = F\DatePicker::make('period_start')
            ->label('بداية الفترة');

        $fields[] = F\DatePicker::make('period_end')
            ->label('نهاية الفترة');

        $fields[] = F\KeyValue::make('metadata')
            ->label('بيانات إضافية')
            ->keyLabel('المفتاح')
            ->valueLabel('القيمة')
            ->reorderable()
            ->columnSpanFull();

        return $schema->components($fields)->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('feature_code')
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('feature_code')
                    ->label('الرمز')
                    ->searchable()
                    ->badge()
                    ->color('gray'),

                TextColumn::make('quota_used')
                    ->label('المستهلك')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('quota_limit')
                    ->label('الحد')
                    ->numeric()
                    ->placeholder('بلا حد')
                    ->sortable(),

                TextColumn::make('period_start')
                    ->label('بداية الفترة')
                    ->date()
                    ->toggleable(),

                TextColumn::make('period_end')
                    ->label('نهاية الفترة')
                    ->date()
                    ->toggleable(),

                TextColumn::make('updated_at')
                    ->label('آخر تحديث')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([
                CreateAction::make()->label('إضافة استهلاك'),
            ])
            ->recordActions([
                EditAction::make()->label('تعديل'),
                DeleteAction::make()->label('حذف'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
