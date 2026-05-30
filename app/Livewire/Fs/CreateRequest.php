<?php

namespace App\Livewire\Fs;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use App\Models\FinancialStatementRequest;
use App\Models\FsRequestFile;
use App\Traits\HasSEO; // 👈 1. استدعاء التريت

class CreateRequest extends Component
{
    use WithFileUploads;
    use HasSEO; // 👈 2. تفعيل التريت

    public string $company_name = '';
    public string $commercial_registration_no = '';
    public string $fiscal_year = '';

    public string $client_notes = '';

    // uploads
    public $files = []; // kind => array of uploads

    public function mount()
    {
        // ---------------------------------------------------------
        // 🛡️ إعدادات الخصوصية (Privacy Shield)
        // ---------------------------------------------------------
        
        // 1. عنوان الصفحة (لتحسين تجربة المستخدم UX فقط)
        $this->setSeo('إنشاء طلب قوائم مالية جديد');

        // 2. ⛔ حظر تام من الأرشفة (صفحة وظيفية خاصة)
        // نستخدم الواجهة المباشرة للمكتبة هنا لضمان عدم التتبع
        \SEOTools::metatags()->addMeta('robots', 'noindex, nofollow');
        // removed: opengraph disable()
        // removed: jsonLd disable()
    }

    public function rules(): array
    {
        $allowedMimes = 'pdf,xlsx,xls,csv,jpg,jpeg,png,webp';

        return [
            'company_name' => ['nullable','string','max:255'],
            'commercial_registration_no' => ['nullable','string','max:255'],
            'fiscal_year' => ['nullable','string','max:20'],

            'client_notes' => ['nullable','string','max:5000'],

            // required
            'files.articles_of_association.*' => ['required','file',"mimes:$allowedMimes",'max:20480'],
            'files.commercial_registration.*' => ['required','file',"mimes:$allowedMimes",'max:20480'],

            // optional
            'files.*.*' => ['file',"mimes:$allowedMimes",'max:20480'],
        ];
    }

    public function submit()
    {
        $this->validate();

        // تأكد الإجباري موجود فعلا
        if (empty($this->files['articles_of_association']) || empty($this->files['commercial_registration'])) {
            $this->addError('files', 'عقد التأسيس والسجل التجاري إلزامي.');
            return;
        }

        $req = FinancialStatementRequest::create([
            'user_id' => auth()->id(),
            'ticket_no' => FinancialStatementRequest::generateTicketNo(),
            'company_name' => $this->company_name ?: null,
            'commercial_registration_no' => $this->commercial_registration_no ?: null,
            'fiscal_year' => $this->fiscal_year ?: null,
            'status' => FinancialStatementRequest::STATUS_NEW,
            'submitted_at' => now(),
            'client_notes' => $this->client_notes ?: null,
        ]);

        // store files private
        foreach (($this->files ?? []) as $kind => $uploads) {
            foreach ((array)$uploads as $upload) {
                // ✅ ممتاز: استخدام مجلد private وحفظها local (وليس public) لحماية المستندات
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

        // أول رسالة (اختياري) من الملاحظات
        if (!empty(trim($req->client_notes ?? ''))) {
            $req->messages()->create([
                'user_id' => auth()->id(),
                'body' => $req->client_notes,
                'is_internal' => false,
            ]);
        }

        return redirect()->route('fs.show', $req->ticket_no);
    }

    public function render()
    {
        return view('livewire.fs.create-request', [
            'kinds' => FinancialStatementRequest::fileKinds(),
            'required' => FinancialStatementRequest::requiredKinds(),
        ]); 
        // 👈 تم حذف تمرير العنوان اليدوي layout([...])
    }
}