<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

/*
|--------------------------------------------------------------------------
| Guest Routes (صفحات الزوار)
| ملاحظة: Fortify يعالج الـ POST requests تلقائياً
| نحن هنا نعرّف فقط صفحات Volt المخصصة للعرض
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    
    Volt::route('register', 'pages.auth.register')
        ->name('register');

    Volt::route('login', 'pages.auth.login')
        ->name('login');

    Volt::route('forgot-password', 'pages.auth.forgot-password')
        ->name('password.request');

    Volt::route('reset-password/{token}', 'pages.auth.reset-password')
        ->name('password.reset');
});

/*
|--------------------------------------------------------------------------
| Authenticated Routes (صفحات الأعضاء)
| ملاحظة: verification.notice, verification.verify, password.confirm
| كلها مُعرَّفة تلقائياً بواسطة Laravel Fortify
|--------------------------------------------------------------------------
*/

// لا توجد حاجة لتعريفات إضافية هنا - Fortify يغطي كل شيء
