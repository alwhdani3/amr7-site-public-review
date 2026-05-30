@extends('emails.layout')

@section('content')
    <h1 class="email-title">نتيجة تشغيل آلي</h1>
    <p class="email-lead">هذا إشعار مختصر بحالة تشغيل أحد workflows المرتبطة بعمليات آمر سبعة.</p>

    @include('emails.partials.detail-card', ['items' => [
        ['label' => 'اسم workflow', 'value' => $workflowName ?? null],
        ['label' => 'النوع', 'value' => $category ?? null],
        ['label' => 'الحالة', 'value' => $status ?? null],
        ['label' => 'وقت التشغيل', 'value' => $runAt ?? null],
        ['label' => 'ملخص السجل', 'value' => $summary ?? null],
    ]])

    @include('emails.partials.button', ['url' => $url ?? config('app.url'), 'label' => 'عرض السجل داخل لوحة آمر سبعة'])
@endsection
