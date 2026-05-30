<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class CompanyController extends Controller
{
    /**
     * تحديث بيانات المنشأة
     */
    public function update(Request $request, Company $company)
    {
        if (! $this->canUpdateCompany($company)) {
            abort(403, 'غير مصرح لك بتعديل بيانات هذه المنشأة.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'unified_number' => 'nullable|numeric',
            'tax_number' => 'nullable|string',
            'cr_number' => 'nullable|string',
            'city' => 'nullable|string',
            'entity_size' => 'nullable|string',
        ]);

        $company->update($validated);

        return back()->with('success', 'تم تحديث بيانات المنشأة بنجاح.');
    }

    private function canUpdateCompany(Company $company): bool
    {
        $user = Auth::user();
        if (! $user) {
            return false;
        }

        if (method_exists($user, 'hasBackofficeAccess') && $user->hasBackofficeAccess()) {
            return true;
        }

        if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
            return true;
        }

        $membership = $company->users()
            ->where('users.id', $user->id)
            ->wherePivotIn('role', ['admin', 'owner']);

        if (Schema::hasColumn('company_user', 'is_active')) {
            $membership->wherePivot('is_active', true);
        }

        return $membership->exists();
    }
}
