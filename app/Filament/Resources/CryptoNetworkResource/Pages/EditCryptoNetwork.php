<?php

namespace App\Filament\Resources\CryptoNetworkResource\Pages;

use App\Filament\Resources\CryptoNetworkResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCryptoNetwork extends EditRecord
{
    protected static string $resource = CryptoNetworkResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
