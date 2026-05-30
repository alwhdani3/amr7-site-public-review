<?php

namespace App\Http\Controllers;

use App\Models\FinancialStatementFile;
use Illuminate\Support\Facades\Storage;

class FsFileDownloadController extends Controller
{
    /**
     * إعدادات الهيدر الأمنية (Security Headers)
     * تهدف لمنع المتصفحات ومحركات البحث من أرشفة أو تخزين الملفات الحساسة مؤقتاً.
     */
    private array $secureHeaders = [
        'X-Robots-Tag'  => 'noindex, nofollow',            // منع الأرشفة
        'Cache-Control' => 'private, no-cache, no-store, must-revalidate', // منع الكاش نهائياً
        'Pragma'        => 'no-cache',
        'Expires'       => '0',
    ];

    public function __construct()
    {
        $this->middleware(['auth', 'verified']);
    }

    /**
     * تحميل ملف القوائم المالية
     */
    public function download(FinancialStatementFile $file)
    {
        // 1. جلب الطلب المرتبط بالملف للتحقق من الصلاحية
        // (افترضنا أن العلاقة في المودل الجديد تسمى 'request')
        $fsRequest = $file->request;

        // 2. التحقق من الصلاحية عبر السياسات (Policy)
        $this->authorize('view', $fsRequest);
        
        if (! $this->isStaffUser() && ($file->visibility === 'staff')) {
    abort(403);
}

        // 3. تحديد القرص والمسار
        // الأولوية للقيمة المخزنة في الداتا بيس، وإذا كانت فارغة نفترض 'private'
        $disk = $file->disk ?: 'private';
        $path = $file->path;

        abort_if(blank($path), 404);

        // 4. (Fallback Check) التحقق من وجود الملف فعلياً
        // إذا لم يوجد في القرص المحدد، نحاول البحث عنه في القرص الآخر
        if (! Storage::disk($disk)->exists($path)) {
            $fallbackDisk = ($disk === 'private') ? 'public' : 'private';
            
            // إذا لم يوجد في البديل أيضاً، نرجع 404
            abort_unless(Storage::disk($fallbackDisk)->exists($path), 404);
            
            // تحديث القرص المستخدم للتحميل
            $disk = $fallbackDisk;
        }

        // 5. تحديد اسم الملف عند التنزيل (الاسم الأصلي أو اسم الملف في المسار)
        $filename = $file->original_name ?: basename($path);

        // 6. تنفيذ التحميل مع الهيدرات الأمنية
        return Storage::disk($disk)->download($path, $filename, $this->secureHeaders);
    }
    
    private function isStaffUser(): bool
{
    $user = auth()->user();

    if (! $user) {
        return false;
    }

    $role = strtolower((string) ($user->role ?? ''));

    return (bool) ($user->is_admin ?? false)
        || in_array($role, ['admin', 'agent', 'staff', 'employee', 'support'], true);
}
}