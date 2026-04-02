<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TripResource\Pages;
use App\Filament\Resources\TripResource\RelationManagers;
use App\Models\Trip;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TripResource extends Resource
{
    protected static ?string $model = Trip::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
            Tables\Columns\TextColumn::make('driver.name')->label('Driver'),
            Tables\Columns\TextColumn::make('bus.bus_number')->label('Bus'),
            Tables\Columns\TextColumn::make('route.route_name')->label('Route'),
            Tables\Columns\TextColumn::make('current_lat')->label('Latitude'),
            Tables\Columns\TextColumn::make('current_lng')->label('Longitude'),
            Tables\Columns\BadgeColumn::make('status')
                ->colors([
                    'warning' => 'on_way',
                    'success' => 'completed',
                    'danger' => 'cancelled',
                ]),
            Tables\Columns\TextColumn::make('passenger_count')->label('Passengers'),
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
            'index' => Pages\ListTrips::route('/'),
            'create' => Pages\CreateTrip::route('/create'),
            'edit' => Pages\EditTrip::route('/{record}/edit'),
        ];
    }
}
