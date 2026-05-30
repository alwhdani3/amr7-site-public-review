import './bootstrap';

// في Livewire v4، Alpine.js مدمج تلقائياً (Injected Assets).
// لا تقم باستيراد 'alpinejs' هنا لتجنب خطأ "Alpine detected multiple instances".

// استيراد الإضافات فقط
import collapse from '@alpinejs/collapse';
import Swal from 'sweetalert2';

// إتاحة SweetAlert للنطاق العام
window.Swal = Swal;

// ✅ تسجيل إضافات Alpine في اللحظة المناسبة (قبل تهيئة Livewire v4)
document.addEventListener('alpine:init', () => {
    window.Alpine.plugin(collapse);
});

/* * وظيفة زر العودة للأعلى (Back to Top)
 * متوافق مع نظام SPA في Livewire v4
 */
function initBackToTop() {
    const btn = document.getElementById('backToTop');
    if (!btn) return;

    // استخدام الكلاسات للتحكم بالظهور (أفضل للأداء من التلاعب بالستايل مباشرة)
    const toggleVisibility = () => {
        if (window.scrollY > 300) {
            btn.classList.remove('opacity-0', 'invisible', 'translate-y-4');
            btn.classList.add('opacity-100', 'visible', 'translate-y-0');
        } else {
            btn.classList.add('opacity-0', 'invisible', 'translate-y-4');
            btn.classList.remove('opacity-100', 'visible', 'translate-y-0');
        }
    };

    // إضافة تأثير الانتقال (Transition)
    btn.style.transition = 'opacity 0.3s ease-in-out, transform 0.3s ease-in-out';
    
    // فحص أولي عند التحميل
    toggleVisibility();

    // مستمع السكرول
    window.addEventListener('scroll', toggleVisibility, { passive: true });

    // النقر للصعود
    btn.onclick = (e) => {
        e.preventDefault();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    };
}

/*
 * دالة التهيئة العامة (UI Initialization)
 */
function initUI() {
    initBackToTop();
    // تمت إزالة initMobileMenu لأن القائمة تعمل الآن عبر Alpine.js في Blade مباشرة
}

// ✅ تشغيل عند التحميل لأول مرة (Hard Refresh)
document.addEventListener('DOMContentLoaded', initUI);

// ✅ تشغيل عند التنقل (SPA Navigation) في Livewire v4
document.addEventListener('livewire:navigated', () => {
    // التأكد من أن DOM جاهز تماماً بعد التحديث
    requestAnimationFrame(initUI);
});