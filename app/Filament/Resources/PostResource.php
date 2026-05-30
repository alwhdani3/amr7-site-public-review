<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PostResource\Pages;
use App\Models\Post;
use App\Services\Ai\N8nContentClient;
use Filament\Actions\Action as FilamentAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components as F;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    protected static \UnitEnum|string|null $navigationGroup = 'المحتوى';
    protected static ?string $navigationLabel = 'المدونة والأخبار';
    protected static ?string $modelLabel = 'مقال';
    protected static ?string $pluralModelLabel = 'المقالات';
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-document-text';

    protected static function isEditor(): bool
    {
        $u = auth()->user();

        return (bool) $u && in_array($u->role, ['admin', 'manager'], true);
    }

    public static function canViewAny(): bool
    {
        return auth()->check();
    }

    public static function canCreate(): bool
    {
        return static::isEditor();
    }

    public static function canEdit($record): bool
    {
        return static::isEditor();
    }

    public static function canDelete($record): bool
    {
        return static::isEditor();
    }

    public static function canDeleteAny(): bool
    {
        return static::isEditor();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Group::make()
                    ->schema([
                        Section::make('المحتوى الرئيسي')
                            ->schema([
                                F\TextInput::make('title')
                                    ->label('عنوان المقال')
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Get $get, Set $set, ?string $state): void {
                                        if (filled($state) && blank($get('slug'))) {
                                            $set('slug', Str::slug($state));
                                        }
                                    }),

                                F\TextInput::make('slug')
                                    ->label('الرابط الدائم (Slug)')
                                    ->disabled()
                                    ->dehydrated()
                                    ->required()
                                    ->unique(Post::class, 'slug', ignoreRecord: true),

                                F\Textarea::make('content')
                                    ->label('نص المقال (HTML)')
                                    ->required()
                                    ->columnSpanFull()
                                    ->rows(20)
                                    ->extraAttributes([
                                        'style' => 'font-family: monospace; font-size: 13px; direction: ltr; white-space: pre;',
                                        'dir'   => 'ltr',
                                    ])
                                    ->hintAction(
                                        FilamentAction::make('improve_ai')
                                            ->icon('heroicon-m-sparkles')
                                            ->label('تحسين النص بـ AI')
                                            ->color('info')
                                            ->action(function (Set $set, Get $get): void {
                                                $currentContent = $get('content');

                                                if (blank($currentContent)) {
                                                    Notification::make()
                                                        ->warning()
                                                        ->title('اكتب نصاً أولاً ليتم تحسينه!')
                                                        ->send();

                                                    return;
                                                }

                                                try {
                                                    $client   = app(N8nContentClient::class);
                                                    $envelope = $client->improve($currentContent, app()->getLocale());

                                                    if (! $envelope['ok']) {
                                                        Notification::make()
                                                            ->danger()
                                                            ->title('فشل تحسين النص')
                                                            ->body($client->reasonMessage($envelope['reason'], $envelope['status']))
                                                            ->send();
                                                        return;
                                                    }

                                                    $data         = $envelope['data'];
                                                    $improvedText = $data['content_html'] ?? $data['content'] ?? null;

                                                    if (! is_string($improvedText) || trim(strip_tags($improvedText)) === '') {
                                                        Notification::make()
                                                            ->warning()
                                                            ->title('n8n رجّع بدون محتوى')
                                                            ->body('لم يصل نص محسَّن — تم إبقاء النص الأصلي كما هو.')
                                                            ->send();
                                                        return;
                                                    }

                                                    $set('content', $improvedText);

                                                    Notification::make()
                                                        ->success()
                                                        ->title('تم تحسين النص بنجاح!')
                                                        ->send();
                                                } catch (\Throwable $e) {
                                                    report($e);

                                                    Notification::make()
                                                        ->danger()
                                                        ->title('خطأ في الاتصال بالذكاء الاصطناعي')
                                                        ->body('تعذر تحسين النص حالياً، حاول مرة أخرى.')
                                                        ->send();
                                                }
                                            })
                                    ),
                            ]),

                        Section::make('تحسين محركات البحث (SEO)')
                            ->collapsed()
                            ->schema([
                                F\TextInput::make('meta_title')
                                    ->label('عنوان الميتا (Meta Title)')
                                    ->placeholder('اتركه فارغاً لاستخدام عنوان المقال تلقائياً')
                                    ->maxLength(60),

                                F\Textarea::make('meta_description')
                                    ->label('وصف الميتا (Meta Description)')
                                    ->placeholder('اتركه فارغاً لاستخدام الوصف المختصر')
                                    ->rows(2)
                                    ->maxLength(160),

                                F\TextInput::make('meta_keywords')
                                    ->label('الكلمات المفتاحية')
                                    ->placeholder('مثال: شركات, استثمار, السعودية'),
                            ]),
                    ])
                    ->columnSpan(2),

                Group::make()
                    ->schema([
                        Section::make('النشر والوسائط')
                            ->schema([
                                F\FileUpload::make('image')
                                    ->label('الصورة البارزة')
                                    ->disk('public')
                                    ->directory('posts/covers')
                                    ->image()
                                    ->imageEditor()
                                    ->openable()
                                    ->downloadable(),

                                F\Textarea::make('excerpt')
                                    ->label('وصف مختصر')
                                    ->helperText('يظهر في بطاقات المدونة ونتائج البحث')
                                    ->rows(3),

                                F\DatePicker::make('published_at')
                                    ->label('تاريخ النشر')
                                    ->default(now())
                                    ->required(),

                                F\Toggle::make('is_published')
                                    ->label('نشر المقال')
                                    ->default(true)
                                    ->onColor('success')
                                    ->offColor('danger'),

                                F\Placeholder::make('views')
                                    ->label('عدد المشاهدات')
                                    ->content(fn (?Post $record): string => (string) ($record->views ?? 0)),
                            ]),
                    ])
                    ->columnSpan(1),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('الصورة')
                    ->circular(),

                Tables\Columns\TextColumn::make('title')
                    ->label('العنوان')
                    ->searchable()
                    ->limit(40)
                    ->weight(FontWeight::Bold)
                    ->description(fn (Post $record): string => $record->slug),

                Tables\Columns\IconColumn::make('is_published')
                    ->label('منشور')
                    ->boolean(),

                Tables\Columns\TextColumn::make('views')
                    ->label('المشاهدات')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                Tables\Columns\TextColumn::make('published_at')
                    ->label('تاريخ النشر')
                    ->date()
                    ->sortable(),
            ])
            ->defaultSort('published_at', 'desc')
            ->recordUrl(
                fn (Post $record): ?string => static::isEditor()
                    ? static::getUrl('edit', ['record' => $record])
                    : null
            )
            ->filters([
                Tables\Filters\TernaryFilter::make('is_published')
                    ->label('حالة النشر'),
            ])
            ->actions([
                FilamentAction::make('preview')
                    ->label('معاينة')
                    ->icon('heroicon-m-eye')
                    ->color('gray')
                    ->url(fn (Post $record): string => url("/blog/{$record->slug}"))
                    ->openUrlInNewTab(),

                EditAction::make()
                    ->visible(fn (): bool => static::isEditor()),

                DeleteAction::make()
                    ->visible(fn (): bool => static::isEditor()),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn (): bool => static::isEditor()),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPosts::route('/'),
            'create' => Pages\CreatePost::route('/create'),
            'edit'   => Pages\EditPost::route('/{record}/edit'),
        ];
    }
}