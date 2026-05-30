<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class FinancialStatementFile extends Model
{
    use HasFactory;

    protected $fillable = [
    'financial_statement_request_id',
    'uploaded_by',
    'file_key',
    'disk',
    'visibility',
    'path',
    'original_name',
    'mime',
    'size',
    'is_final',
];

    protected $casts = [
        'is_final' => 'boolean',
        'size'     => 'integer',
    ];

    protected $appends = [
        'url',
        'formatted_size',
        'icon_class',
    ];

    public function request(): BelongsTo
    {
        return $this->belongsTo(FinancialStatementRequest::class, 'financial_statement_request_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getUrlAttribute(): ?string
    {
        return route('financial-statements.file.download', $this);
    }

    public function getFormattedSizeAttribute(): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max((int) $this->size, 0);
        $pow   = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow   = min((int) $pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, 2) . ' ' . $units[$pow];
    }

    public function getIconClassAttribute(): string
    {
        return match ($this->mime) {
            'application/pdf' => 'fas fa-file-pdf text-red-500',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'text/csv' => 'fas fa-file-excel text-green-600',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'fas fa-file-word text-blue-600',
            'image/jpeg',
            'image/png',
            'image/webp' => 'fas fa-file-image text-purple-600',
            'application/zip',
            'application/x-rar-compressed' => 'fas fa-file-archive text-yellow-600',
            default => 'fas fa-file-alt text-slate-500',
        };
    }

    public static function upload($requestModel, $file, $key, $isFinal = false, $disk = 'private')
    {
        $path = $file->store("fs-requests/{$requestModel->id}", $disk);

        return self::create([
            'financial_statement_request_id' => $requestModel->id,
            'uploaded_by'   => auth()->id(),
            'file_key'      => $key,
            'disk'          => $disk,
            'original_name' => $file->getClientOriginalName(),
            'path'          => $path,
            'mime'          => $file->getMimeType(),
            'size'          => $file->getSize(),
            'is_final'      => $isFinal,
        ]);
    }

    public function deleteFile(): void
    {
        $disk = $this->disk ?: 'private';

        if ($this->path && Storage::disk($disk)->exists($this->path)) {
            Storage::disk($disk)->delete($this->path);
        }

        $this->delete();
    }
}