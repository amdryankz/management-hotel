<?php

namespace App\Filament\Resources\CheckInReservationResource\Pages;

use App\Filament\Resources\CheckInReservationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCheckInReservations extends ListRecords
{
    protected static string $resource = CheckInReservationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
