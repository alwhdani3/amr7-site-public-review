<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Number;
use App\Models\Ticket;
use App\Models\TicketReply;

class Attachment extends Model
{
    protected $table = 'attachments';

    protected $fillable = [
        'ticket_id',
        'user_id',
        'company_id',
        'uploaded_by',
        'title',
        'category',
        'tags',
        'disk',
        'path',
        'file_path',
        'original_name',
        'mime',
        'size',
        'attachable_type',
        'attachable_id',
        'mime_type',
        'file_name',
        'extension',
        'visibility',
        'ticket_reply_id',
    ];

    protected $casts = [
        'tags' => 'array',
        'size' => 'integer',
    ];

    protected $appends = [
        'url',
        'readable_size',
        'icon_class',
        'extension',
    ];

    /* =============================
        RELATIONS (العلاقات)
    ============================== */

    public function attachable(): MorphTo
    {
        return $this->morphTo();
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function uploadedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /* =============================
        ACCESSORS & MUTATORS
    ============================== */

    /**
     * رابط الملف الذكي: يفرق بين العام والخاص تلقائياً ✅
     */
    public function getUrlAttribute(): string
    {
        $path = $this->path ?: $this->file_path;

        if (! $path) return '#';

        // إذا كان الملف مخزناً في القرص العام، نعطي رابطاً مباشراً
        if ($this->disk === 'public') {
            return Storage::disk('public')->url($path);
        }

        // للملفات الخاصة (التذاكر وغيرها)، نستخدم الرابط الآمن المشفر
        return route('attachments.download', $this->id);
    }

    public function getReadableSizeAttribute(): string
    {
        if (! $this->size) return '0 KB';

        if (class_exists(Number::class)) {
            return Number::fileSize((int) $this->size);
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = (int) $this->size;
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, 2) . ' ' . $units[$pow];
    }

    public function getIconClassAttribute(): string
    {
        $mime = strtolower((string) ($this->mime ?? ''));

        return match (true) {
            str_starts_with($mime, 'image/') => 'fas fa-image text-purple-600',
            str_contains($mime, 'pdf')        => 'fas fa-file-pdf text-red-600',
            str_contains($mime, 'word')       => 'fas fa-file-word text-blue-600',
            str_contains($mime, 'excel')      => 'fas fa-file-excel text-green-600',
            str_contains($mime, 'zip'),
            str_contains($mime, 'rar')        => 'fas fa-file-archive text-yellow-600',
            default                           => 'fas fa-paperclip text-slate-500',
        };
    }

    // --- توافق الحقول الوهمية ---

    public function getMimeTypeAttribute(): ?string { return $this->mime; }
    public function setMimeTypeAttribute($value): void { $this->attributes['mime'] = $value; }

    public function getFileNameAttribute(): ?string
    {
        return $this->original_name ?: basename($this->path ?: $this->file_path ?: '');
    }

    public function getExtensionAttribute(): string
    {
        return strtolower(pathinfo($this->file_name ?? '', PATHINFO_EXTENSION));
    }

    public function setTicketReplyIdAttribute($value): void
    {
        if (filled($value)) {
            $this->attributes['attachable_type'] = TicketReply::class;
            $this->attributes['attachable_id'] = (int) $value;
        }
    }

    /* =============================
        MODEL EVENTS (BOOTED)
    ============================== */

    protected static function booted(): void
    {
        static::creating(function (Attachment $att) {
            // التخزين الافتراضي للمرفقات الجديدة هو الخاص private ✅
            $att->disk = $att->disk ?? 'private';

            // توحيد المسارات
            if (empty($att->path)) $att->path = $att->file_path;
            if (empty($att->file_path)) $att->file_path = $att->path;

            // تعبئة البيانات الأساسية آلياً
            if (empty($att->original_name)) $att->original_name = basename($att->path ?? '');
            if (empty($att->uploaded_by)) $att->uploaded_by = auth()->id();
            if (empty($att->user_id)) $att->user_id = $att->uploaded_by;

            // الربط الذكي بالتذكرة والشركة آلياً 🧠
            if (empty($att->ticket_id) && !empty($att->attachable_id)) {
                if ($att->attachable_type === Ticket::class) {
                    $att->ticket_id = $att->attachable_id;
                } elseif ($att->attachable_type === TicketReply::class) {
                    $reply = TicketReply::find($att->attachable_id);
                    if ($reply) $att->ticket_id = $reply->ticket_id;
                }
            }

            // سحب الشركة من التذكرة لضمان التقارير الصحيحة
            if (empty($att->company_id) && !empty($att->ticket_id)) {
                $ticket = Ticket::find($att->ticket_id);
                if ($ticket) $att->company_id = $ticket->company_id;
            }
        });

        // Phase 5 audit fix: تنظيف الملف الفعلي على القرص عند حذف الـAttachment.
        // فقط مرفقات جديدة سيتم حذفها بهذا — السلوك مستقبلي.
        // يفحص المسار والـdisk قبل الحذف ويتجاهل الأخطاء بـreport()
        // لتفادي كسر عملية الحذف الأصلية.
        static::deleted(function (Attachment $att): void {
            $path = trim((string) ($att->path ?: $att->file_path));
            if ($path === '') {
                return;
            }

            $disk = trim((string) ($att->disk ?: 'private'));
            if ($disk === '') {
                return;
            }

            try {
                if (Storage::disk($disk)->exists($path)) {
                    Storage::disk($disk)->delete($path);
                }
            } catch (\Throwable $e) {
                report($e);
            }
        });
    }
}