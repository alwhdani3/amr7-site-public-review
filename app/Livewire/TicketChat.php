<?php

namespace App\Livewire;

use App\Models\Ticket;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Validate; // 👈 التحقق الحديث
use Artesaos\SEOTools\Facades\SEOTools;
use Illuminate\Support\Facades\DB; // للمعاملات البنكية

class TicketChat extends Component
{
    use WithFileUploads;

    public Ticket $ticket;

    // 1. التحقق الحديث: الرسالة مطلوبة فقط إذا لم يكن هناك مرفق
    #[Validate('required_without:attachment|string')]
    public string $message = '';

    #[Validate('nullable|file|max:10240')] // رفعنا الحد لـ 10MB للتذاكر
    public $attachment;

    public function mount()
    {
        // إعدادات السيو والخصوصية (ممتازة كما هي)
        SEOTools::setTitle('محادثة التذكرة #' . $this->ticket->id);
        SEOTools::metatags()->addMeta('robots', 'noindex, nofollow');
    }

    public function send()
    {
        $this->validate(); // سيأخذ القواعد من الـ Attributes أعلاه

        // 2. استخدام Transaction لضمان سلامة البيانات
        DB::transaction(function () {
            
            $attachmentData = null;
            
            // 3. 🛡️ التخزين في 'local' بدلاً من 'public' للخصوصية
            if ($this->attachment) {
                $path = $this->attachment->store('ticket-attachments', 'local');
                $attachmentData = [
                    'path' => $path,
                    'name' => $this->attachment->getClientOriginalName(),
                ];
            }

            // إنشاء الرد مرة واحدة (أنظف للكود)
            $this->ticket->replies()->create([
                'user_id' => auth()->id(),
                'message' => $this->message,
                'attachment_path' => $attachmentData['path'] ?? null,
                'attachment_name' => $attachmentData['name'] ?? null,
            ]);
        });

        // تنظيف الحقول وإشعار الواجهة
        $this->reset(['message', 'attachment']);
        $this->dispatch('message-sent'); 
        // 💡 نصيحة: في الفرونت إند استخدم هذا الحدث لعمل scroll to bottom
    }

    public function render()
    {
        // تحسين الاستعلام
        $replies = $this->ticket->replies()
            ->with('user') // Eager Loading
            ->latest()
            ->take(50) // ⚡ نكتفي بآخر 50 رسالة للأداء (أو استخدم paginate)
            ->get()
            ->reverse(); // لعكس الترتيب في الشات (القديم فوق والجديد تحت)

        return view('livewire.ticket-chat', compact('replies'));
    }
}