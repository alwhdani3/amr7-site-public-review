@extends('emails.layout')

@php
    $preheader = __('email_preheader_new_contact', ['name' => $data['name'] ?? __('guest')]);
@endphp

@section('content')
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
        <tr>
            <td style="padding-bottom: 24px;">
                <h1 style="margin: 0 0 16px; font-size: 20px; font-weight: bold; color: #111827; font-family: 'Tajawal', Arial, sans-serif;">
                    {{ __('admin_notify_title') }}
                </h1>
                <p style="margin: 0; font-size: 15px; color: #4b5563; line-height: 1.6;">
                    {{ __('admin_notify_intro') }}
                </p>
            </td>
        </tr>

        <tr>
            <td>
                <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" 
                       style="border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden; margin-bottom: 24px;">
                    
                    @php
                        $rows = [
                            'email_label_name'    => $data['name'] ?? __('not_provided'),
                            'email_label_company' => $data['company'] ?? null,
                            'email_label_phone'   => $data['phone'] ?? '-',
                            'email_label_email'   => $data['email'] ?? '-',
                            'email_label_subject' => $data['subject'] ?? __('no_subject'),
                        ];
                    @endphp

                    @foreach($rows as $key => $value)
                        @if($value)
                            <tr>
                                <td width="35%" style="padding: 14px 16px; background-color: #f9fafb; color: #6b7280; font-weight: bold; font-size: 13px; border-bottom: 1px solid #e5e7eb; white-space: nowrap;">
                                    {{ __($key) }}
                                </td>
                                <td style="padding: 14px 16px; background-color: #ffffff; color: #1f2937; font-size: 14px; font-weight: 500; border-bottom: 1px solid #e5e7eb; {{ $key === 'email_label_phone' ? 'font-family: Tahoma, sans-serif;' : '' }}"
                                    {{ $key === 'email_label_phone' ? 'dir=ltr align=right' : '' }}>
                                    
                                    @if($key === 'email_label_email' && filter_var($value, FILTER_VALIDATE_EMAIL))
                                        <a href="mailto:{{ $value }}" style="color: #1FA7A2; text-decoration: none; font-weight: bold;">{{ $value }}</a>
                                    @else
                                        {{ $value }}
                                    @endif

                                </td>
                            </tr>
                        @endif
                    @endforeach
                </table>
            </td>
        </tr>

        <tr>
            <td style="padding-bottom: 24px;">
                <p style="margin: 0 0 10px; font-size: 14px; font-weight: bold; color: #374151;">
                    {{ __('email_label_message_body') }}
                </p>
                <div style="background-color: #f3f4f6; border-radius: 8px; padding: 20px; color: #4b5563; line-height: 1.8; font-size: 14px; border: 1px solid #e5e7eb;">
                    {!! nl2br(e($data['message'] ?? __('no_message_content'))) !!}
                </div>
            </td>
        </tr>

        @if(!empty($data['email']))
        <tr>
            <td align="center" style="padding-top: 20px; border-top: 1px dashed #e5e7eb;">
                <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                    <tr>
                        <td style="border-radius: 50px; background-color: #ffffff; border: 1px solid #1FA7A2; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                            <a href="mailto:{{ $data['email'] }}?subject=Re: {{ $data['subject'] ?? '' }}"
                               target="_blank"
                               style="display: inline-block; padding: 12px 30px; font-family: 'Tajawal', Arial, sans-serif; font-size: 14px; color: #1FA7A2; text-decoration: none; font-weight: bold; border-radius: 50px;">
                                {{ __('email_btn_reply_sender') }} 
                                <span style="margin-right: 5px;">↩️</span>
                            </a>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        @endif
    </table>
@endsection