<?php

namespace App\Filament\Resources\StoryResource\Pages;

use App\Filament\Concerns\HasTranslateAction;
use App\Filament\Resources\StoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateStory extends CreateRecord
{
    use HasTranslateAction;

    protected static string $resource = StoryResource::class;

    protected function getHeaderActions(): array
    {
        return [$this->getTranslateAction()];
    }
}
