<?php

namespace App\Filament\Resources\BusScheduleResource\Pages;

use App\Filament\Resources\BusScheduleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBusSchedules extends ListRecords
{
    protected static string $resource = BusScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
