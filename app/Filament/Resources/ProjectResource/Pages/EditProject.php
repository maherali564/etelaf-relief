<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Filament\Concerns\HasTranslateAction;
use App\Filament\Resources\ProjectResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProject extends EditRecord
{
    use HasTranslateAction;

    protected static string $resource = ProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->getTranslateAction(),
            Actions\DeleteAction::make(),
        ];
    }
}
