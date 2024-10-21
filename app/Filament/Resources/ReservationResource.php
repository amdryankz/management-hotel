<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReservationResource\Pages;
use App\Filament\Resources\ReservationResource\RelationManagers;
use App\Models\Reservation;
use App\Models\Room;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Radio;
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
                Wizard::make([
                    Wizard\Step::make('Guest')
                        ->schema([
                            Select::make('guest_selection')
                                ->label('Select or Create Guest')
                                ->options([
                                    'existing' => 'Select Existing Guest',
                                    'new' => 'Create New Guest',
                                ])
                                ->reactive()
                                ->required(),

                            Select::make('guest_id')
                                ->label('Guest')
                                ->searchable()
                                ->relationship('guest', 'name')
                                ->requiredWith('guest_selection', 'existing')
                                ->placeholder('Select existing guest')
                                ->hidden(fn(callable $get) => $get('guest_selection') !== 'existing'),

                            TextInput::make('guest_name')
                                ->label('New Guest Name')
                                ->requiredWith('guest_selection', 'new')
                                ->placeholder('Enter guest name')
                                ->hidden(fn(callable $get) => $get('guest_selection') !== 'new'),

                            TextInput::make('guest_phone')
                                ->label('New Guest Phone')
                                ->requiredWith('guest_selection', 'new')
                                ->placeholder('Enter guest phone')
                                ->hidden(fn(callable $get) => $get('guest_selection') !== 'new'),
                        ]),
                    Wizard\Step::make('Room')
                        ->schema([
                            Select::make('room_ids')
                                ->label('Rooms')
                                ->relationship('rooms', 'room_number')
                                ->options(Room::all()->pluck('room_number', 'id')->toArray())
                                ->required()
                                ->multiple()
                                ->placeholder('Select rooms'),

                            DateTimePicker::make('check_in_date')
                                ->label('Check-in')
                                ->required(),

                            DateTimePicker::make('check_out_date')
                                ->label('Check-out')
                                ->required(),

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
                        ]),
                ])->columnSpan('full'),
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
