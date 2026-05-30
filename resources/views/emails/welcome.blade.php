@extends('emails.layout')

@php
    $preheader = __('email_welcome_preheader');
@endphp

@section('content')
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
        <tr>
            <td style="padding: 0 0 24px 0;">
                <h1 style="margin: 0 0 16px; font-size: 20px; font-weight: bold; color: #111827; font-family: 'Tajawal', Arial, sans-serif;">
                    {{ __('email_welcome_title') }}
                </h1>
                <p style="margin: 0; font-size: 15px; line-height: 1.6; color: #4b5563;">
                    {!! __('email_welcome_greeting', ['name' => '<span style="color: #111827; font-weight: bold;">' . ($user->name ?? __('valued_customer')) . '</span>']) !!}
                    <br>
                    {{ __('email_welcome_body', ['app_name' => config('app.name')]) }}
                </p>
            </td>
        </tr>

        <tr>
            <td>
                <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" 
                       style="border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden; margin-bottom: 24px;">
                    
                    <tr>
                        <td colspan="2" style="background-color: #f3f4f6; padding: 12px 20px; border-bottom: 1px solid #e5e7eb; font-size: 14px; font-weight: bold; color: #374151;">
                            {{ __('email_account_details_title') }}
                        </td>
                    </tr>

                    @php
                        $fields = [
                            'email_label_name'   => $user->name ?? '-',
                            'email_label_email'  => $user->email ?? '-',
                            'email_label_mobile' => $user->mobile ?? null,
                        ];
                    @endphp

                    @foreach($fields as $key => $value)
                        @if($value)
                            <tr>
                                <td width="35%" style="padding: 16px; font-size: 14px; font-weight: bold; color: #4b5563; background-color: #f9fafb; border-bottom: 1px solid #e5e7eb; white-space: nowrap;">
                                    {{ __($key) }}
                                </td>
                                <td style="padding: 16px; font-size: 14px; font-weight: 500; color: #111827; border-bottom: 1px solid #e5e7eb; {{ $key === 'email_label_mobile' ? 'font-family: Tahoma, sans-serif;' : '' }}"
                                    {{ $key === 'email_label_mobile' ? 'dir=ltr align=right' : '' }}>
                                    {{ $value }}
                                </td>
                            </tr>
                        @endif
                    @endforeach
                </table>
            </td>
        </tr>

        <tr>
            <td align="center" style="padding-bottom: 30px;">
                <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                    <tr>
                        <td style="border-radius: 50px; background-color: #1FA7A2; box-shadow: 0 4px 6px rgba(35, 109, 111, 0.2);">
                            <a href="{{ $loginUrl ?? url('/login') }}"
                               target="_blank"
                               style="display: inline-block; padding: 14px 36px; font-family: 'Tajawal', Arial, sans-serif; font-size: 16px; color: #ffffff; text-decoration: none; font-weight: bold; border-radius: 50px; background-color: #1FA7A2; border: 1px solid #1FA7A2;">
                                {{ __('email_btn_login') }}
                            </a>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        <tr>
            <td align="center" style="border-top: 1px solid #e5e7eb; padding-top: 24px;">
                <p style="margin: 0; font-size: 13px; color: #6b7280; line-height: 1.5;">
                    {{ __('email_help_text') }} 
                    <a href="{{ $contactUrl ?? url('/contact-us') }}" style="color: #1FA7A2; text-decoration: none; font-weight: bold;">
                        {{ __('email_contact_us') }}
                    </a>
                </p>
            </td>
        </tr>
    </table>
@endsection