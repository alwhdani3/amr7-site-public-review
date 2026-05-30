<?php

namespace App\Services;

use App\Models\Post;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PostIngestor
{
    public function create(array $data): Post
    {
        $title   = trim((string)($data['title'] ?? ''));
        $content = (string)($data['content'] ?? '');

        // ✅ Slug يدعم العربي + fallback
        $slug = $this->makeSlug($title);
        if ($slug === '') {
            $slug = 'post-' . time();
        }

        // ✅ تفادي التكرار (استخدمنا while لضمان عدم التكرار حتى لو تم إنشاء مقالين في نفس الثانية)
        $originalSlug = $slug;
        $counter = 1;
        while (Post::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        // ✅ excerpt & SEO
        $excerpt = $data['excerpt'] ?? $data['seo_desc'] ?? Str::limit(trim(strip_tags($content)), 155, '');

        // ✅ draft/publish
        $isPublished = (bool)($data['is_published'] ?? true);
        $publishedAt = $isPublished ? ($data['published_at'] ?? now()) : null;

        // ✅ image: تنزيل محلي أو العودة للرابط الخارجي
        $image = $this->resolveImage($data['image_url'] ?? null);

        return Post::create([
            'title'            => $title,
            'slug'             => $slug,
            'content'          => $content,
            'excerpt'          => $excerpt,
            'image'            => $image, 
            'is_published'     => $isPublished,
            'published_at'     => $publishedAt,
            'views'            => 0,
            
            // 🚀 إضافة حقول الـ SEO التي تم تمريرها من n8n أو الكنترولر
            'meta_title'       => $data['meta_title'] ?? null,
            'meta_description' => $data['meta_description'] ?? $excerpt, // نستخدم المقتطف كبديل ممتاز
            'meta_keywords'    => $data['meta_keywords'] ?? null,
        ]);
    }

    /**
     * Allowed MIME types for ingested post images. Verified via finfo on the
     * downloaded body — extension-derived hints alone are insufficient since
     * remote URLs can lie.
     */
    private const ALLOWED_IMAGE_MIMES = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
    ];

    /** Hard cap on remote image size, in bytes. Prevents disk-fill via large payloads. */
    private const MAX_IMAGE_BYTES = 8 * 1024 * 1024; // 8 MB

    private function resolveImage(?string $imageUrl): ?string
    {
        if (!$imageUrl) return null;

        // 🛡️ SSRF guard: reject local/private/internal targets before fetching
        if (! $this->isSafeImageUrl($imageUrl)) {
            return null;
        }

        try {
            $response = Http::timeout(25)
                ->withOptions(['allow_redirects' => ['max' => 3, 'strict' => true]])
                ->get($imageUrl);

            if (! $response->successful()) {
                return $imageUrl;
            }

            $body = (string) $response->body();
            if (strlen($body) === 0 || strlen($body) > self::MAX_IMAGE_BYTES) {
                return $imageUrl;
            }

            // Verify the actual content type from the bytes — not the URL
            // extension and not the Content-Type header (both are spoofable).
            $detectedMime = $this->sniffMime($body);
            if (! isset(self::ALLOWED_IMAGE_MIMES[$detectedMime])) {
                return $imageUrl;
            }

            $ext = self::ALLOWED_IMAGE_MIMES[$detectedMime];
            $path = 'posts/' . now()->format('YmdHis') . '-' . Str::random(10) . '.' . $ext;

            Storage::disk('public')->put($path, $body);
            return $path;
        } catch (\Throwable $e) {
            // Network/decode failure — fall back to storing the external URL.
        }

        return $imageUrl;
    }

    private function sniffMime(string $body): string
    {
        if (! function_exists('finfo_open')) {
            return '';
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo === false) {
            return '';
        }

        $mime = (string) finfo_buffer($finfo, $body);
        finfo_close($finfo);

        return $mime;
    }

    /**
     * Validate that a URL is safe to fetch from the server.
     *
     * Blocks SSRF vectors by allowing only http/https schemes and rejecting
     * hosts that resolve to loopback, link-local, or RFC1918 private IPs.
     */
    private function isSafeImageUrl(string $url): bool
    {
        $parts = parse_url($url);

        if (! $parts || empty($parts['scheme']) || empty($parts['host'])) {
            return false;
        }

        if (! in_array(strtolower($parts['scheme']), ['http', 'https'], true)) {
            return false;
        }

        $host = strtolower($parts['host']);

        // Reject obvious local hostnames that won't resolve to a useful IP via DNS
        if (in_array($host, ['localhost', '0.0.0.0', '0', 'ip6-localhost', 'ip6-loopback'], true)) {
            return false;
        }

        // If the host is an IP literal, validate it directly
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            return (bool) filter_var(
                $host,
                FILTER_VALIDATE_IP,
                FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
            );
        }

        // Resolve the hostname and validate the resulting IP
        $ip = gethostbyname($host);
        if ($ip === $host) {
            // gethostbyname returns the input on failure
            return false;
        }

        return (bool) filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        );
    }

    private function makeSlug(string $title): string
    {
        $t = trim($title);
        if ($t === '') return '';

        // أولاً جرّب slug العادي مع دعم اللغة العربية في لارافل (طريقة إضافية)
        $s = Str::slug($t, '-', 'ar'); 
        if ($s !== '') return $s;

        // fallback عربي: حافظ على العربي + الأرقام + الشرطات
        $t = preg_replace('/[\s_]+/u', '-', $t);
        $t = preg_replace('/[^\p{Arabic}\p{L}\p{N}-]+/u', '', $t);
        $t = preg_replace('/-+/u', '-', $t);

        return trim($t, '-');
    }
}