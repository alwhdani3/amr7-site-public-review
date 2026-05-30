<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Artesaos\SEOTools\Facades\SEOTools;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CompanySelectionController extends Controller
{
    public function index(Request $request)
    {
        if (class_exists(SEOTools::class)) {
            SEOTools::setTitle('اختيار المنشأة - شركة آمر سبعة لحلول الأعمال');
            SEOTools::metatags()->setRobots('noindex,nofollow');
            SEOTools::setCanonical(url()->current());
        }

        $user = $request->user();

        $companies = $user->companies()
            ->select('companies.*')
            ->orderByDesc('company_user.is_active')
            ->orderByDesc('companies.created_at')
            ->get();

        return view('company.select', [
            'companies'   => $companies,
            'noCompanies' => $companies->isEmpty(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate(
            ['company_id' => ['required', 'integer', 'exists:companies,id']],
            ['company_id.required' => 'يرجى اختيار منشأة للمتابعة إلى لوحة التحكم.'],
            ['company_id' => 'المنشأة']
        );

        $companyId = (int) $request->company_id;
        $user = $request->user();

        abort_unless($user->companies()->whereKey($companyId)->exists(), 403);

        $this->activateCompanyInDb((int) $user->id, $companyId);

        session(['active_company_id' => $companyId]);

        return redirect()->to(session()->pull('url.intended', route('dashboard')));
    }

    public function switch(Request $request, Company $company)
    {
        $user = $request->user();

        abort_unless($user->companies()->whereKey($company->id)->exists(), 403);

        $this->activateCompanyInDb((int) $user->id, (int) $company->id);

        session(['active_company_id' => (int) $company->id]);

        return redirect()->to(session()->pull('url.intended', route('dashboard')));
    }

    private function activateCompanyInDb(int $userId, int $companyId): void
    {
        DB::transaction(function () use ($userId, $companyId) {
            DB::table('company_user')
                ->where('user_id', $userId)
                ->update([
                    'is_active' => false,
                    'updated_at' => now(),
                ]);

            $updated = DB::table('company_user')
                ->where('user_id', $userId)
                ->where('company_id', $companyId)
                ->update([
                    'is_active' => true,
                    'updated_at' => now(),
                ]);

            if ($updated === 0) {
                throw new \RuntimeException('تعذر تفعيل المنشأة المحددة لهذا المستخدم.');
            }
        });
    }
}