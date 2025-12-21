<?php

namespace App\Filament\Resources\AccommodationResource\Pages;

use App\Filament\Resources\AccommodationResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateAccommodation extends CreateRecord
{
    protected static string $resource = AccommodationResource::class;

    // ADD THIS FUNCTION
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}