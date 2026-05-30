<?php

namespace App\Filament\Resources\TaskResource\Pages;

use App\Filament\Resources\TaskResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;

class ListTasks extends ListRecords
{
    protected static string $resource = TaskResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('مهمة جديدة'),
        ];
    }
}