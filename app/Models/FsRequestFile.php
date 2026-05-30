<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class FsRequestFile extends Model
{
    protected $fillable = [
        'request_id',
        'uploader_id',
        'kind',
        'path',
        'original_name',
        'size',
        'mime',
        'is_final',
        'visibility'
    ];

    protected $casts = [
        'is_final' => 'boolean',
        'size' => 'integer',
        'request_id' => 'integer',
        'uploader_id' => 'integer',
    ];

    protected $appends = ['url', 'formatted_size'];

    public function request(): BelongsTo
    {
        return $this->belongsTo(FinancialStatementRequest::class, 'request_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploader_id');
    }

    public function getUrlAttribute(): string
    {
        return Storage::disk('local')->url($this->path);
    }

    public function getFormattedSizeAttribute(): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($this->size, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, 2) . ' ' . $units[$pow];
    }

    public static function upload($requestModel, $file, $kind, $visibility = 'public')
    {
        $path = $file->store('fs-requests', 'local');

        return self::create([
            'request_id'    => $requestModel->id,
            'uploader_id'   => auth()->id(),
            'kind'          => $kind,
            'path'          => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime'          => $file->getMimeType(),
            'size'          => $file->getSize(),
            'is_final'      => false,
            'visibility'    => $visibility,
        ]);
    }
}