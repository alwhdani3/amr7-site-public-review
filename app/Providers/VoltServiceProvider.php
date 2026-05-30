<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Livewire\Volt\Volt;

class VoltServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // تسجيل المسارات التي سيبحث فيها Volt عن المكونات
        Volt::mount([
            // المسار الافتراضي لمكونات Livewire
            config('livewire.view_path', resource_path('views/livewire')),
            
            // مسار الصفحات (مثالي للاستخدام مع Laravel Folio)
            resource_path('views/pages'),
        ]);
    }
}