@extends('emails.layout')

@section('content')
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
        <tr>
            <td style="padding-bottom: 24px;">
                <h1 style="margin: 0 0 16px; font-size: 20px; font-weight: bold; color: #111827; font-family: 'Tajawal', Arial, sans-serif;">
                    {{ $title ?? __('email_contact_title') }}
                </h1>
                <p style="margin: 0; font-size: 15px; color: #4b5563; line-height: 1.6;">
                    {{ __('email_contact_intro') }}
                </p>
            </td>
        </tr>

        <tr>
            <td>
                <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" 
                       style="border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden; margin-bottom: 24px;">
                    
                    <tr>
                        <td width="30%" style="padding: 14px 16px; background-color: #f9fafb; color: #6b7280; font-weight: bold; font-size: 13px; border-bottom: 1px solid #e5e7eb; white-space: nowrap;">
                            {{ __('email_label_name') }}
                        </td>
                        <td style="padding: 14px 16px; background-color: #ffffff; color: #111827; font-weight: 600; font-size: 14px; border-bottom: 1px solid #e5e7eb;">
                            {{ $formData['name'] ?? __('not_provided') }}
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 14px 16px; background-color: #f9fafb; color: #6b7280; font-weight: bold; font-size: 13px; border-bottom: 1px solid #e5e7eb;">
                            {{ __('email_label_phone') }}
                        </td>
                        <td style="padding: 14px 16px; background-color: #ffffff; color: #111827; font-size: 14px; border-bottom: 1px solid #e5e7eb; font-family: Tahoma, sans-serif;" dir="ltr" align="right">
                            <a href="tel:{{ $formData['phone'] ?? '' }}" style="color:#111827; text-decoration:none;">
                                {{ $formData['phone'] ?? '-' }}
                            </a>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 14px 16px; background-color: #f9fafb; color: #6b7280; font-weight: bold; font-size: 13px; border-bottom: 1px solid #e5e7eb;">
                            {{ __('email_label_email') }}
                        </td>
                        <td style="padding: 14px 16px; background-color: #ffffff; font-size: 14px; border-bottom: 1px solid #e5e7eb;">
                            <a href="mailto:{{ $formData['email'] }}" style="color: #1FA7A2; text-decoration: none; font-weight: bold;">
                                {{ $formData['email'] ?? '-' }}
                            </a>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 14px 16px; background-color: #f9fafb; color: #6b7280; font-weight: bold; font-size: 13px; border-bottom: 1px solid #e5e7eb;">
                            {{ __('email_label_subject') }}
                        </td>
                        <td style="padding: 14px 16px; background-color: #ffffff; color: #111827; font-size: 14px; border-bottom: 1px solid #e5e7eb;">
                            {{ $formData['subject'] ?? __('no_subject') }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        <tr>
            <td style="padding-bottom: 24px;">
                <p style="margin: 0 0 10px; font-size: 14px; font-weight: bold; color: #374151;">
                    {{ __('email_label_message') }}
                </p>
                <div style="background-color: #f3f4f6; border-radius: 8px; padding: 20px; color: #4b5563; line-height: 1.8; font-size: 14px; border: 1px solid #e5e7eb;">
                    {!! nl2br(e($formData['message'] ?? '-')) !!}
                </div>
            </td>
        </tr>

        @if(!empty($formData['email']))
        <tr>
            <td align="center" style="padding-top: 20px; border-top: 1px dashed #e5e7eb;">
                <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                    <tr>
                        <td style="border-radius: 50px; background-color: #ffffff; border: 1px solid #1FA7A2; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                            <a href="mailto:{{ $formData['email'] }}?subject=Re: {{ $formData['subject'] ?? '' }}"
                               target="_blank"
                               style="display: inline-block; padding: 12px 30px; font-family: 'Tajawal', Arial, sans-serif; font-size: 14px; color: #1FA7A2; text-decoration: none; font-weight: bold; border-radius: 50px;">
                                {{ __('email_btn_reply') }} 
                                <span style="margin-right: 5px;">✉️</span>
                            </a>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        @endif
    </table>
@endsection