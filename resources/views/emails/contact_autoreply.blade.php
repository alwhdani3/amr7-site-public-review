@extends('emails.layout')

@php
    $preheader = __('email_confirmation_preheader');
@endphp

@section('content')
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
        <tr>
            <td style="padding-bottom: 24px;">
                <h1 style="margin: 0 0 16px; font-size: 20px; font-weight: bold; color: #111827; font-family: 'Tajawal', Arial, sans-serif;">
                    {{ __('email_confirmation_title') }}
                </h1>
                <p style="margin: 0; font-size: 15px; color: #4b5563; line-height: 1.6;">
                    {!! __('email_confirmation_greeting', ['name' => '<span style="color: #111827; font-weight: bold;">' . ($data['name'] ?? __('valued_customer')) . '</span>']) !!}
                    <br>
                    {{ __('email_confirmation_body') }}
                </p>
            </td>
        </tr>

        <tr>
            <td>
                <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" 
                       style="border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden; margin-bottom: 24px; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
                    
                    <tr>
                        <td style="background-color: #f9fafb; padding: 12px 20px; border-bottom: 1px solid #e5e7eb; font-size: 13px; font-weight: bold; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px;">
                            {{ __('email_summary_title') }}
                        </td>
                    </tr>

                    <tr>
                        <td style="background-color: #ffffff; padding: 20px;">
                            
                            @if(!empty($data['subject']))
                                <p style="margin: 0 0 12px 0; font-size: 14px; font-weight: bold; color: #111827; padding-bottom: 12px; border-bottom: 1px dashed #e5e7eb;">
                                    <span style="color: #1FA7A2;">{{ __('email_label_subject') }}:</span> 
                                    {{ $data['subject'] }}
                                </p>
                            @endif

                            <div style="font-size: 14px; line-height: 1.7; color: #4b5563;">
                                {!! nl2br(e($data['message'] ?? '—')) !!}
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        <tr>
            <td>
                <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" 
                       style="background-color: #f0fdfa; border: 1px solid #ccfbf1; border-radius: 8px;">
                    <tr>
                        <td style="padding: 12px 16px; text-align: center; color: #115e59; font-size: 13px; font-weight: 500;">
                            {{ __('email_working_hours_note') }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
@endsection