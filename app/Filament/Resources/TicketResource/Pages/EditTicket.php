<?php

namespace App\Filament\Resources\TicketResource\Pages;

use App\Filament\Resources\TicketResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTicket extends EditRecord
{
    protected static string $resource = TicketResource::class;

    // ❌ تم حذف السطر التالي لأنه يعتمد على ملف Blade قديم قد يسبب مشاكل
    // protected string $view = 'filament.resources.tickets.pages.edit-ticket';

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    
    // ✅ لضمان ظهور الردود والمرفقات، تأكد أنك أضفتهم في ملف TicketResource.php
    // في دالة getRelations()
}