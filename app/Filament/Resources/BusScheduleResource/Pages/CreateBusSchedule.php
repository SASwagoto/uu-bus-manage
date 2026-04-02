<?php

namespace App\Filament\Resources\BusScheduleResource\Pages;

use App\Filament\Resources\BusScheduleResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateBusSchedule extends CreateRecord
{
    protected static string $resource = BusScheduleResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
