<?php

namespace App\Filament\Exports;

use App\Models\Payment;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Database\Eloquent\Builder;

class PaymentExporter extends Exporter
{
    protected static ?string $model = Payment::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('reservation.guest.name')
                ->label('Nama Tamu'),

            ExportColumn::make('reservation.rooms.room_number')
                ->getStateUsing(function ($record) {
                    return $record->rooms->pluck('room_number')->join(', ');
                })
                ->label('Ruangan'),

            ExportColumn::make('reservation.check_in_date')
                ->label('Tanggal Masuk')
                ->formatStateUsing(fn($state) => \Carbon\Carbon::parse($state)->translatedFormat('j F Y')),

            ExportColumn::make('reservation.check_out_date')
                ->label('Tanggal Keluar')
                ->formatStateUsing(fn($state) => \Carbon\Carbon::parse($state)->translatedFormat('j F Y')),

            ExportColumn::make('reservation.extra_bed')
                ->label('Extra Bed'),

            ExportColumn::make('amount')
                ->label('Tarif')
                ->formatStateUsing(fn($state) => 'Rp. ' . number_format($state, 0, ',', '.')),

            ExportColumn::make('payment_method')
                ->label('Pembayaran')
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
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your payment export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
