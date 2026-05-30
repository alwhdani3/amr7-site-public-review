<?php

namespace App\Filament\Resources\PostResource\Pages;

use App\Filament\Resources\PostResource;
use App\Services\Ai\N8nContentClient;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditPost extends EditRecord
{
    protected static string $resource = PostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('aiRewrite')
                ->label('تحسين المحتوى (AI)')
                ->icon('heroicon-o-sparkles')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('إعادة صياغة المقال؟')
                ->modalDescription('سيقوم الذكاء الاصطناعي بقراءة محتوى المقال الحالي وإعادة كتابة العنوان، النص، والوصف.')
                ->action(function () {
                    try {
                        $currentTitle = $this->data['title'] ?? '';
                        $rawContent   = $this->data['content'] ?? '';

                        if (is_array($rawContent)) {
                            $currentContent = $this->extractTextFromJson($rawContent);
                        } else {
                            $currentContent = strip_tags((string) $rawContent);
                        }

                        if (empty(trim($currentContent))) {
                            Notification::make()
                                ->title('المحتوى فارغ')
                                ->body('يرجى كتابة بعض المحتوى ليتمكن الذكاء الاصطناعي من تحسينه.')
                                ->warning()
                                ->send();
                            return;
                        }

                        $client   = app(N8nContentClient::class);
                        $envelope = $client->rewrite(
                            title: $currentTitle,
                            content: $currentContent,
                            url: '',
                            locale: app()->getLocale()
                        );

                        if (! $envelope['ok']) {
                            Notification::make()
                                ->title('فشل تحسين المحتوى')
                                ->body($client->reasonMessage($envelope['reason'], $envelope['status']))
                                ->danger()
                                ->send();
                            return;
                        }

                        $ai = $envelope['data'];

                        $newContent = $ai['content_html'] ?? $ai['content'] ?? null;
                        $hasContent = is_string($newContent) && trim(strip_tags($newContent)) !== '';

                        if (! $hasContent) {
                            Notification::make()
                                ->title('n8n رجّع بدون محتوى')
                                ->body('استُلم رد ناجح لكن بدون نص فعلي — لم يتم تعديل المقال.')
                                ->warning()
                                ->send();
                            return;
                        }

                        $this->data['content'] = $newContent;

                        if (! empty($ai['title']) && is_string($ai['title'])) {
                            $this->data['title'] = $ai['title'];
                        }

                        if (! empty($ai['seo_desc']) && is_string($ai['seo_desc'])) {
                            $this->data['excerpt']          = $ai['seo_desc'];
                            $this->data['meta_description'] = $ai['seo_desc'];
                        }

                        if (! empty($ai['meta_title']) && is_string($ai['meta_title'])) {
                            $this->data['meta_title'] = $ai['meta_title'];
                        }

                        if (! empty($ai['meta_keywords']) && is_string($ai['meta_keywords'])) {
                            $this->data['meta_keywords'] = $ai['meta_keywords'];
                        }

                        if (! empty($ai['image_url']) && is_string($ai['image_url']) && empty($this->data['image'])) {
                            $this->data['image'] = $ai['image_url'];
                        }

                        Notification::make()
                            ->title('تم تحسين المحتوى بنجاح! ✨')
                            ->body('راجع التعديلات، ثم اضغط على "حفظ التغييرات" لتأكيدها.')
                            ->success()
                            ->send();

                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('فشل الاتصال بالذكاء الاصطناعي')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Actions\DeleteAction::make(),
        ];
    }

    private function extractTextFromJson(array $json): string
    {
        $text = '';

        if (isset($json['text'])) {
            $text .= $json['text'] . ' ';
        }

        if (isset($json['content']) && is_array($json['content'])) {
            foreach ($json['content'] as $node) {
                if (is_array($node)) {
                    $text .= $this->extractTextFromJson($node);
                }
            }
        }

        return trim($text);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
