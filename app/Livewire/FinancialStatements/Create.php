<?php

namespace App\Livewire\FinancialStatements;

use App\Models\FinancialStatementRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Component;
use App\Traits\HasSEO;

class Create extends Component
{
    use HasSEO;
    
    public int $currentStep = 1;

    public string $entity = '';
    public string $cr = '';
    public string $year = '';
    public string $notes = '';

    public string $name = '';
    public string $email = '';
    public string $mobile = '';


    public function mount(): void
    {
        $this->setSeo(
            __('create_request_title') . ' | ' . __('Amr 7'),
            __('create_request_subtitle')
        );

        $draft = session('fs_draft');
        if (is_array($draft)) {
            $this->name = $draft['name'] ?? '';
            $this->email = $draft['email'] ?? '';
            $this->mobile = $draft['mobile'] ?? '';
            $this->entity = $draft['entity'] ?? '';
            $this->cr = $draft['cr'] ?? '';
            $this->year = $draft['year'] ?? '';
        }
    }

public function nextStep(): void
{
    if ($this->currentStep === 1) {
        $this->validate([
            'entity' => 'required|string|min:2|max:190',
            'cr' => 'required|string|min:5|max:30',
            'year' => 'required|digits:4',
        ], [], [
            'entity' => __('label_entity_name'),
            'cr' => __('label_cr_number'),
            'year' => __('label_fiscal_year'),
        ]);
    }

    $this->currentStep++;
}

    public function previousStep(): void
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
        }
    }

    public function removeAttachment($index): void
    {
        array_splice($this->attachments, $index, 1);
    }

    private function generatePublicId(): string
    {
        do {
            $id = 'REQ-' . Str::upper(Str::random(13));
        } while (FinancialStatementRequest::query()->where('public_id', $id)->exists());

        return $id;
    }

public function submit(): void
{
    $this->validate([
        'entity' => 'required|string|min:2|max:190',
        'cr' => 'required|string|min:5|max:30',
        'year' => 'required|digits:4',
        'notes' => 'nullable|string|max:2000',
    ], [], [
        'entity' => __('label_entity_name'),
        'cr' => __('label_cr_number'),
        'year' => __('label_fiscal_year'),
        'notes' => __('label_notes_opt'),
    ]);

    if (!Auth::check()) {
        $this->validate([
            'name' => 'required|string|min:2|max:120',
            'email' => 'required|email|max:190',
            'mobile' => 'required|string|min:8|max:20',
        ], [], [
            'name' => __('name'),
            'email' => __('email_label'),
            'mobile' => __('mobile_number'),
        ]);

        $draft = [
            'name' => $this->name,
            'email' => strtolower(trim($this->email)),
            'mobile' => trim($this->mobile),
            'entity' => trim($this->entity),
            'cr' => trim($this->cr),
            'year' => trim($this->year),
            'notes' => trim($this->notes),
        ];

        session(['fs_draft' => $draft]);

        $exists = User::query()->where('email', $draft['email'])->exists();
        $redirect = route('financial-statements.create');

        session(['after_auth_redirect' => $redirect]);

        $this->redirectRoute($exists ? 'login' : 'register', navigate: true);
        return;
    }

    $req = FinancialStatementRequest::create([
        'public_id' => $this->generatePublicId(),
        'user_id' => Auth::id(),
        'status' => 'new',
        'company_name' => trim($this->entity),
        'cr_number' => trim($this->cr),
        'fiscal_year' => (int) $this->year,
        'client_notes' => trim($this->notes) ?: null,
    ]);

    session()->forget('fs_draft');

    $this->redirect(route('financial-statements.show', $req), navigate: false);
}
    public function render()
    {
        return view('livewire.financial-statements.create')
            ->layout('layouts.app');
    }
}
