<?php

namespace App\Mail\Operations;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Status mail for an n8n workflow run.
 *
 * TODO Phase N5: wire a dispatcher/listener that fires this mail when a
 * critical workflow run lands in n8n_workflow_runs with status=failed,
 * so an operator is notified instead of having to poll the dashboard.
 * Today this class is defined but nothing dispatches it.
 */
class N8nWorkflowStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public array $run, public ?string $url = null) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'نتيجة تشغيل آلي - شركة آمر سبعة لحلول الأعمال');
    }

    public function content(): Content
    {
        $url = $this->url ?: config('app.url') . '/amr7/n8n-operations-center';

        return new Content(
            view: 'emails.operations.n8n-workflow-status',
            text: 'emails.plain.operations-notification',
            with: [
                'emailTitle' => 'نتيجة تشغيل آلي',
                'workflowName' => $this->run['workflow_name'] ?? $this->run['workflow_key'] ?? null,
                'category' => $this->run['category'] ?? null,
                'status' => $this->run['status'] ?? null,
                'runAt' => $this->run['created_at'] ?? now()->toDateTimeString(),
                'summary' => $this->run['summary'] ?? $this->run['error_message'] ?? null,
                'url' => $url,
                'title' => 'نتيجة تشغيل آلي',
                'intro' => 'هذا إشعار مختصر بحالة تشغيل أحد workflows المرتبطة بعمليات آمر سبعة.',
                'actionLabel' => 'عرض السجل داخل لوحة آمر سبعة',
                'lines' => [
                    'اسم workflow' => $this->run['workflow_name'] ?? $this->run['workflow_key'] ?? null,
                    'النوع' => $this->run['category'] ?? null,
                    'الحالة' => $this->run['status'] ?? null,
                    'وقت التشغيل' => $this->run['created_at'] ?? now()->toDateTimeString(),
                    'ملخص السجل' => $this->run['summary'] ?? $this->run['error_message'] ?? null,
                ],
            ],
        );
    }
}
