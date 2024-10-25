<?php

namespace App\Filament\Resources;

use App\Filament\Exports\PaymentExporter;
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
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

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

                            // Hitung harga total kamar
                            $totalRoomPrice = $reservation->rooms->sum('price') * $days;

                            // Hitung biaya extra bed
                            $extraBedCost = $reservation->extra_bed * $reservation->extra_bed_price * $days;

                            // Total amount = harga kamar + biaya extra bed
                            $totalAmount = $totalRoomPrice + $extraBedCost;

                            $formattedAmount = number_format($totalAmount, 0, ',', '.');
                            $set('amount', $formattedAmount);
                        } else {
                            $set('amount', '0');
                        }
                    }),

                TextInput::make('amount')
                    ->label('Amount')
                    ->required()
                    ->readOnly()
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

                TextInput::make('amount_paid_1')
                    ->label('Amount Paid (Method 1)')
                    ->required()
                    ->prefix('Rp.')
                    ->inputMode('decimal')
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {
                        $cleanedValue = str_replace('.', '', $state);
                        if (is_numeric($cleanedValue)) {
                            $formattedValue = number_format($cleanedValue, 0, ',', '.');
                            $set('amount_paid_1', $formattedValue);
                        }
                    })
                    ->dehydrateStateUsing(fn($state) => (int) str_replace('.', '', $state)),

                // Input untuk metode pembayaran kedua (opsional)
                Select::make('payment_method_2')
                    ->label('Payment Method')
                    ->nullable()
                    ->options([
                        'cash' => 'Cash',
                        'bank_transfer' => 'Bank Transfer',
                        'credit_card' => 'Credit Card',
                    ])
                    ->placeholder('Select a payment method'),

                TextInput::make('amount_paid_2')
                    ->label('Amount Paid (Method 2)')
                    ->nullable()
                    ->prefix('Rp.')
                    ->inputMode('decimal')
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {
                        $cleanedValue = str_replace('.', '', $state);
                        if (is_numeric($cleanedValue)) {
                            $formattedValue = number_format($cleanedValue, 0, ',', '.');
                            $set('amount_paid_2', $formattedValue);
                        }
                    })
                    ->dehydrateStateUsing(fn($state) => (int) str_replace('.', '', $state)),

                Hidden::make('paid_at')->default(now()),

                Hidden::make('reserved_by')->default(Auth::id())
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
                    ->formatStateUsing(function ($state, $record) {
                        $method1 = $record->payment_method;
                        $method2 = $record->payment_method_2;

                        if ($method1 && $method2) {
                            return ucfirst($method1) . ' & ' . ucfirst($method2);
                        } elseif ($method1) {
                            return ucfirst($method1);
                        } elseif ($method2) {
                            return ucfirst($method2);
                        }
                    })
                    ->sortable(),

                TextColumn::make('paid_at')
                    ->label('Payment Date')
                    ->dateTime()
                    ->sortable()
                    ->formatStateUsing(fn($state) => \Carbon\Carbon::parse($state)->translatedFormat('j F Y, H:i')),

                TextColumn::make('reservation.user.name')
                    ->label('Reserved By')
                    ->sortable()
                    ->searchable(),
            ])
            ->headerActions([
                ExportAction::make()
                    ->exporter(PaymentExporter::class)
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
