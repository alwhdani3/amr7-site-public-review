<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PostIngestor;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function store(Request $request)
    {
        // ❌ تم إزالة كود الحماية من هنا لأن الـ Middleware (CheckN8nSecret) يقوم بهذه المهمة قبل وصول الطلب للكنترولر

        // ✅ التحقق من البيانات (Validation) مع إضافة حقول السيو
        $validated = $request->validate([
            'title'            => 'required|string|max:255',
            'content'          => 'required|string',
            'image_url'        => ['nullable', 'url:http,https', 'max:2048'],
            // حقول السيو الإضافية لتتطابق مع قاعدة البيانات و Filament
            'seo_desc'         => 'nullable|string|max:160', 
            'meta_title'       => 'nullable|string|max:60',
            'meta_keywords'    => 'nullable|string|max:255',
            'is_published'     => 'nullable|boolean',
            'published_at'     => 'nullable|date',
        ]);

        // ✅ إنشاء المقال عبر الخدمة المخصصة
        $post = app(PostIngestor::class)->create([
            'title'            => $validated['title'],
            'content'          => $validated['content'],
            'image_url'        => $validated['image_url'] ?? null,
            // تمرير حقول السيو (تأكد أن PostIngestor يستقبلها ويحفظها)
            'excerpt'          => $validated['seo_desc'] ?? null,
            'meta_title'       => $validated['meta_title'] ?? null,
            'meta_description' => $validated['seo_desc'] ?? null, // نستخدم seo_desc كوصف للميتا أيضاً
            'meta_keywords'    => $validated['meta_keywords'] ?? null,
            // استخدام دالة boolean الأنيقة من لارافل (ترجع true إذا لم يتم تمرير القيمة)
            'is_published'     => $request->boolean('is_published', true),
            'published_at'     => $validated['published_at'] ?? now(), // إذا لم يرسل تاريخ نضع تاريخ اللحظة
        ]);

        // ✅ إرجاع الرد إلى n8n
        return response()->json([
            'success' => true,
            'id'      => $post->id,
            'slug'    => $post->slug,
            'url'     => url('/blog/' . $post->slug),
            'message' => 'Post created successfully',
        ]);
    }
}