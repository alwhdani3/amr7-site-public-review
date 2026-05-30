<?php

namespace App\Http\Controllers;

use App\Models\CompanyFile;
use Illuminate\Support\Facades\Storage;

/**
 * @deprecated Phase 5 audit: هذا الـcontroller غير مربوط بأي route حاليًا
 *             (راجع routes/web.php — كل تنزيلات الملفات تستخدم SecureFileController).
 *             أُبقي عليه فقط لتفادي كسر أي استدعاء خارجي محتمل، لكنه أصبح
 *             يفوّض إلى SecureFileController الذي يفحص ownership/backoffice
 *             قبل التنزيل. لا تربط route جديد به مباشرة — استخدم
 *             route('company.files.download') على SecureFileController بدلاً منه.
 */
class CompanyFileDownloadController extends Controller
{
    public function __invoke(CompanyFile $companyFile, \Illuminate\Http\Request $request)
    {
        // Phase 5: تفويض آمن إلى SecureFileController حتى لا يلتفّ أحد على فحص الملكية
        // في حال أُعيد ربط هذا الـcontroller بـroute بطريق الخطأ.
        return app(SecureFileController::class)->companyFileDownload($request, $companyFile);
    }
}