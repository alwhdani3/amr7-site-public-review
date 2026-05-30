<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\BankAccount;
use Artesaos\SEOTools\Facades\SEOMeta;

class BankAccountsIndex extends Component
{
    public function mount(): void
    {
        SEOMeta::removeMeta('robots');
        SEOMeta::setRobots('index,follow');
    }

    public function render()
    {
        return view('livewire.bank-accounts-index', [
            'banks' => BankAccount::query()
                ->where('is_active', true)
                ->get(),
        ]);
    }
}
