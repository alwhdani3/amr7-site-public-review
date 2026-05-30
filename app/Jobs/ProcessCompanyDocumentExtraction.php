<?php

namespace App\Jobs;

use App\Services\Ai\DocumentExtractionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Phase E — Job يُحوّل استخراج وثيقة من pending إلى ready_for_review.
 *
 * dispatched من الـ Observer/Controller عند رفع وثيقة من نوع مدعوم.
 */
class ProcessCompanyDocumentExtraction implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;
    public int $timeout = 120;

    public function __construct(public int $extractionId)
    {
    }

    public function handle(DocumentExtractionService $service): void
    {
        $service->run($this->extractionId);
    }
}
