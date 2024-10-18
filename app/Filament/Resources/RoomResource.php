<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoomResource\Pages;
use App\Filament\Resources\RoomResource\RelationManagers;
use App\Models\Room;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RoomResource extends Resource
{
    protected static ?string $model = Room::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('room_number')
                    ->inputMode('numeric')
                    ->required()
                    ->label('Room Number')
                    ->placeholder('Enter room number'),

                Select::make('category')
                    ->label('Room Category')
                    ->required()
                    ->options([
                        'Standard' => 'Standard',
                        'Deluxe' => 'Deluxe',
                        'Superior' => 'Superior',
                    ])
                    ->placeholder('Select room category'),

                TextInput::make('description')
                    ->label('Description')
                    ->required()
                    ->placeholder('Describe the room'),

                TextInput::make('price')
                    ->label('Price (Rp)')
                    ->required()
                    ->prefix('Rp.')
                    ->inputMode('decimal')
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {
                        $cleanedValue = str_replace('.', '', $state);
                        if (is_numeric($cleanedValue)) {
                            $formattedValue = number_format($cleanedValue, 0, ',', '.');
                            $set('price', $formattedValue);
                        }
                    })
                    ->dehydrateStateUsing(fn($state) => (int) str_replace('.', '', $state)),

                Select::make('status')
                    ->label('Room Status')
                    ->required()
                    ->options([
                        'available' => 'Available',
                        'occupied' => 'Occupied',
                        'maintenance' => 'Maintenance',
                    ])
                    ->default('available')
                    ->placeholder('Select room status'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('room_number')
                    ->label('Room Number')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('category')
                    ->label('Category')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('price')
                    ->label('Price')
                    ->formatStateUsing(fn($state) => 'Rp. ' . number_format($state, 0, ',', '.'))
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'occupied' => 'warning',
                        'available' => 'success',
                        'maintenance' => 'danger',
                    })
                    ->sortable()
            ])
            ->filters([
                SelectFilter::make('category')
                    ->label('Category')
                    ->options([
                        'Standard' => 'Standard',
                        'Deluxe' => 'Deluxe',
                        'Superior' => 'Superior',
                    ]),
                SelectFilter::make('status')
                    ->label('Room Status')
                    ->options([
                        'available' => 'Available',
                        'occupied' => 'Occupied',
                        'maintenance' => 'Maintenance',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListRooms::route('/'),
            'create' => Pages\CreateRoom::route('/create'),
            'edit' => Pages\EditRoom::route('/{record}/edit'),
        ];
    }
}
