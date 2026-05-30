<?php

namespace App\Providers;

use App\Models\CompanyDocument;
use App\Models\FinancialStatementRequest;
use App\Models\Post;
use App\Models\ServiceRequest;
use App\Models\Task;
use App\Models\Ticket;
use App\Policies\CompanyDocumentPolicy;
use App\Policies\FinancialStatementRequestPolicy;
use App\Policies\PostPolicy;
use App\Policies\ServiceRequestPolicy;
use App\Policies\TaskPolicy;
use App\Policies\TicketPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Post::class                      => PostPolicy::class,
        Ticket::class                    => TicketPolicy::class,
        FinancialStatementRequest::class => FinancialStatementRequestPolicy::class,
        // P1.2 — سياسات جديدة
        Task::class                      => TaskPolicy::class,
        ServiceRequest::class            => ServiceRequestPolicy::class,
        CompanyDocument::class           => CompanyDocumentPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        /**
         * ✅ Super Admin يتجاوز كل الـ Policies والـ Gates
         * Spatie يضيف دوره من قاعدة البيانات
         */
        Gate::before(function ($user, $ability) {
            if ($user->isAdmin()) {
                return true;
            }
        });

        // =====================================================
        // Gates إضافية للصلاحيات خارج Policies
        // =====================================================

        // وصول لوحة التحكم (Filament)
        Gate::define('access-admin-panel', function ($user) {
            return $user->isAdmin() || $user->isAgent();
        });

        // إدارة الموظفين
        Gate::define('manage-employees', function ($user) {
            return $user->hasPermissionTo('employees.view');
        });

        // إدارة الأدوار والصلاحيات
        Gate::define('manage-roles', function ($user) {
            return $user->hasPermissionTo('roles.manage');
        });

        // تصدير التقارير
        Gate::define('export-reports', function ($user) {
            return $user->hasPermissionTo('reports.export');
        });
    }
}
