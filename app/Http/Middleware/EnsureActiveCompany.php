<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureActiveCompany
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (! $user) {
            return redirect()->route('login');
        }

        // استثناء صفحات اختيار/تبديل المنشأة حتى ما يصير loop
        if ($request->routeIs('company.select', 'company.select.store', 'company.switch')) {
            return $next($request);
        }

        // (اختياري) دعم company_id بالـ query
        $qid = $request->query('company_id');
        if ($qid && $user->companies()
            ->whereKey((int) $qid)
            ->wherePivot('is_active', true)
            ->exists()) {
            session(['active_company_id' => (int) $qid]);
        }

        $activeId = session('active_company_id');

        // إذا ما فيه منشأة مختارة: خزّن الوجهة وروّح للاختيار
        if (! $activeId) {
            session(['url.intended' => $request->fullUrl()]);
            return redirect()->route('company.select');
        }

        // تحقق أن المنشأة فعلاً للمستخدم وأن العضوية مفعّلة
        if (! $user->companies()
            ->whereKey((int) $activeId)
            ->wherePivot('is_active', true)
            ->exists()) {
            session()->forget('active_company_id');
            session(['url.intended' => $request->fullUrl()]);
            return redirect()->route('company.select');
        }

        return $next($request);
    }
}
