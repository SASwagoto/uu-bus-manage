<?php

namespace App\Filament\Widgets;

use App\Models\Bus;
use App\Models\User;
use App\Models\Trip;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?string $pollingInterval = '30s';
    protected function getStats(): array
    {
        return [
            // ১. টোটাল বাস সংখ্যা
            Stat::make('Total Buses', Bus::count())
                ->description('All registered university buses')
                ->descriptionIcon('heroicon-m-truck')
                ->color('primary'),

            // ২. টোটাল ড্রাইভার (রোল অনুযায়ী ফিল্টার)
            Stat::make('Total Drivers', User::where('role', 'driver')->count())
                ->description('Active bus drivers')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('success'),

            // ৩. টোটাল প্যাসেঞ্জার
            Stat::make('Total Passengers', User::where('role', 'passenger')->count())
                ->description('Registered students & staff')
                ->descriptionIcon('heroicon-m-users')
                ->color('warning'),

            // ৪. বর্তমান রানিং ট্রিপ (অপশনাল কিন্তু কাজের)
            Stat::make('Active Trips', Trip::where('status', 'on_way')->count())
                ->description('Buses currently on road')
                ->descriptionIcon('heroicon-m-play')
                ->color('danger'),
        ];
    }
}
