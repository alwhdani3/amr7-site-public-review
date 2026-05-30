<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width">
    <title>{{ $subjectText ?? 'مقترح تعاون مهني لخدمة عملاء آمر سبعة' }}</title>
</head>

<body style="margin:0;padding:0;background:#f3f6f8;font-family:Tahoma,Arial,sans-serif;color:#1f2937;direction:rtl;text-align:right;">
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#f3f6f8;padding:28px 10px;">
    <tr>
        <td align="center">

            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:680px;background:#ffffff;border:1px solid #e5e7eb;border-radius:18px;overflow:hidden;">

                <tr>
                    <td style="background:#0A2540;padding:24px 30px;text-align:center;">
                        <img src="{{ asset('brand/amr7/amr7-logo-lockup-light.png') }}"
                             alt="شركة آمر سبعة لحلول الأعمال"
                             width="135"
                             style="display:block;margin:0 auto 12px auto;max-width:135px;height:auto;border:0;">

                        <div style="font-size:20px;font-weight:800;color:#ffffff;line-height:1.7;">
                            شركة آمر سبعة لحلول الأعمال
                        </div>

                        <div style="font-size:13px;color:#8EDCEF;line-height:1.8;margin-top:3px;">
                            حلول أعمال وخدمات تأسيس وإسناد مهني
                        </div>
                    </td>
                </tr>

                <tr>
                    <td style="padding:34px 36px 16px 36px;">

                        <h1 style="margin:0 0 22px 0;font-size:24px;line-height:1.6;color:#0A2540;font-weight:800;">
                            {{ $subjectText ?? 'مقترح تعاون مهني لخدمة عملاء آمر سبعة' }}
                        </h1>

                        <div style="font-size:16px;line-height:2.05;color:#374151;">
                            {!! nl2br(e($bodyText)) !!}
                        </div>

                        <div style="margin-top:28px;font-size:16px;line-height:2;color:#374151;">
                            <strong>شركة آمر سبعة لحلول الأعمال</strong><br>
                            الموقع:
                            <a href="https://amr-7.sa" style="color:#0A2540;text-decoration:none;font-weight:700;">amr-7.sa</a><br>
                            البريد:
                            <a href="mailto:info@amr-7.sa" style="color:#0A2540;text-decoration:none;font-weight:700;">info@amr-7.sa</a><br>
                            واتساب:
                            <a href="https://wa.me/966505336956" style="color:#0A2540;text-decoration:none;font-weight:700;">+966 50 533 6956</a>
                        </div>

                        <div style="margin-top:26px;text-align:center;">
                            <a href="https://wa.me/966505336956"
                               style="display:inline-block;background:#1FA7A2;color:#ffffff;text-decoration:none;padding:13px 24px;border-radius:10px;font-size:15px;font-weight:800;margin:0 4px 8px 4px;">
                                مناقشة المقترح
                            </a>

                            <a href="https://amr-7.sa"
                               style="display:inline-block;background:#0A2540;color:#ffffff;text-decoration:none;padding:13px 24px;border-radius:10px;font-size:15px;font-weight:800;margin:0 4px 8px 4px;">
                                الاطلاع على الشركة
                            </a>
                        </div>

                    </td>
                </tr>

                <tr>
                    <td style="background:#f8fafc;border-top:1px solid #e5e7eb;padding:18px 30px;text-align:center;">
                        <div style="font-size:12px;color:#6b7280;line-height:1.8;">
                            هذه الرسالة موجهة للمكاتب المهنية ذات العلاقة بخدمات الأعمال والمحاسبة والزكاة والضريبة.<br>
                            لإيقاف استقبال الرسائل مستقبلًا، يمكنكم الرد بكلمة: <strong>إلغاء</strong>
                        </div>
                    </td>
                </tr>

            </table>

        </td>
    </tr>
</table>
</body>
</html>
