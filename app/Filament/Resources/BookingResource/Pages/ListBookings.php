<?php

namespace App\Filament\Resources\BookingResource\Pages;

use App\Filament\Resources\BookingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBookings extends ListRecords
{
    protected static string $resource = BookingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // NEW: QR Scan Button
            Actions\Action::make('scan_qr')
                ->label('Scan QR')
                ->icon('heroicon-m-qr-code')
                ->color('primary')
                ->modalHeading('Scan Guest Ticket')
                ->modalSubmitAction(false) // Hide the 'Confirm' button
                ->modalCancelAction(false) // Hide the 'Cancel' button
                ->modalContent(view('filament.pages.scan-qr')), // Load our camera view

            Actions\CreateAction::make(),
        ];
    }
}