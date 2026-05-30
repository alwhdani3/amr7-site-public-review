@extends('emails.layout')

@php
    $preheader = __('email_verify_preheader');
@endphp

@section('content')
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
        <tr>
            <td style="padding-bottom: 24px;">
                <h1 style="margin: 0 0 16px; font-size: 20px; font-weight: bold; color: #111827; font-family: 'Tajawal', Arial, sans-serif;">
                    {{ __('email_verify_heading') }}
                </h1>
                <p style="margin: 0; font-size: 15px; color: #4b5563; line-height: 1.6;">
                    {!! __('email_verify_intro', ['name' => '<span style="color: #111827; font-weight: bold;">' . ($user->name ?? __('valued_customer')) . '</span>']) !!}
                    <br>
                    {!! __('email_verify_instruction', ['app_name' => '<strong>' . config('app.name') . '</strong>']) !!}
                </p>
            </td>
        </tr>

        <tr>
            <td align="center" style="padding-bottom: 30px;">
                <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                    <tr>
                        <td style="border-radius: 50px; background-color: #1FA7A2; box-shadow: 0 4px 6px rgba(35, 109, 111, 0.2);">
                            <a href="{{ $url }}"
                               target="_blank"
                               style="display: inline-block; padding: 14px 40px; font-family: 'Tajawal', Arial, sans-serif; font-size: 16px; color: #ffffff; text-decoration: none; font-weight: bold; border-radius: 50px; background-color: #1FA7A2; border: 1px solid #1FA7A2;">
                                {{ __('email_btn_activate_account') }}
                            </a>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        <tr>
            <td style="padding-bottom: 24px;">
                <p style="margin: 0; font-size: 14px; color: #6b7280; line-height: 1.6; background-color: #fff1f2; border: 1px solid #fecdd3; padding: 12px; border-radius: 8px; color: #9f1239;">
                    🛡️ {{ __('email_verify_ignore_safety') }}
                </p>
            </td>
        </tr>

        <tr>
            <td style="padding-top: 24px; border-top: 1px solid #e5e7eb;">
                <p style="margin: 0 0 10px; font-size: 13px; color: #6b7280;">
                    {{ __('email_verify_trouble_text') }}
                </p>
                <div style="background-color: #f8fafc; padding: 12px 16px; border-radius: 8px; border: 1px dashed #cbd5e1; font-size: 12px; color: #1FA7A2; word-break: break-all; font-family: Tahoma, sans-serif;" dir="ltr">
                    <a href="{{ $url }}" style="color: #1FA7A2; text-decoration: none;">{{ $url }}</a>
                </div>
            </td>
        </tr>
    </table>
@endsection