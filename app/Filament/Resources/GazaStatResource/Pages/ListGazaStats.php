<?php

namespace App\Filament\Resources\GazaStatResource\Pages;

use App\Filament\Resources\GazaStatResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGazaStats extends ListRecords
{
    protected static string $resource = GazaStatResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
