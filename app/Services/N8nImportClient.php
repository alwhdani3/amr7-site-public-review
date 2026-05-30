<?php

namespace App\Services;

use App\Services\Ai\N8nContentClient;

/**
 * Wrapper للـ import webhook. حالياً غير مستخدم من الواجهة (الاستيراد من رابط
 * يمرّ عبر N8nAiClient::rewrite مع url)، لكن يُحتفظ به للـ backward compatibility
 * مع أي callers خارجيين محتملين.
 */
class N8nImportClient
{
    public function __construct(private N8nContentClient $client)
    {
    }

    public function importFromUrl(string $url, string $lang = 'ar'): array
    {
        $envelope = $this->client->importFromUrl($url, $lang);

        if (! $envelope['ok']) {
            return [
                'title'         => null,
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

        $data = $envelope['data'];

        return [
            'title'         => $data['title']         ?? null,
            'content_html'  => $data['content_html']  ?? ($data['content'] ?? null),
            'seo_desc'      => $data['seo_desc']      ?? ($data['excerpt'] ?? null),
            'image_url'     => $data['image_url']     ?? null,
            'meta_title'    => $data['meta_title']    ?? null,
            'meta_keywords' => $data['meta_keywords'] ?? null,
            '_n8n_ok'       => true,
            '_n8n_reason'   => null,
            '_n8n_status'   => $envelope['status'],
        ];
    }
}
