<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use App\Models\CompanyDocument;
use App\Models\CompanyFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SecureFileController extends Controller
{
    private function secureHeaders(string $disposition = 'inline', ?string $filename = null): array
    {
        $headers = [
            'X-Robots-Tag' => 'noindex, nofollow, noarchive',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store, max-age=0',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ];

        if ($filename) {
            $headers['Content-Disposition'] = $disposition . '; filename="' . addslashes($filename) . '"';
        }

        return $headers;
    }

    private function resolveDiskAndPath(?string $preferredDisk, ?string $path): ?array
    {
        if (! $path) {
            return null;
        }

        $preferredDisk = $preferredDisk ?: 'private';

        if (Storage::disk($preferredDisk)->exists($path)) {
            return [$preferredDisk, $path];
        }

        if ($preferredDisk !== 'private' && Storage::disk('private')->exists($path)) {
            return ['private', $path];
        }

        if ($preferredDisk !== 'public' && Storage::disk('public')->exists($path)) {
            return ['public', $path];
        }

        return null;
    }

    private function isBackofficeUser($user): bool
    {
        if (! $user) {
            return false;
        }

        if (method_exists($user, 'hasBackofficeAccess') && $user->hasBackofficeAccess()) {
            return true;
        }

        if (method_exists($user, 'hasAnyRole') && $user->hasAnyRole([
            'super_admin',
            'admin',
            'manager',
            'employee',
            'support',
        ])) {
            return true;
        }

        $legacyRole = strtolower((string) ($user->role ?? ''));

        return in_array($legacyRole, [
            'superadmin',
            'admin',
            'manager',
            'agent',
            'staff',
            'employee',
            'support',
        ], true);
    }

    private function userOwnsCompany($user, ?int $companyId): bool
    {
        if (! $user || ! $companyId) {
            return false;
        }

        return $user->companies()
            ->whereKey($companyId)
            ->wherePivot('is_active', true)
            ->exists();
    }

    private function canAccessCompanyScopedFile($user, ?int $companyId): bool
    {
        if (! $user) {
            return false;
        }

        if ($this->isBackofficeUser($user)) {
            return true;
        }

        return $this->userOwnsCompany($user, $companyId);
    }

    public function attachmentView(Request $request, Attachment $attachment): StreamedResponse
    {
        $user = $request->user();
        abort_unless($user, 403);

        $companyId = $attachment->company_id;

        if (! $companyId && $attachment->ticket_id && $attachment->ticket) {
            $companyId = $attachment->ticket->company_id;
        }

        abort_unless($this->canAccessCompanyScopedFile($user, $companyId), 403);

        $path = $attachment->path ?: $attachment->file_path;
        $resolved = $this->resolveDiskAndPath($attachment->disk, $path);

        abort_unless($resolved, 404);

        [$disk, $resolvedPath] = $resolved;

        return Storage::disk($disk)->response(
            $resolvedPath,
            $attachment->original_name ?: basename($resolvedPath),
            $this->secureHeaders('inline', $attachment->original_name ?: basename($resolvedPath))
        );
    }

    public function attachmentDownload(Request $request, Attachment $attachment): StreamedResponse
    {
        $user = $request->user();
        abort_unless($user, 403);

        $companyId = $attachment->company_id;

        if (! $companyId && $attachment->ticket_id && $attachment->ticket) {
            $companyId = $attachment->ticket->company_id;
        }

        abort_unless($this->canAccessCompanyScopedFile($user, $companyId), 403);

        $path = $attachment->path ?: $attachment->file_path;
        $resolved = $this->resolveDiskAndPath($attachment->disk, $path);

        abort_unless($resolved, 404);

        [$disk, $resolvedPath] = $resolved;

        return Storage::disk($disk)->download(
            $resolvedPath,
            $attachment->original_name ?: basename($resolvedPath),
            $this->secureHeaders('attachment', $attachment->original_name ?: basename($resolvedPath))
        );
    }

    public function companyDocView(Request $request, CompanyDocument $companyDocument): StreamedResponse
    {
        $user = $request->user();
        abort_unless($user, 403);
        abort_unless($this->canAccessCompanyScopedFile($user, $companyDocument->company_id), 403);

        $resolved = $this->resolveDiskAndPath('private', $companyDocument->file_path);
        abort_unless($resolved, 404);

        [$disk, $resolvedPath] = $resolved;

        return Storage::disk($disk)->response(
            $resolvedPath,
            basename($resolvedPath),
            $this->secureHeaders('inline', basename($resolvedPath))
        );
    }

    public function companyDocDownload(Request $request, CompanyDocument $companyDocument): StreamedResponse
    {
        $user = $request->user();
        abort_unless($user, 403);
        abort_unless($this->canAccessCompanyScopedFile($user, $companyDocument->company_id), 403);

        $resolved = $this->resolveDiskAndPath('private', $companyDocument->file_path);
        abort_unless($resolved, 404);

        [$disk, $resolvedPath] = $resolved;

        return Storage::disk($disk)->download(
            $resolvedPath,
            basename($resolvedPath),
            $this->secureHeaders('attachment', basename($resolvedPath))
        );
    }

    public function companyFileView(Request $request, CompanyFile $companyFile): StreamedResponse
    {
        $user = $request->user();
        abort_unless($user, 403);
        abort_unless($this->canAccessCompanyScopedFile($user, $companyFile->company_id), 403);

        $path = $companyFile->path ?? $companyFile->file_path ?? null;
        $disk = $companyFile->disk ?? 'private';

        $resolved = $this->resolveDiskAndPath($disk, $path);
        abort_unless($resolved, 404);

        [$resolvedDisk, $resolvedPath] = $resolved;
        $filename = $companyFile->original_name ?? basename($resolvedPath);

        return Storage::disk($resolvedDisk)->response(
            $resolvedPath,
            $filename,
            $this->secureHeaders('inline', $filename)
        );
    }

    public function companyFileDownload(Request $request, CompanyFile $companyFile): StreamedResponse
    {
        $user = $request->user();
        abort_unless($user, 403);
        abort_unless($this->canAccessCompanyScopedFile($user, $companyFile->company_id), 403);

        $path = $companyFile->path ?? $companyFile->file_path ?? null;
        $disk = $companyFile->disk ?? 'private';

        $resolved = $this->resolveDiskAndPath($disk, $path);
        abort_unless($resolved, 404);

        [$resolvedDisk, $resolvedPath] = $resolved;
        $filename = $companyFile->original_name ?? basename($resolvedPath);

        return Storage::disk($resolvedDisk)->download(
            $resolvedPath,
            $filename,
            $this->secureHeaders('attachment', $filename)
        );
    }
}