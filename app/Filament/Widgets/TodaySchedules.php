<?php

namespace App\Filament\Widgets;

use App\Models\BusSchedule;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class TodaySchedules extends BaseWidget
{
    // ড্যাশবোর্ডের কতটুকু জায়গা জুড়ে থাকবে (পুরো জায়গা নিতে 'full' ব্যবহার কর)
    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = 'Today\'s Bus Schedules';

    public function table(Table $table): Table
    {
        $today = now()->format('l'); 

        return $table
            ->query(
                BusSchedule::query()
                    ->where('is_active', true)
                    ->whereJsonContains('days_of_week', $today)
                    ->orderBy('departure_time', 'asc')
            )
            ->columns([
                Tables\Columns\TextColumn::make('departure_time')
                    ->label('Time')
                    ->time('h:i A')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('bus.bus_number')
                    ->label('Bus Number')
                    ->icon('heroicon-m-truck'),

                Tables\Columns\TextColumn::make('route.route_name')
                    ->label('Route'),

                Tables\Columns\TextColumn::make('driver.name')
                    ->label('Driver'),
                
                Tables\Columns\TextColumn::make('route.start_point')
                    ->label('Start'),

                Tables\Columns\TextColumn::make('route.end_point')
                    ->label('Destination'),
            ]);
    }
}