<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Post extends Model
{
    use HasFactory;

    protected $table = 'posts';

    protected $fillable = [
        // البيانات الأساسية
        'title',
        'slug',
        'content',
        'excerpt',       // (موجود في Post)
        'image',
        
        // الحالة والنشر
        'is_published',
        'published_at',  // (موجود في Post)
        'views',         // (موجود في Post)
        
        // بيانات السيو (قادمة من Article)
        'meta_title',
        'meta_description',
        'meta_keywords',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
        'views'        => 'integer',
    ];

    protected $appends = ['image_url', 'reading_time', 'short_description'];

    /**
     * ✅ توليد Slug تلقائياً عند الإنشاء
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($post) {
            if (empty($post->slug)) {
                $post->slug = Str::slug($post->title);
            }
        });
        
        static::updating(function ($post) {
            if ($post->isDirty('title') && empty($post->slug)) {
                 $post->slug = Str::slug($post->title);
            }
        });
    }

    /**
     * استخدام Slug في الروابط
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /* =============================
        Accessors (المعالجات)
    ============================== */

    /**
     * جلب رابط الصورة (يدعم الروابط الخارجية والمحلية)
     */
    public function getImageUrlAttribute(): string
    {
        if (!$this->image) {
            return asset('images/placeholder-blog.jpg');
        }

        if (Str::startsWith($this->image, ['http://', 'https://'])) {
            return $this->image;
        }

        return Storage::disk('public')->url($this->image);
    }

    /**
     * حساب وقت القراءة
     */
    public function getReadingTimeAttribute(): int
    {
        $text = strip_tags($this->content);
        $wordCount = str_word_count($text);
        // متوسط القراءة: 200 كلمة في الدقيقة
        return (int) ceil($wordCount / 200) ?: 1;
    }

    /**
     * وصف مختصر (يدمج ميزة Article و Post)
     * إذا وجد excerpt يرجعه، وإلا يقتطع من المحتوى
     */
    public function getShortDescriptionAttribute(): string
    {
        return $this->excerpt ?? Str::limit(strip_tags($this->content), 150);
    }

    /* =============================
        Scopes (نطاقات البحث)
    ============================== */

    /**
     * المقالات المنشورة فقط (والتي حان موعد نشرها)
     */
    public function scopePublished(Builder $query): void
    {
        $query->where('is_published', true)
              ->where(function ($q) {
                  $q->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
              });
    }

    /* =============================
        Helpers
    ============================== */

    public function incrementViews(): void
    {
        $this->increment('views');
    }
}