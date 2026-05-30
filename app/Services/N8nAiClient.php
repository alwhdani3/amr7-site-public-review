<?php

namespace App\Services;

use App\Services\Ai\N8nContentClient;

/**
 * Wrapper متوافق مع الكود القديم.
 *
 * النية المستقبلية: استدعاء N8nContentClient مباشرة من الـ Filament callers
 * للحصول على envelope كامل (ok / reason / status). هذا الوسيط يضمن عدم
 * كسر أي مكان كان يستدعي rewrite()/improve() بالتعاقد القديم.
 */
class N8nAiClient
{
    public function __construct(private N8nContentClient $client)
    {
    }

    /**
     * يعيد نفس مفاتيح المصفوفة كما كان سابقاً. عند فشل n8n نعيد المحتوى الأصلي
     * في 'content' بدلاً من تخريب البيانات. يضاف '_n8n_ok' / '_n8n_reason'
     * كي يتمكّن الـ callers الجدد من اكتشاف الفشل بسهولة.
     */
    public function rewrite(string $title, string $content, string $url = '', string $locale = 'ar'): array
    {
        $envelope = $this->client->rewrite($title, $content, $url, $locale);

        if (! $envelope['ok']) {
            return [
                'title'         => $title,
                'content'       => $content,
                'content_html'  => null,
                'seo_desc'      => null,
                'image_url'     => null,
                'meta_title'    => null,
                'meta_keywords' => null,
                '_n8n_ok'       => false,
                '_n8n_reason'   => $envelope['reason'],
                '_n8n_status'   => $envelope['status'],
            ];
        }

        $data       = $envelope['data'];
        $newContent = $data['content_html'] ?? $data['content'] ?? null;
        $hasContent = is_string($newContent) && trim(strip_tags($newContent)) !== '';

        return [
            'title'         => is_string($data['title'] ?? null) && trim($data['title']) !== '' ? $data['title'] : $title,
            'content'       => $hasContent ? $newContent : $content,
            'content_html'  => $hasContent ? $newContent : null,
            'seo_desc'      => $data['seo_desc']      ?? null,
            'image_url'     => $data['image_url']     ?? null,
            'meta_title'    => $data['meta_title']    ?? null,
            'meta_keywords' => $data['meta_keywords'] ?? null,
            '_n8n_ok'       => true,
            '_n8n_reason'   => null,
            '_n8n_status'   => $envelope['status'],
        ];
    }

    /**
     * يحافظ على التعاقد القديم: يُرجع string. عند الفشل أو الرد الفارغ
     * يُرجع النص الأصلي دون تغيير (آمن جداً للـ rich editor).
     */
    public function improve(string $content, string $locale = 'ar'): string
    {
        $envelope = $this->client->improve($content, $locale);

        if (! $envelope['ok']) {
            return $content;
        }

        $data = $envelope['data'];
        $new  = $data['content_html'] ?? $data['content'] ?? null;

        if (! is_string($new) || trim(strip_tags($new)) === '') {
            return $content;
        }

        return $new;
    }
}
