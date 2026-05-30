{{ $title ?? 'إشعار من شركة آمر سبعة لحلول الأعمال' }}

{{ $intro ?? 'يوجد تحديث جديد داخل بوابة آمر سبعة.' }}

@foreach(($lines ?? []) as $label => $value)
{{ $label }}: {{ filled($value) ? $value : 'غير متوفر' }}
@endforeach

@if(! empty($url))
{{ $actionLabel ?? 'فتح الرابط' }}:
{{ $url }}
@endif

شركة آمر سبعة لحلول الأعمال
الرياض، المملكة العربية السعودية
info@amr-7.sa
amr-7.sa
920017083
0505336956
