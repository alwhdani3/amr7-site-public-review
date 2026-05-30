<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Article extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'content',
        'image',
        'excerpt',
        'is_active',
        'published_at',
    ];

    /**
     * تحويل البيانات تلقائياً (Casting)
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    /**
     * ✅ يخلي Route Model Binding يستخدم slug بدل id
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors & Mutators (المعالجات التلقائية)
    |--------------------------------------------------------------------------
    */

    /**
     * إنشاء Slug تلقائياً عند حفظ العنوان إذا كان الـ Slug فارغاً
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($article) {
            if (empty($article->slug)) {
                $article->slug = Str::slug($article->title);
            }
        });
    }

    /**
     * جلب رابط الصورة الكامل (مفيد جداً للـ API والعرض)
     * الاستخدام: $article->image_url
     */
    public function getImageUrlAttribute()
    {
        if ($this->image) {
            return asset('storage/' . $this->image);
        }
        // صورة افتراضية في حال عدم وجود صورة
        return asset('images/default-article.jpg');
    }

    /**
     * جلب مقتطف قصير من المحتوى تلقائياً إذا كان حقل excerpt فارغاً
     * الاستخدام: $article->short_description
     */
    public function getShortDescriptionAttribute()
    {
        return $this->excerpt ?? Str::limit(strip_tags($this->content), 150);
    }
}