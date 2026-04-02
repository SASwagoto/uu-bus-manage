<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RouteResource\Pages;
use App\Filament\Resources\RouteResource\RelationManagers;
use App\Models\Route;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RouteResource extends Resource
{
    protected static ?string $model = Route::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
            Section::make('Route Details')
                ->schema([
                    TextInput::make('route_name')
                        ->required()
                        ->placeholder('যেমন: উত্তরা - ক্যাম্পাস (সকাল)'),
                    
                    TextInput::make('start_point')
                        ->required()
                        ->placeholder('যেমন: উত্তরা হাউজ বিল্ডিং'),
                    
                    TextInput::make('end_point')
                        ->required()
                        ->placeholder('যেমন: মেইন ক্যাম্পাস'),
                ])->columns(2),

            Section::make('Stoppages')
                ->schema([
                    // এটি JSON কলামে ডেটা সেভ করবে
                    Repeater::make('stoppages')
                        ->schema([
                            TextInput::make('stop_name')
                                ->label('Stop Name')
                                ->required(),
                        ])
                        ->createItemButtonLabel('Add New Stoppage')
                        ->columns(1)
                        ->grid(2) // দুই কলামে দেখাবে
                ])
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
           ->columns([
            TextColumn::make('route_name')
                ->label('Route Name')
                ->searchable()
                ->sortable(),

            TextColumn::make('start_point')
                ->label('Starts From'),

            TextColumn::make('end_point')
                ->label('Ends At'),

            // স্টপেজ কয়টি আছে তা সংখ্যায় দেখাবে
            TextColumn::make('stoppages')
                ->label('Total Stops')
                ->getStateUsing(fn ($record) => is_array($record->stoppages) ? count($record->stoppages) : 0)
                ->badge(),
        ])
        ->filters([
            // প্রয়োজন হলে ফিল্টার যোগ করতে পারিস
        ])
        ->actions([
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListRoutes::route('/'),
            'create' => Pages\CreateRoute::route('/create'),
            'edit' => Pages\EditRoute::route('/{record}/edit'),
        ];
    }
}
