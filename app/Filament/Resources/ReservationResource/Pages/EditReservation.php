<?php

namespace App\Filament\Resources\ReservationResource\Pages;

use App\Filament\Resources\ReservationResource;
use App\Models\Guest;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditReservation extends EditRecord
{
    protected static string $resource = ReservationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if ($data['guest_selection'] === 'new') {
            $guest = Guest::create([
                'name' => $data['guest_name'],
                'phone' => $data['guest_phone'],
            ]);
            $data['guest_id'] = $guest->id;
        }

        unset($data['guest_selection'], $data['guest_name'], $data['guest_phone']);

        return $data;
    }
}
