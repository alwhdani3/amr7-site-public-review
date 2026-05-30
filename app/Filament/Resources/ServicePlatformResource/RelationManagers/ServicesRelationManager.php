<?php

namespace App\Filament\Resources\ServicePlatformResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Forms\Components as F;
use Filament\Tables;
use Filament\Tables\Table;

use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;

class ServicesRelationManager extends RelationManager
{
    protected static string $relationship = 'services';
    protected static ?string $title = 'الخدمات';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            F\TextInput::make('title_ar')
                ->label('اسم الخدمة (عربي)')
                ->required()
                ->maxLength(255),

            F\TextInput::make('title_en')
                ->label('اسم الخدمة (English)')
                ->maxLength(255),

            F\Textarea::make('excerpt_ar')
                ->label('وصف مختصر (عربي)')
                ->rows(2)
                ->columnSpanFull(),

            F\Textarea::make('excerpt_en')
                ->label('وصف مختصر (English)')
                ->rows(2)
                ->columnSpanFull(),

            F\RichEditor::make('content_ar')
                ->label('المحتوى (عربي)')
                ->columnSpanFull(),

            F\RichEditor::make('content_en')
                ->label('المحتوى (English)')
                ->columnSpanFull(),

            F\Toggle::make('is_active')
                ->label('مفعل')
                ->default(true),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title_ar')
            ->columns([
                Tables\Columns\TextColumn::make('title_ar')
                    ->label('الخدمة')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('الحالة')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('أُنشئت')
                    ->since(),
            ])
            ->headerActions([
                CreateAction::make()->label('إضافة خدمة'),
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
