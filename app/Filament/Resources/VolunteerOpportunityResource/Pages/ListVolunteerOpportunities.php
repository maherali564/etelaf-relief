<?php

namespace App\Filament\Resources\VolunteerOpportunityResource\Pages;

use App\Filament\Resources\VolunteerOpportunityResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListVolunteerOpportunities extends ListRecords
{
    protected static string $resource = VolunteerOpportunityResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
