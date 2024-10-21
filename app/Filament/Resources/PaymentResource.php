<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Filament\Resources\PaymentResource\RelationManagers;
use App\Models\Payment;
use App\Models\Reservation;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('reservation_id')
                    ->label('Reservation')
                    ->required()
                    ->options(
                        Reservation::where('status', 'pending')
                            ->with('guest')
                            ->get()
                            ->mapWithKeys(fn($reservation) => [
                                $reservation->id => $reservation->guest->name
                            ])
                    )
                    ->placeholder('Select a reservation')
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {
                        $reservation = Reservation::with('rooms')->find($state);

                        if ($reservation) {
                            $checkIn = Carbon::parse($reservation->check_in_date);
                            $checkOut = Carbon::parse($reservation->check_out_date);

                            $checkInAtNoon = $checkIn->copy()->setTime(11, 59, 59);
                            if ($checkIn->greaterThan($checkInAtNoon)) {
                                $checkIn->addDay();
                            }

                            $days = max($checkIn->diffInDays($checkOut), 1);

                            $totalRoomPrice = $reservation->rooms->sum('price');

                            $totalAmount = $totalRoomPrice * $days;

                            $formattedAmount = number_format($totalAmount, 0, ',', '.');
                            $set('amount', $formattedAmount);
                        } else {
                            $set('amount', '0');
                        }
                    }),

                TextInput::make('amount')
                    ->label('Amount')
                    ->required()
                    ->prefix('Rp.')
                    ->inputMode('decimal')
                    ->dehydrateStateUsing(fn($state) => (int) str_replace('.', '', $state)),

                Select::make('payment_method')
                    ->label('Payment Method')
                    ->required()
                    ->options([
                        'cash' => 'Cash',
                        'bank_transfer' => 'Bank Transfer',
                        'credit_card' => 'Credit Card',
                    ])
                    ->placeholder('Select a payment method'),

                Hidden::make('paid_at')
                    ->default(now())
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reservation.guest.name')
                    ->label('Guest Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('amount')
                    ->label('Amount')
                    ->formatStateUsing(fn($state) => 'Rp. ' . number_format($state, 0, ',', '.'))
                    ->sortable(),

                TextColumn::make('payment_method')
                    ->label('Payment Method')
                    ->sortable(),

                TextColumn::make('paid_at')
                    ->label('Payment Date')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                //
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
            'index' => Pages\ListPayments::route('/'),
            'create' => Pages\CreatePayment::route('/create'),
            'edit' => Pages\EditPayment::route('/{record}/edit'),
        ];
    }
}
