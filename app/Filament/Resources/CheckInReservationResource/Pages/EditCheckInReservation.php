<?php

namespace App\Filament\Resources\CheckInReservationResource\Pages;

use App\Filament\Resources\CheckInReservationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCheckInReservation extends EditRecord
{
    protected static string $resource = CheckInReservationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
