@extends('emails.layout')

@section('content')
    <h1 class="email-title">تحديث على التذكرة</h1>
    <p class="email-lead">يوجد رد جديد على محادثة الدعم داخل بوابة آمر سبعة.</p>

    @include('emails.partials.detail-card', ['items' => [
        ['label' => 'رقم التذكرة', 'value' => $ticketNumber ?? null],
        ['label' => 'الموضوع', 'value' => $subject ?? null],
        ['label' => 'آخر رد', 'value' => $replyPreview ?? null],
        ['label' => 'الحالة', 'value' => $status ?? null],
    ]])

    @include('emails.partials.button', ['url' => $url ?? config('app.url'), 'label' => 'عرض المحادثة'])
@endsection
