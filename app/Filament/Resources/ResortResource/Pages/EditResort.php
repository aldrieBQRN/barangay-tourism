<?php

namespace App\Filament\Resources\ResortResource\Pages;

use App\Filament\Resources\ResortResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditResort extends EditRecord
{
    protected static string $resource = ResortResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    // ADD THIS FUNCTION
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}