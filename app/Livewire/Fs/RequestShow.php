<?php

namespace App\Livewire\Fs;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\FinancialStatementRequest;
use App\Models\FsRequestFile;
use Artesaos\SEOTools\Facades\SEOTools; // 👈 1. استدعاء المكتبة

class RequestShow extends Component
{
    use WithFileUploads;

    public int $requestId;

    public string $message = '';
    public string $client_notes = '';

    public $extraFiles = []; // kind => array uploads

    public function mount(int $requestId): void
    {
        $this->requestId = $requestId;
        $req = $this->req();
        
        // التحقق من الصلاحية (مهم جداً قبل أي شيء)
        $this->authorize('view', $req);
        
        $this->client_notes = (string)($req->client_notes ?? '');

        // ---------------------------------------------------------
        // 🛡️ إعدادات الخصوصية (Privacy Shield)
        // ---------------------------------------------------------
        
        // 1. عنوان الصفحة: نستخدم رقم التذكرة (ticket_no) لأنه الأهم للعميل
        SEOTools::setTitle('تفاصيل الطلب #' . $req->ticket_no);

        // 2. ⛔ حظر تام من الأرشفة (بيانات مالية خاصة)
        SEOTools::metatags()->addMeta('robots', 'noindex, nofollow');
        // removed: opengraph disable()
        // removed: jsonLd disable()
    }

    public function req(): FinancialStatementRequest
    {
        return FinancialStatementRequest::with(['files','messages.user'])->findOrFail($this->requestId);
    }

    public function sendMessage()
    {
        $req = $this->req();
        $this->authorize('view', $req);

        $this->validate([
            'message' => ['required','string','max:5000'],
        ]);

        $req->messages()->create([
            'user_id' => auth()->id(),
            'body' => $this->message,
            'is_internal' => false,
        ]);

        $this->message = '';
    }

    public function saveNotes()
    {
        $req = $this->req();
        $this->authorize('update', $req);

        $this->validate([
            'client_notes' => ['nullable','string','max:5000'],
        ]);

        $req->update(['client_notes' => $this->client_notes ?: null]);
    }

    public function uploadExtra()
    {
        $req = $this->req();
        $this->authorize('update', $req);

        $allowed = 'pdf,xlsx,xls,csv,jpg,jpeg,png,webp';

        $this->validate([
            'extraFiles.*.*' => ['file',"mimes:$allowed",'max:20480'],
        ]);

        foreach (($this->extraFiles ?? []) as $kind => $uploads) {
            foreach ((array)$uploads as $upload) {
                // ✅ تخزين في مجلد private (محمي)
                $path = $upload->store("private/fs/{$req->ticket_no}/{$kind}", 'local');

                FsRequestFile::create([
                    'request_id' => $req->id,
                    'uploader_id' => auth()->id(),
                    'kind' => $kind,
                    'path' => $path,
                    'original_name' => $upload->getClientOriginalName(),
                    'size' => $upload->getSize(),
                    'mime' => $upload->getMimeType(),
                    'is_final' => false,
                    'visibility' => 'client',
                ]);
            }
        }

        $this->extraFiles = [];
    }

    public function render()
    {
        $req = $this->req();
        $this->authorize('view', $req);

        $statusLabels = FinancialStatementRequest::statusLabels();
        $kinds = FinancialStatementRequest::fileKinds();

        $clientFiles = $req->files->where('visibility','client')->where('is_final',false);
        $finalFiles  = $req->files->where('visibility','client')->where('is_final',true);

        $messages = $req->messages
            ->filter(fn($m) => !$m->is_internal)
            ->sortBy('created_at');

        return view('livewire.fs.request-show', compact(
            'req','statusLabels','kinds','clientFiles','finalFiles','messages'
        ));
    }
}