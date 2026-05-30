@php
    $path = '/' . ltrim(request()->path(), '/');
    $isEn = $path === '/en' || str_starts_with($path, '/en/');
    $locale = $isEn ? 'en' : 'ar';
    $dir = $isEn ? 'ltr' : 'rtl';

    $strings = $isEn
        ? [
            'title'      => 'Page no longer available — Amr 7',
            'code'       => 'Error 410',
            'heading'    => 'This page is gone',
            'body'       => 'This page has been permanently removed. We may have moved to a new system or updated the content.',
            'home'       => 'Back to home',
            'services'   => 'Browse services',
            'home_url'   => '/en',
            'services_url' => '/en/services',
        ]
        : [
            'title'      => 'الصفحة لم تعد متاحة - شركة آمر سبعة لحلول الأعمال',
            'code'       => 'خطأ 410',
            'heading'    => 'الصفحة لم تعد متاحة',
            'body'       => 'هذه الصفحة تم حذفها بشكل دائم. ربما انتقلنا لنظام جديد أو تم تحديث المحتوى.',
            'home'       => 'العودة للرئيسية',
            'services'   => 'تصفح الخدمات',
            'home_url'   => '/',
            'services_url' => '/services',
        ];
@endphp
<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $dir }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $strings['title'] }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Tajawal', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #1FA7A2 0%, #1a5254 100%);
            color: #fff;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            text-align: center;
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
            padding: 50px 30px;
            border-radius: 20px;
            border: 1px solid rgba(255,255,255,0.2);
        }
        .icon { font-size: 80px; margin-bottom: 20px; }
        h1 { font-size: 2.5rem; margin-bottom: 15px; font-weight: 700; }
        .code { font-size: 1rem; opacity: 0.7; margin-bottom: 20px; letter-spacing: 2px; }
        p { font-size: 1.1rem; line-height: 1.8; margin-bottom: 30px; opacity: 0.95; }
        .buttons { display: flex; gap: 15px; justify-content: center; flex-wrap: wrap; }
        .btn {
            display: inline-block;
            padding: 12px 30px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
            font-size: 1rem;
        }
        .btn-primary { background: #fff; color: #1FA7A2; }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.2); }
        .btn-secondary { background: transparent; color: #fff; border: 2px solid #fff; }
        .btn-secondary:hover { background: rgba(255,255,255,0.1); }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">📭</div>
        <div class="code">{{ $strings['code'] }}</div>
        <h1>{{ $strings['heading'] }}</h1>
        <p>{{ $strings['body'] }}</p>
        <div class="buttons">
            <a href="{{ $strings['home_url'] }}" class="btn btn-primary">{{ $strings['home'] }}</a>
            <a href="{{ $strings['services_url'] }}" class="btn btn-secondary">{{ $strings['services'] }}</a>
        </div>
    </div>
</body>
</html>
