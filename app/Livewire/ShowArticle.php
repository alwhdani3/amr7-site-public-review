<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Article;
use App\Traits\HasSEO;
use Illuminate\Support\Str;
use Artesaos\SEOTools\Facades\SEOTools;
use Livewire\Attributes\Locked; // 👈 ميزة أمنية في Livewire 3

class ShowArticle extends Component
{
    use HasSEO;

    // 🔒 نستخدم Locked لمنع التلاعب بالبيانات من المتصفح
    #[Locked] 
    public Article $article;

    // نستقبل الموديل مباشرة بدلاً من الـ Slug (Laravel يجلبه تلقائياً)
    public function mount(Article $article)
    {
        // 1. التحقق من النشاط (إذا لم يكن نشطاً، نرجع 404)
        if (! $article->is_active) {
            abort(404);
        }

        $this->article = $article;

        // 2. تجهيز البيانات (تم تحسين كود الصورة للتعامل مع القيم الفارغة)
        $title = $this->article->meta_title ?: $this->article->title;
        
        // تنظيف الوصف: إزالة المسافات الزائدة + التاغات
        $cleanContent = trim(preg_replace('/\s+/', ' ', strip_tags($this->article->content)));
        $description = $this->article->meta_description ?: Str::limit($cleanContent, 160);
        
        $image = $this->article->image ? asset('storage/' . $this->article->image) : null;

        // 3. استدعاء التريت
        $this->setSeo($title, $description, $image);

        // 4. Advanced SEO (Structured Data)
        $this->setStructuredData($title);
    }

    // فصلت كود البيانات المهيكلة لترتيب الكود (Clean Code)
    private function setStructuredData($title)
    {
        SEOTools::opengraph()->addProperty('type', 'article');
        
        // إضافة تاريخ التحديث أيضاً مهم لجوجل
        SEOTools::jsonLd()->setType('Article');
        SEOTools::jsonLd()->addValue('headline', $title);
        SEOTools::jsonLd()->addValue('datePublished', $this->article->created_at->toIso8601String());
        SEOTools::jsonLd()->addValue('dateModified', $this->article->updated_at->toIso8601String());
        
        if ($this->article->image) {
             SEOTools::jsonLd()->addImage(asset('storage/' . $this->article->image));
        }

        if ($this->article->meta_keywords) {
            SEOTools::metatags()->setKeywords(explode(',', $this->article->meta_keywords));
        }
    }

    public function render()
    {
        return view('livewire.show-article');
    }
}