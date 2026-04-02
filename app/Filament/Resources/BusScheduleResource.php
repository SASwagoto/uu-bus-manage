<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BusScheduleResource\Pages;
use App\Filament\Resources\BusScheduleResource\RelationManagers;
use App\Models\BusSchedule;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Closure;

class BusScheduleResource extends Resource
{
    protected static ?string $model = BusSchedule::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
            Forms\Components\Section::make('Schedule Information')
                ->description('বাস, ড্রাইভার এবং সময় নির্ধারণ করুন। সিস্টেম অটোমেটিক কনফ্লিক্ট চেক করবে।')
                ->schema([
                    Forms\Components\Select::make('bus_id')
                        ->relationship('bus', 'bus_number')
                        ->required()
                        ->live(), // এটি দিলে ভ্যালিডেশন রিয়েল-টাইমে কাজ করবে

                    Forms\Components\Select::make('driver_id')
                        ->label('Driver')
                        ->options(\App\Models\User::where('role', 'driver')->pluck('name', 'id'))
                        ->required()
                        ->live(),

                    Forms\Components\Select::make('route_id')
                        ->relationship('route', 'route_name')
                        ->required(),

                    Forms\Components\TimePicker::make('departure_time')
                        ->required()
                        ->label('Departure Time')
                        ->seconds(false)
                        ->live()
                        ->rules([
                            fn (Get $get): Closure => function (string $attribute, $value, Closure $fail) use ($get) {
                                $busId = $get('bus_id');
                                $driverId = $get('driver_id');
                                $days = $get('days_of_week');
                                $recordId = $get('id'); // এডিট করার সময় বর্তমান আইডি

                                if (!$busId || !$driverId || !$days || !$value) return;

                                foreach ($days as $day) {
                                    // ১. বাস কনফ্লিক্ট চেক
                                    $busConflict = BusSchedule::where('bus_id', $busId)
                                        ->where('departure_time', $value)
                                        ->whereJsonContains('days_of_week', $day)
                                        ->when($recordId, fn ($q) => $q->where('id', '!=', $recordId))
                                        ->exists();

                                    if ($busConflict) {
                                        $fail("{$day} তারিখে এই সময়ে এই বাসটি অন্য শিডিউলে আছে।");
                                    }

                                    // ২. ড্রাইভার কনফ্লিক্ট চেক
                                    $driverConflict = BusSchedule::where('driver_id', $driverId)
                                        ->where('departure_time', $value)
                                        ->whereJsonContains('days_of_week', $day)
                                        ->when($recordId, fn ($q) => $q->where('id', '!=', $recordId))
                                        ->exists();

                                    if ($driverConflict) {
                                        $fail("{$day} তারিখে এই সময়ে এই ড্রাইভার অন্য বাসে ডিউটিতে আছেন।");
                                    }
                                }
                            },
                        ]),

                    Forms\Components\CheckboxList::make('days_of_week')
                        ->label('Operation Days')
                        ->options([
                            'Saturday' => 'Saturday',
                            'Sunday' => 'Sunday',
                            'Monday' => 'Monday',
                            'Tuesday' => 'Tuesday',
                            'Wednesday' => 'Wednesday',
                            'Thursday' => 'Thursday',
                            'Friday' => 'Friday',
                        ])
                        ->columns(2)
                        ->required()
                        ->live(),
                        
                    Forms\Components\Toggle::make('is_active')
                        ->label('Schedule Active Status')
                        ->default(true),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('bus.bus_number')->label('Bus'),
                Tables\Columns\TextColumn::make('route.route_name')->label('Route'),
                Tables\Columns\TextColumn::make('departure_time')->time('h:i A')->label('Time'),
                Tables\Columns\TextColumn::make('days_of_week')
                    ->badge()
                    ->label('Running Days'),
                Tables\Columns\ToggleColumn::make('is_active')->label('Status'),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBusSchedules::route('/'),
            'create' => Pages\CreateBusSchedule::route('/create'),
            'edit' => Pages\EditBusSchedule::route('/{record}/edit'),
        ];
    }
}
