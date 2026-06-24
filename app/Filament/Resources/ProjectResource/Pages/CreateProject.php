<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Filament\Concerns\HasTranslateAction;
use App\Filament\Resources\ProjectResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProject extends CreateRecord
{
    use HasTranslateAction;

    protected static string $resource = ProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [$this->getTranslateAction()];
    }
}
