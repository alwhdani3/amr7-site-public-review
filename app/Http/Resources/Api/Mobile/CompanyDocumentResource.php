<?php

namespace App\Http\Resources\Api\Mobile;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Mobile API — تمثيل وثيقة منشأة.
 *
 * Phase 2:
 *   - لا يُكشَف storage path (file_path) إطلاقًا.
 *   - download_url = null حاليًا. endpoint التنزيل سيُضاف في Phase تالٍ.
 *   - has_file فقط يخبر العميل إن كان للوثيقة ملف مرفق.
 */
class CompanyDocumentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                 => $this->id,
            'company_id'         => $this->company_id,
            'type'               => $this->type,
            'document_number'    => $this->document_number,
            'issue_date'         => optional($this->issue_date)->toDateString(),
            'expiry_date'        => optional($this->expiry_date)->toDateString(),
            'status'             => $this->status,
            'computed_status'    => $this->computed_status ?? null,
            'days_remaining'     => $this->days_remaining,
            'alert_stage'        => $this->alert_stage ?? 'none',
            'alert_last_sent_at' => optional($this->alert_last_sent_at)->toIso8601String(),
            'has_file'           => ! empty($this->file_path),
            // Phase 2: download_url مُعطَّل عمدًا — placeholder حتى يتم بناء endpoint التنزيل الآمن
            'download_url'       => null,
            'download_available' => false,
        ];
    }
}
