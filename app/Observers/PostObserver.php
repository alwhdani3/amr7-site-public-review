<?php

namespace App\Observers;

use App\Models\Post;
use Illuminate\Support\Str;

class PostObserver
{
    /**
     * تنفيذ الكود قبل إنشاء المقال في قاعدة البيانات
     */
    public function creating(Post $post): void
    {
        if (!$post->slug) {
            $post->slug = $this->generateUniqueSlug($post->title);
        }
    }

    /**
     * تنفيذ الكود قبل تحديث المقال
     */
    public function updating(Post $post): void
    {
        if ($post->isDirty('title') && !$post->isDirty('slug')) {
            $post->slug = $this->generateUniqueSlug($post->title);
        }
    }

    /**
     * دالة ذكية لضمان عدم تكرار الـ Slug
     */
    private function generateUniqueSlug(string $title): string
    {
        // استخدام السلج الأصلي (يدعم العربية في لارافيل الحديث)
        $slug = Str::slug($title);
        
        if (empty($slug)) {
            // حل احتياطي في حال كان العنوان لا يحتوي إلا على رموز
            $slug = 'post-' . time();
        }

        $originalSlug = $slug;
        $count = 1;

        // التحقق من التكرار
        while (Post::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $count++;
        }

        return $slug;
    }
}