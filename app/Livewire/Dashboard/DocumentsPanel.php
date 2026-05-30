<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use App\Models\Company;
use App\Models\CompanyDocument;

class DocumentsPanel extends Component
{
    use WithFileUploads;

    public bool $isAdmin = false;

    public ?Company $company = null;

    public bool $showEditCompany = false;
    public bool $showAddDocument = false;

    public string $name = '';
    public ?string $unified_number = null;
    public ?string $tax_number = null;
    public ?string $city = null;
    public ?string $address = null;

    public ?int $company_id = null;
    public string $type = 'cr';
    public ?string $document_number = null;
    public ?string $issue_date = null;
    public string $expiry_date = '';
    public $file;

    public function mount(): void
    {
        $user = auth()->user();

        $this->isAdmin = $user->isAdmin();
        $this->company = $user->companies()->first();

        if ($this->company) {
            $this->fillCompanyForm($this->company);
            $this->company_id = $this->company->id;
        }
    }

    private function fillCompanyForm(Company $company): void
    {
        $this->name = (string) ($company->name ?? '');
        $this->unified_number = $company->unified_number;
        $this->tax_number = $company->tax_number;
        $this->city = $company->city;
        $this->address = $company->address;
    }

    public function getDocumentsProperty()
    {
        if ($this->isAdmin) {
            return CompanyDocument::with('company')->latest()->get();
        }

        if (! $this->company) {
            return collect();
        }

        return CompanyDocument::where('company_id', $this->company->id)
            ->latest()
            ->get();
    }

    public function getStatsProperty(): array
    {
        $docs = $this->documents;

        return [
            'total'   => $docs->count(),
            'valid'   => $docs->where('status', 'valid')->count(),
            'warning' => $docs->where('status', 'warning')->count(),
            'expired' => $docs->where('status', 'expired')->count(),
        ];
    }

    public function openEditCompany(): void
    {
        if ($this->company) {
            $this->fillCompanyForm($this->company);
        }

        $this->showEditCompany = true;
    }

    public function saveCompany(): void
    {
        if (! $this->company) {
            $this->addError('company', 'لا توجد منشأة مرتبطة بهذا المستخدم.');
            return;
        }

        $this->validate([
            'name'           => ['required', 'string', 'max:255'],
            'unified_number' => ['nullable', 'string', 'max:50'],
            'tax_number'     => ['nullable', 'string', 'max:50'],
            'city'           => ['nullable', 'string', 'max:100'],
            'address'        => ['nullable', 'string', 'max:255'],
        ]);

        $this->company->update([
            'name'           => $this->name,
            'unified_number' => $this->unified_number,
            'tax_number'     => $this->tax_number,
            'city'           => $this->city,
            'address'        => $this->address,
        ]);

        $this->showEditCompany = false;
        session()->flash('success', 'تم تحديث بيانات المنشأة.');
    }

    public function openAddDocument(): void
    {
        $this->resetDocumentForm();
        $this->showAddDocument = true;
    }

    private function resetDocumentForm(): void
    {
        $this->type = 'cr';
        $this->document_number = null;
        $this->issue_date = null;
        $this->expiry_date = '';
        $this->file = null;

        $this->company_id = $this->isAdmin
            ? null
            : ($this->company?->id);
    }

    public function saveDocument(): void
    {
        // Empty string from the date input becomes null so "no expiry" works.
        $expiry = $this->expiry_date === '' ? null : $this->expiry_date;

        $rules = [
            'type'            => ['required', 'string', 'max:50'],
            'document_number' => ['nullable', 'string', 'max:100'],
            'issue_date'      => ['nullable', 'date'],
            // Expiry is optional: some documents (founding contracts, invoices,
            // generic contracts) do not expire. Form-side toggle clears the value.
            'expiry_date'     => ['nullable', 'date'],
            'file'            => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ];

        if ($this->isAdmin) {
            $rules['company_id'] = ['required', Rule::exists('companies', 'id')];
        } else {
            $rules['company_id'] = ['required', Rule::in([$this->company?->id])];
        }

        $this->validate($rules);

        $path = $this->file->store('companies/docs/' . $this->company_id, 'private');

        CompanyDocument::create([
            'company_id'      => $this->company_id,
            'type'            => $this->type,
            'document_number' => $this->document_number,
            'issue_date'      => $this->issue_date,
            'expiry_date'     => $expiry,
            'file_path'       => $path,
            'status'          => 'valid',
        ]);

        $this->showAddDocument = false;
        session()->flash('success', 'تم إضافة الوثيقة بنجاح.');
    }

    public function deleteDocument($id): void
    {
        $query = $this->isAdmin
            ? CompanyDocument::query()
            : CompanyDocument::where('company_id', $this->company?->id);

        $doc = $query->findOrFail((int) $id);

        if ($doc->file_path) {
            Storage::disk('private')->delete($doc->file_path);
        }

        $doc->delete();

        session()->flash('success', 'تم حذف الوثيقة بنجاح.');
    }

    public function render()
    {
        return view('livewire.dashboard.documents-panel');
    }
}