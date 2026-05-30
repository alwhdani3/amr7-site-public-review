<?php

namespace App\Livewire\Fs;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\FinancialStatementRequest;
use Artesaos\SEOTools\Facades\SEOTools; // 👈 1. استدعاء المكتبة

class RequestsList extends Component
{
    use WithPagination;

    public string $status = 'all';

    public function mount()
    {
        // ---------------------------------------------------------
        // 🛡️ إعدادات الخصوصية (Privacy Shield)
        // ---------------------------------------------------------
        
        // 1. عنوان الصفحة (للمستخدم فقط)
        SEOTools::setTitle('قائمة طلباتي - القوائم المالية');

        // 2. ⛔ حظر تام من الأرشفة (صفحة خاصة بالأعضاء)
        SEOTools::metatags()->addMeta('robots', 'noindex, nofollow');
        // removed: opengraph disable()
        // removed: jsonLd disable()
    }

    public function render()
    {
        $q = FinancialStatementRequest::query()
            ->where('user_id', auth()->id())
            ->latest();

        if ($this->status !== 'all') {
            $q->where('status', $this->status);
        }

        return view('livewire.fs.requests-list', [
            'requests' => $q->paginate(10),
            'statusLabels' => FinancialStatementRequest::statusLabels(),
        ]);
    }
}