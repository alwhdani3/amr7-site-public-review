@extends('emails.layout')

@section('content')
    <h1 class="email-title">طلب قائمة مالية جديد</h1>
    <p class="email-lead">تم تسجيل طلب جديد للقوائم المالية ويحتاج إلى مراجعة بيانات المنشأة وحالة الطلب.</p>

    @include('emails.partials.detail-card', ['items' => [
        ['label' => 'اسم المنشأة', 'value' => $companyName ?? null],
        ['label' => 'رقم السجل', 'value' => $crNumber ?? null],
        ['label' => 'السنة المالية', 'value' => $fiscalYear ?? null],
        ['label' => 'رقم التتبع', 'value' => $trackingNumber ?? null],
        ['label' => 'الحالة', 'value' => $status ?? null],
    ]])

    @include('emails.partials.button', ['url' => $url ?? config('app.url'), 'label' => 'فتح الطلب'])
@endsection
