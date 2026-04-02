<?php

namespace App\Filament\Resources\BusResource\Pages;

use App\Filament\Resources\BusResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateBus extends CreateRecord
{
    protected static string $resource = BusResource::class;
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
