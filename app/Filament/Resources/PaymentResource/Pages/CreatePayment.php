<?php

namespace App\Filament\Resources\PaymentResource\Pages;

use App\Filament\Resources\PaymentResource;
use App\Models\Reservation;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePayment extends CreateRecord
{
    protected static string $resource = PaymentResource::class;

    protected function afterCreate(): void
    {
        // Retrieve the payment instance with the related reservation
        $payment = $this->record; // The created payment record
        $reservation = Reservation::with('rooms')->find($payment->reservation_id);

        if ($reservation) {
            // Update reservation status to 'complete'
            $reservation->status = 'confirmed';
            $reservation->save();

            $reservation->guest_status = 'Checked In';
            $reservation->save();

            // Update all related rooms to 'occupied'
            foreach ($reservation->rooms as $room) {
                $room->status = 'occupied';
                $room->save();
            }
        }
    }
}
