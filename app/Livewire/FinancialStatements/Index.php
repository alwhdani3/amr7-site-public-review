<?php

namespace App\Livewire\FinancialStatements;

use App\Models\FinancialStatementRequest;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use App\Traits\HasSEO;

class Index extends Component
{
    use WithPagination, HasSEO;

    public string $search = '';
    public string $status = 'all';

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingStatus(): void { $this->resetPage(); }

    public function getIsStaffProperty(): bool
    {
        $u = Auth::user();
        if (! $u) return false;

        $role = strtolower((string) ($u->role ?? ''));
        return (bool) $u->is_admin || in_array($role, ['admin','agent','staff','employee','support'], true);
    }

    public function statuses(): array
    {
        return [
            'all'             => __('all_statuses'),
            'new'             => __('status_new'),
            'waiting_docs'    => __('status_waiting_docs'),
            'in_review'       => __('status_in_review'),
            'client_approval' => __('status_client_approval'),
            'moc_approval'    => __('status_moc_approval'),
            'completed'       => __('status_completed'),
            'closed'          => __('status_closed'),
            'cancelled'       => __('cancelled'),
        ];
    }

    public function trackRequest(): void
    {
        $this->validate([
            'search' => 'required|string|min:3',
        ]);

        $req = FinancialStatementRequest::where('public_id', trim($this->search))->first();

        if ($req) {
            $this->redirectRoute('financial-statements.show', $req->public_id, navigate: true);
        } else {
            $this->addError('search', __('no_requests_found'));
        }
    }

    public function render()
    {
        $this->setSeo(
            __('fs_portal_title') . ' | ' . __('Amr 7'),
            __('fs_portal_subtitle')
        );

        $q = FinancialStatementRequest::query()->latest();

        if (! $this->isStaff) {
            $q->where('user_id', Auth::id());
        }

        if ($this->status !== 'all') {
            $q->where('status', $this->status);
        }

        $s = trim($this->search);
        if ($s !== '') {
            $q->where(function ($qq) use ($s) {
                $qq->where('public_id', 'like', "%{$s}%")
                   ->orWhere('company_name', 'like', "%{$s}%")
                   ->orWhere('cr_number', 'like', "%{$s}%");
            });
        }

        return view('livewire.financial-statements.index', [
            'requests' => $q->paginate(12),
        ])->layout('layouts.app');
    }
}