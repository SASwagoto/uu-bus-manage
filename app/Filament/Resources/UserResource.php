<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Dom\Text;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Illuminate\Support\Facades\Hash;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('User Information')
                    ->description('ব্যক্তিগত তথ্য এবং অ্যাকাউন্ট ডিটেইলস')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->label('Full Name'),
                        TextInput::make('username')
                            ->required()
                            ->label('Username'),
                        TextInput::make('email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true),

                        TextInput::make('password')
                            ->password()
                            ->dehydrateStateUsing(fn($state) => Hash::make($state)) // পাসওয়ার্ড অটো হ্যাশ হবে
                            ->required(fn(string $context): bool => $context === 'create') // শুধু ক্রিয়েট করার সময় লাগবে
                            ->label('Password'),

                        Select::make('role')
                            ->options([
                                'admin' => 'Admin',
                                'driver' => 'Driver',
                                'passenger' => 'Passenger',
                            ])
                            ->required()
                            ->native(false)
                            ->reactive(), // এটি দিলে রোল সিলেক্ট করলে নিচের ফিল্ডগুলো শো/হাইড হবে

                        TextInput::make('phone')
                            ->tel()
                            ->label('Phone Number'),

                        // শুধুমাত্র প্যাসেঞ্জার সিলেক্ট করলে এই ফিল্ডটি দেখাবে
                        TextInput::make('student_id')
                            ->label('Student/Staff ID')
                            ->visible(fn(callable $get) => $get('role') === 'passenger')
                            ->placeholder('যেমন: 2021000123'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->searchable(),

                // রোল অনুযায়ী কালারফুল ব্যাজ
                BadgeColumn::make('role')
                    ->colors([
                        'danger' => 'admin',
                        'warning' => 'driver',
                        'success' => 'passenger',
                    ])
                    ->icons([
                        'heroicon-o-shield-check' => 'admin',
                        'heroicon-o-truck' => 'driver',
                        'heroicon-o-user' => 'passenger',
                    ]),

                TextColumn::make('student_id')
                    ->label('ID No')
                    ->toggleable(), // এই কলাম হাইড/শো করা যাবে

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // রোল অনুযায়ী ফিল্টার করার সুবিধা
                Tables\Filters\SelectFilter::make('role')
                    ->options([
                        'admin' => 'Admin',
                        'driver' => 'Driver',
                        'passenger' => 'Passenger',
                    ]),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
