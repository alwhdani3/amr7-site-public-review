@extends('emails.layout')

@section('content')
    <h1 class="email-title">وثيقة جديدة مرفوعة</h1>
    <p class="email-lead">تم رفع وثيقة جديدة في بوابة آمر سبعة. راجع بيانات الوثيقة وحالة التحليل الذكي عند توفرها.</p>

    @include('emails.partials.detail-card', ['items' => [
        ['label' => 'نوع الوثيقة', 'value' => $documentType ?? null],
        ['label' => 'المنشأة', 'value' => $companyName ?? null],
        ['label' => 'تاريخ الانتهاء', 'value' => $expiresAt ?? null],
        ['label' => 'حالة التحليل الذكي', 'value' => $aiStatus ?? null],
    ]])

    @include('emails.partials.button', ['url' => $url ?? config('app.url'), 'label' => 'مراجعة الوثيقة'])
@endsection
