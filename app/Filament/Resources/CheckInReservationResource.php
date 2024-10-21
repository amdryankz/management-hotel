<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CheckInReservationResource\Pages;
use App\Filament\Resources\CheckInReservationResource\RelationManagers;
use App\Models\Payment;
use App\Models\Reservation;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CheckInReservationResource extends Resource
{
    protected static ?string $model = Reservation::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationLabel = 'Check In';

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
                TextColumn::make('guest.name')
                    ->label('Guest Name')
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
                    ->sortable()
                    ->dateTime(),

                TextColumn::make('check_out_date')
                    ->label('Check-out Date')
                    ->sortable()
                    ->dateTime(),

                TextColumn::make('guest_status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Checked In' => 'success',
                        'Checked Out' => 'danger',
                    })
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('Extend Checkout')
                    ->icon('heroicon-o-calendar')
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\TextInput::make('additional_days')
                            ->label('Additional Days')
                            ->type('number')
                            ->required(),
                        Select::make('payment_method')
                            ->label('Payment Method')
                            ->required()
                            ->options([
                                'cash' => 'Cash',
                                'bank_transfer' => 'Bank Transfer',
                                'credit_card' => 'Credit Card',
                            ])
                            ->placeholder('Select a payment method'),
                    ])
                    ->action(function (Reservation $record, array $data) {
                        // Calculate the new checkout date
                        $newCheckoutDate = Carbon::parse($record->check_out_date)
                            ->addDays($data['additional_days']);
                        $record->update(['check_out_date' => $newCheckoutDate]);

                        // Calculate the additional cost for the extra days
                        $totalRoomPrice = $record->rooms->sum('price');
                        $additionalAmount = $totalRoomPrice * $data['additional_days'];

                        // Create a new payment record for the extension
                        Payment::create([
                            'reservation_id' => $record->id,
                            'amount' => $additionalAmount,
                            'payment_method' => $data['payment_method'],
                            'paid_at' => now(),
                        ]);
                    })
                    ->color('primary')
                    ->visible(fn(Reservation $record) => $record->guest_status === 'Checked In'),


                Tables\Actions\Action::make('Check-Out')
                    ->icon('heroicon-o-x-mark')
                    ->requiresConfirmation()
                    ->action(function (Reservation $record) {
                        $record->update(['guest_status' => 'Checked Out']);

                        $record->rooms()->update(['status' => 'available']);
                    })
                    ->color('danger')
                    ->visible(fn(Reservation $record) => $record->guest_status === 'Checked In'),
            ])
            ->defaultSort('check_in_date', 'desc');
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
            'index' => Pages\ListCheckInReservations::route('/'),
        ];
    }
}
