<?php

namespace App\Filament\Resources\BusScheduleResource\Pages;

use App\Filament\Resources\BusScheduleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBusSchedule extends EditRecord
{
    protected static string $resource = BusScheduleResource::class;

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
}
