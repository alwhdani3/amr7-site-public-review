<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Artesaos\SEOTools\Facades\SEOTools;
use Illuminate\Support\Str;

class BlogController extends Controller
{
    public function show(Post $post)
    {
        $canPreview = $this->canPreviewUnpublished();

        abort_if(! $post->is_published && ! $canPreview, 404);

        if ($post->is_published && ! $canPreview) {
            $post->incrementViews();
        }

        $siteName = 'شركة آمر سبعة لحلول الأعمال';
        $title = trim((string) ($post->meta_title ?: ($post->title . ' | ' . $siteName)));

        $descriptionSource = $post->meta_description
            ?: ($post->excerpt ?: Str::limit(strip_tags((string) $post->content), 160));

        $description = trim((string) $descriptionSource);
        $canonicalUrl = url()->current();
        $imageUrl = $post->image ? $post->image_url : asset('assets/images/default-post.png');

        SEOTools::setTitle($title, false);
        SEOTools::setDescription($description);
        SEOTools::setCanonical($canonicalUrl);

        if ($post->meta_keywords) {
            SEOTools::metatags()->setKeywords(
                array_values(array_filter(array_map('trim', explode(',', (string) $post->meta_keywords))))
            );
        }

        if (! $post->is_published) {
            SEOTools::metatags()->setRobots('noindex,nofollow');
        }

        SEOTools::opengraph()->setUrl($canonicalUrl);
        SEOTools::opengraph()->setTitle($title);
        SEOTools::opengraph()->setDescription($description);
        SEOTools::opengraph()->addProperty('type', 'article');

        if ($post->published_at) {
            SEOTools::opengraph()->addProperty('article:published_time', $post->published_at->toIso8601String());
        }

        SEOTools::opengraph()->addImage($imageUrl);
        SEOTools::twitter()->setTitle($title);
        SEOTools::twitter()->setDescription($description);
        SEOTools::twitter()->setImage($imageUrl);

        return view('public.blog.show', [
            'post'    => $post,
            'article' => $post,
        ]);
    }

    private function canPreviewUnpublished(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        if (method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['super_admin', 'admin', 'manager'])) {
            return true;
        }

        $legacyRole = strtolower((string) ($user->role ?? ''));

        return in_array($legacyRole, ['admin', 'manager'], true);
    }
}