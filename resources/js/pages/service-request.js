// resources/js/pages/service-request.js

document.addEventListener('livewire:init', () => {
    // استماع لحدث إرسال الطلب بنجاح
    Livewire.on('service-request-sent', ({ message }) => {
        if (window.Swal) {
            Swal.fire({
                title: 'تم الإرسال!',
                text: message,
                icon: 'success',
                
                // --- تعديلات الوضع النهاري (Light Mode) ---
                background: '#ffffff',     // خلفية بيضاء
                color: '#0f172a',          // نص كحلي غامق
                // ----------------------------------------
                
                confirmButtonColor: '#1FA7A2', // لون الهوية
                confirmButtonText: 'ممتاز',
            });
        }
    });
});