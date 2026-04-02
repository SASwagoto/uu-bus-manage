<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BusResource\Pages;
use App\Filament\Resources\BusResource\RelationManagers;
use App\Models\Bus;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BusResource extends Resource
{
    protected static ?string $model = Bus::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
            Forms\Components\Card::make()
                ->schema([
                    Forms\Components\TextInput::make('bus_number')
                        ->required()
                        ->unique(ignoreRecord: true),
                    Forms\Components\TextInput::make('model_name'),
                    Forms\Components\TextInput::make('capacity')
                        ->numeric()
                        ->default(40),
                    Forms\Components\Select::make('status')
                        ->options([
                            'active' => 'Active',
                            'idle' => 'Idle',
                            'maintenance' => 'Maintenance',
                        ])
                        ->default('idle')
                        ->required(),
                ])
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
            // বাসের নাম্বার দেখানোর জন্য
            TextColumn::make('bus_number')
                ->label('Bus Number')
                ->searchable()
                ->sortable(),

            // সিট ক্যাপাসিটি
            TextColumn::make('capacity')
                ->label('Total Seats'),

            // মডেল বা টাইপ (যদি মাইগ্রেশনে দিয়ে থাকিস)
            TextColumn::make('model_name')
                ->label('Model')
                ->placeholder('N/A'),

            // স্ট্যাটাস দেখানোর জন্য সুন্দর একটি ব্যাজ
            BadgeColumn::make('status')
                ->colors([
                    'secondary' => 'idle',
                    'success' => 'active',
                    'danger' => 'maintenance',
                ])
                ->icons([
                    'heroicon-o-clock' => 'idle',
                    'heroicon-o-check-circle' => 'active',
                    'heroicon-o-wrench' => 'maintenance',
                ]),
        ])
        ->filters([
            // স্ট্যাটাস অনুযায়ী ফিল্টার করার অপশন
            Tables\Filters\SelectFilter::make('status')
                ->options([
                    'active' => 'Active',
                    'idle' => 'Idle',
                    'maintenance' => 'Maintenance',
                ]),
        ])
        ->actions([
            Tables\Actions\EditAction::make(),
        ])
        ->bulkActions([
            Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListBuses::route('/'),
            'create' => Pages\CreateBus::route('/create'),
            'edit' => Pages\EditBus::route('/{record}/edit'),
        ];
    }
}
