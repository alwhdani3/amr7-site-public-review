@extends('emails.layout')

@section('content')
    <h1 class="email-title">تذكرة جديدة - شركة آمر سبعة لحلول الأعمال</h1>
    <p class="email-lead">تم إنشاء هذه التذكرة من بوابة آمر سبعة وتحتاج إلى متابعة من فريق الإدارة.</p>

    @include('emails.partials.detail-card', ['items' => [
        ['label' => 'اسم العميل', 'value' => $customerName ?? null],
        ['label' => 'الشركة', 'value' => $companyName ?? null],
        ['label' => 'الموضوع', 'value' => $subject ?? null],
        ['label' => 'رقم التذكرة', 'value' => $ticketNumber ?? null],
        ['label' => 'الأولوية', 'value' => $priority ?? null],
        ['label' => 'الحالة', 'value' => $status ?? null],
    ]])

    @include('emails.partials.button', ['url' => $url ?? config('app.url'), 'label' => 'فتح التذكرة'])
@endsection
