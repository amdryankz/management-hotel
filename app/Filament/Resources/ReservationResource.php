<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReservationResource\Pages;
use App\Filament\Resources\ReservationResource\RelationManagers;
use App\Models\Reservation;
use App\Models\Room;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class ReservationResource extends Resource
{
    protected static ?string $model = Reservation::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(2)
                    ->schema([
                        Select::make('guest_id')
                            ->label('Guest')
                            ->relationship('guest', 'name')
                            ->required()
                            ->placeholder('Select guest'),

                        Select::make('room_ids')
                            ->label('Rooms')
                            ->options(Room::all()->pluck('room_number', 'id')->toArray()) // Adjust this line
                            ->required()
                            ->multiple()
                            ->placeholder('Select rooms'),

                        TextInput::make('check_in_date')
                            ->label('Check-in Date')
                            ->required()
                            ->type('date'),

                        TextInput::make('check_out_date')
                            ->label('Check-out Date')
                            ->required()
                            ->type('date'),

                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'pending' => 'Pending',
                                'confirmed' => 'Confirmed',
                                'canceled' => 'Canceled',
                            ])
                            ->default('pending')
                            ->required(),

                        Hidden::make('reserved_by')->default(Auth::id())
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('guest.name')
                    ->label('Guest')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('rooms.room_number')
                    ->label('Rooms')
                    ->sortable()
                    ->searchable()
                    ->getStateUsing(function ($record) {
                        return $record->rooms->pluck('room_number')->join(', ');
                    }),

                TextColumn::make('check_in_date')
                    ->label('Check-in Date')
                    ->sortable(),

                TextColumn::make('check_out_date')
                    ->label('Check-out Date')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->sortable()
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'warning',
                        'confirmed' => 'success',
                        'canceled' => 'danger',
                    }),

                TextColumn::make('user.name')
                    ->label('Reserved By')
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pending',
                        'confirmed' => 'Confirmed',
                        'canceled' => 'Canceled',
                    ])
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
            'index' => Pages\ListReservations::route('/'),
            'create' => Pages\CreateReservation::route('/create'),
            'edit' => Pages\EditReservation::route('/{record}/edit'),
        ];
    }
}
