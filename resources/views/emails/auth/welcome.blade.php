@extends('emails.layout')

@section('content')
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
        <tr>
            <td style="padding-bottom: 24px;">
                <h1 style="margin: 0 0 16px; font-size: 20px; font-weight: bold; color: #111827; font-family: 'Tajawal', Arial, sans-serif;">
                    {{ __('email_welcome_title_v2', ['app_name' => config('app.name')]) }}
                </h1>
                <p style="margin: 0; font-size: 15px; color: #4b5563; line-height: 1.6;">
                    {!! __('email_welcome_greeting', ['name' => '<span style="color: #111827; font-weight: bold;">' . ($user->name ?? __('valued_customer')) . '</span>']) !!}
                    <br>
                    {{ __('email_welcome_success_msg') }}
                </p>
            </td>
        </tr>

        <tr>
            <td>
                <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" 
                       style="border: 1px solid #e2e8f0; border-radius: 12px; background-color: #f8fafc; overflow: hidden; margin-bottom: 24px;">
                    
                    <tr>
                        <td colspan="2" style="padding: 16px 20px 8px; font-size: 12px; font-weight: bold; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">
                            {{ __('email_account_details_header') }}
                        </td>
                    </tr>

                    <tr>
                        <td width="30%" style="padding: 6px 20px; font-size: 13px; color: #475569;">
                            {{ __('email_label_name') }}
                        </td>
                        <td style="padding: 6px 20px; font-size: 13px; color: #0f172a; font-weight: 600;">
                            {{ $user->name ?? '-' }}
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 6px 20px; font-size: 13px; color: #475569;">
                            {{ __('email_label_email') }}
                        </td>
                        <td style="padding: 6px 20px; font-size: 13px; color: #0f172a; font-weight: 600;">
                            {{ $user->email ?? '-' }}
                        </td>
                    </tr>

                    @if(!empty($user->mobile))
                        <tr>
                            <td style="padding: 6px 20px 16px; font-size: 13px; color: #475569;">
                                {{ __('email_label_mobile') }}
                            </td>
                            <td style="padding: 6px 20px 16px; font-size: 13px; color: #0f172a; font-weight: 600; font-family: Tahoma, sans-serif;" dir="ltr" align="right">
                                {{ $user->mobile }}
                            </td>
                        </tr>
                    @else
                        <tr><td colspan="2" height="10"></td></tr>
                    @endif
                </table>
            </td>
        </tr>

        <tr>
            <td align="center" style="padding-bottom: 30px;">
                <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                    <tr>
                        <td style="border-radius: 50px; background-color: #1FA7A2; box-shadow: 0 4px 6px rgba(35, 109, 111, 0.2);">
                            <a href="{{ $url ?? url('/login') }}"
                               target="_blank"
                               style="display: inline-block; padding: 14px 40px; font-family: 'Tajawal', Arial, sans-serif; font-size: 16px; color: #ffffff; text-decoration: none; font-weight: bold; border-radius: 50px; background-color: #1FA7A2; border: 1px solid #1FA7A2;">
                                {{ __('email_btn_login_short') }}
                            </a>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        <tr>
            <td align="center" style="padding-top: 10px; border-top: 1px dashed #e2e8f0;">
                <p style="margin: 10px 0 0; font-size: 12px; color: #64748b; line-height: 1.6;">
                    {{ __('email_security_alert') }}
                </p>
            </td>
        </tr>
    </table>
@endsection