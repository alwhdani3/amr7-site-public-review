<?php

namespace App\Filament\Resources\FinancialStatementRequestResource\Pages;

use App\Filament\Resources\FinancialStatementRequestResource;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage;

class EditFinancialStatementRequest extends EditRecord
{
    protected static string $resource = FinancialStatementRequestResource::class;

    public function getTitle(): string
    {
        return 'تعديل طلب قوائم مالية';
    }

    public function getBreadcrumb(): string
    {
        return 'تعديل / معالجة';
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        unset($data['final_outputs_temp']);

        return $data;
    }

    protected function afterSave(): void
    {
        $state = $this->form->getState();
        $paths = $state['final_outputs_temp'] ?? [];

        if (! empty($paths)) {
            foreach ($paths as $path) {
                $exists = $this->record->files()
                    ->where('path', $path)
                    ->exists();

                if (! $exists) {
                    $disk = 'private';

        $this->record->files()->create([
    'uploaded_by'   => auth()->id(),
    'file_key'      => 'final_output',
    'disk'          => $disk,
    'visibility'    => 'client',
    'path'          => $path,
    'original_name' => basename($path),
    'mime'          => Storage::disk($disk)->exists($path) ? Storage::disk($disk)->mimeType($path) : null,
    'size'          => Storage::disk($disk)->exists($path) ? Storage::disk($disk)->size($path) : null,
    'is_final'      => true,
]);
                }
            }

            $this->form->fill(array_merge($state, [
                'final_outputs_temp' => [],
            ]));
        }
    }
}