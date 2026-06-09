<?php

namespace App\Filament\Resources\GazaStatResource\Pages;

use App\Filament\Resources\GazaStatResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGazaStat extends EditRecord
{
    protected static string $resource = GazaStatResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
