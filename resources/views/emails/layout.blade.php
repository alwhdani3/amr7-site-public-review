<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>{{ $emailTitle ?? $title ?? config('app.name') }}</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            width: 100% !important;
            background-color: #f8fafc;
            color: #334155;
            font-family: Tahoma, Arial, sans-serif;
            -webkit-font-smoothing: antialiased;
        }
        table { border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        img { border: 0; line-height: 100%; outline: none; text-decoration: none; }
        a { color: #1fa7a2; text-decoration: none; font-weight: 700; }
        p { margin: 0; }
        .email-shell { width: 100%; background-color: #f8fafc; }
        .email-container { width: 640px; max-width: 640px; }
        .email-card { background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; overflow: hidden; box-shadow: 0 16px 40px rgba(15, 23, 42, 0.08); }
        .email-content { padding: 36px; text-align: {{ app()->getLocale() === 'ar' ? 'right' : 'left' }}; direction: {{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}; }
        .email-title { margin: 0 0 12px; color: #0a2540; font-size: 24px; line-height: 1.35; font-weight: 800; }
        .email-lead { color: #475569; font-size: 15px; line-height: 1.9; }
        .email-section { margin-top: 24px; }
        @media only screen and (max-width: 680px) {
            .email-outer-padding { padding: 20px 10px !important; }
            .email-container { width: 100% !important; max-width: 100% !important; }
            .email-content { padding: 24px 18px !important; }
            .email-title { font-size: 21px !important; }
            .email-card { border-radius: 14px !important; }
            .detail-label, .detail-value { display: block !important; width: 100% !important; box-sizing: border-box !important; }
            .detail-label { border-bottom: 0 !important; padding-bottom: 4px !important; }
            .detail-value { padding-top: 4px !important; }
        }
    </style>
</head>
<body>
    <table role="presentation" class="email-shell" width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td class="email-outer-padding" align="center" style="padding: 32px 12px;">
                <table role="presentation" class="email-container" width="640" cellpadding="0" cellspacing="0">
                    <tr>
                        <td class="email-card">
                            @include('emails.partials.header')

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td class="email-content">
                                        @yield('content')
                                    </td>
                                </tr>
                            </table>

                            @include('emails.partials.footer')
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
