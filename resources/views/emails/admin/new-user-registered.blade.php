@extends('emails.layout')

@php
    $preheader = 'مستخدم جديد انضم للمنصة: ' . ($user->name ?? 'زائر');
@endphp

@section('content')
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
        <tr>
            <td style="padding-bottom: 24px;">
                <h1 style="margin: 0 0 16px; font-size: 20px; font-weight: bold; color: #111827; font-family: 'Tajawal', Arial, sans-serif;">
                    تسجيل مستخدم جديد 👤
                </h1>
                <p style="margin: 0; font-size: 15px; color: #4b5563; line-height: 1.6;">
                    مرحباً، تم إنشاء حساب جديد بنجاح في منصة <strong>{{ config('app.name') }}</strong>. إليك تفاصيل العميل:
                </p>
            </td>
        </tr>

        <tr>
            <td>
                <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" 
                       style="border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden; margin-bottom: 24px;">
                    
                    @php
                        // تجهيز نوع الحساب للعرض
                        $userType = match($user->type ?? '') {
                            'business' => 'منشأة 🏢',
                            'individual' => 'فرد 👤',
                            'admin' => 'مسؤول 🛡️',
                            default => $user->type ?? '-'
                        };

                        $fields = [
                            'الاسم'         => $user->name ?? '-',
                            'البريد'        => $user->email ?? '-',
                            'الجوال'        => $user->mobile ?? '-',
                            'نوع الحساب'    => $userType,
                            'تاريخ التسجيل' => optional($user->created_at)->format('Y-m-d H:i a'),
                        ];
                    @endphp

                    @foreach($fields as $label => $value)
                        <tr>
                            <td width="35%" style="padding: 14px 16px; background-color: #f9fafb; color: #6b7280; font-weight: bold; font-size: 13px; border-bottom: 1px solid #e5e7eb; white-space: nowrap;">
                                {{ $label }}
                            </td>
                            <td style="padding: 14px 16px; background-color: #ffffff; color: #111827; font-size: 14px; font-weight: 500; border-bottom: 1px solid #e5e7eb; {{ $label === 'الجوال' ? 'font-family: Tahoma, sans-serif;' : '' }}"
                                {{ $label === 'الجوال' ? 'dir=ltr align=right' : '' }}>
                                
                                @if($label === 'البريد')
                                    <a href="mailto:{{ $value }}" style="color: #1FA7A2; text-decoration: none; font-weight: bold;">{{ $value }}</a>
                                @else
                                    {{ $value }}
                                @endif

                            </td>
                        </tr>
                    @endforeach
                </table>
            </td>
        </tr>

        <tr>
            <td align="center" style="padding-top: 10px; padding-bottom: 30px;">
                <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                    <tr>
                        <td style="border-radius: 50px; background-color: #111827; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
                            <a href="{{ $adminUrl ?? url('/amr7/users/' . $user->id . '/edit') }}"
                               target="_blank"
                               style="display: inline-block; padding: 14px 36px; font-family: 'Tajawal', Arial, sans-serif; font-size: 15px; color: #ffffff; text-decoration: none; font-weight: bold; border-radius: 50px; background-color: #111827; border: 1px solid #111827;">
                                عرض ملف المستخدم
                                <span style="margin-right: 8px;">↗</span>
                            </a>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        <tr>
            <td align="center" style="border-top: 1px dashed #e5e7eb; padding-top: 20px;">
                <p style="margin: 0; font-size: 12px; color: #9ca3af;">
                    هذا إشعار آلي للنظام، لا تقم بالرد عليه.
                </p>
            </td>
        </tr>
    </table>
@endsection