<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class CompanyFile extends Model
{
    protected $fillable = [
        'company_id',
        'uploaded_by',
        'title',
        'category',
        'disk',
        'path',
        'original_name',
        'mime',
        'size',
        'is_public',
    ];

    protected $casts = [
        'size' => 'integer',
        'is_public' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::deleted(function (CompanyFile $file): void {
            $path = trim((string) $file->path);
            if ($path === '') {
                return;
            }

            $disk = trim((string) ($file->disk ?: 'private'));
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

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getHumanSizeAttribute(): string
    {
        $bytes = (int) ($this->size ?? 0);
        if ($bytes < 1024) return $bytes . ' B';
        if ($bytes < 1024 * 1024) return round($bytes / 1024, 1) . ' KB';
        if ($bytes < 1024 * 1024 * 1024) return round($bytes / (1024 * 1024), 1) . ' MB';
        return round($bytes / (1024 * 1024 * 1024), 2) . ' GB';
    }
}
