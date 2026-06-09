<?php

namespace App\Filament\Resources\VolunteerTaskResource\Pages;

use App\Filament\Resources\VolunteerTaskResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditVolunteerTask extends EditRecord
{
    protected static string $resource = VolunteerTaskResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
