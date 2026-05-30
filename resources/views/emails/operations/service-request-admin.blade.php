@extends('emails.layout')

@section('content')
    <h1 class="email-title">طلب خدمة جديد</h1>
    <p class="email-lead">وصل طلب خدمة جديد من موقع آمر سبعة. راجع بيانات الطلب ثم تابع العميل من لوحة التحكم.</p>

    @include('emails.partials.detail-card', ['items' => [
        ['label' => 'الاسم', 'value' => $name ?? null],
        ['label' => 'الجوال', 'value' => $phone ?? null],
        ['label' => 'البريد', 'value' => $email ?? null],
        ['label' => 'الخدمة المختارة', 'value' => $serviceName ?? null],
        ['label' => 'الموضوع', 'value' => $subject ?? null],
        ['label' => 'الرسالة', 'value' => $requestMessage ?? null],
    ]])

    @include('emails.partials.button', ['url' => $url ?? config('app.url'), 'label' => 'فتح الطلب في لوحة التحكم'])
@endsection
