<?php

namespace App\Livewire\FinancialStatements;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\FinancialStatementRequest;
use App\Traits\HasSEO;

class Portal extends Component
{
    use HasSEO;

    public string $tracking = '';

    public function mount()
    {
        $this->setSeo(
            __('fs_portal_seo_title'),
            __('fs_portal_seo_desc')
        );
    }

    public function go(): void
    {
        $t = trim($this->tracking);

        if ($t === '') {
            $this->addError('tracking', __('tracking_required_error'));
            return;
        }

        $req = FinancialStatementRequest::query()
            ->where('public_id', $t)
            ->first();

        if (!$req) {
            $this->addError('tracking', __('tracking_not_found_error'));
            return;
        }

        if (Auth::check()) {
            $this->redirectRoute('financial-statements.show', $req->public_id, navigate: true);
            return;
        }

        $redirect = route('financial-statements.show', $req->public_id);
        session(['after_auth_redirect' => $redirect]);
        
        $this->redirectRoute('login', navigate: true);
    }

    public function render()
    {
        return view('livewire.financial-statements.portal')
            ->layout('layouts.app');
    }
}
