<?php

namespace App\Filament\Resources\PostResource\Pages;

use App\Filament\Resources\PostResource;
use App\Models\Post;
use App\Services\Ai\N8nContentClient;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Illuminate\Support\Str;

class ListPosts extends ListRecords
{
    protected static string $resource = PostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),

            Action::make('importAi')
                ->label('استيراد + AI')
                ->icon('heroicon-o-sparkles')
                ->color('info')
                ->form([
                    TextInput::make('title')
                        ->label('عنوان مبدئي / موضوع')
                        ->required(),

                    TextInput::make('url')
                        ->label('رابط مقال للاستيراد (اختياري)')
                        ->url()
                        ->placeholder('https://example.com/article')
                        ->helperText('إذا وضعت رابطاً، سيقوم الذكاء الاصطناعي بقراءته وإعادة صياغته.'),

                    Textarea::make('raw')
                        ->label('أو نص خام / نقاط رئيسية')
                        ->rows(4)
                        ->helperText('اتركه فارغاً إذا قمت بوضع رابط أعلاه.'),

                    Toggle::make('publish')
                        ->label('نشر مباشرة')
                        ->default(false),
                ])
                ->action(function (array $data) {
                    try {
                        $client = app(N8nContentClient::class);

                        $envelope = $client->rewrite(
                            title: $data['title'],
                            content: $data['raw'] ?? '',
                            url: $data['url'] ?? '',
                            locale: app()->getLocale()
                        );

                        if (! $envelope['ok']) {
                            Notification::make()
                                ->title('فشل الاستيراد')
                                ->body($client->reasonMessage($envelope['reason'], $envelope['status']))
                                ->danger()
                                ->send();
                            return;
                        }

                        $aiData      = $envelope['data'];
                        $contentHtml = $aiData['content_html'] ?? $aiData['content'] ?? null;

                        // حماية: لا ننشئ post بمحتوى فاضي مهما كانت الحالة.
                        if (! is_string($contentHtml) || trim(strip_tags($contentHtml)) === '') {
                            Notification::make()
                                ->title('n8n رجّع بدون محتوى')
                                ->body('استُلم رد ناجح لكن بدون نص فعلي — لم يتم إنشاء المقال.')
                                ->warning()
                                ->send();
                            return;
                        }

                        $title = is_string($aiData['title'] ?? null) && trim($aiData['title']) !== ''
                            ? $aiData['title']
                            : $data['title'];

                        // slug مع fallback للعربي
                        $slug = Str::slug($title, '-', 'ar');
                        if (empty($slug)) {
                            $slug = 'post-' . time();
                        }
                        $originalSlug = $slug;
                        $counter = 1;
                        while (Post::where('slug', $slug)->exists()) {
                            $slug = $originalSlug . '-' . $counter++;
                        }

                        $seoDesc = $aiData['seo_desc']
                            ?? $aiData['excerpt']
                            ?? Str::limit(strip_tags($contentHtml), 155);

                        $post = Post::create([
                            'title'            => $title,
                            'slug'             => $slug,
                            'content'          => $contentHtml,
                            'excerpt'          => $seoDesc,
                            'meta_title'       => $aiData['meta_title'] ?? $title,
                            'meta_description' => $seoDesc,
                            'meta_keywords'    => $aiData['meta_keywords'] ?? null,
                            'image'            => $aiData['image_url'] ?? null,
                            'is_published'     => $data['publish'],
                            'published_at'     => $data['publish'] ? now() : null,
                            'views'            => 0,
                        ]);

                        Notification::make()
                            ->title('تم استيراد وصياغة المقال بنجاح!')
                            ->success()
                            ->send();

                        return redirect()->to(PostResource::getUrl('edit', ['record' => $post]));

                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('حدث خطأ أثناء الاتصال بالذكاء الاصطناعي')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}