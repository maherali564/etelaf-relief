<?php

namespace App\Filament\Resources\DonationSubmissionResource\Pages;

use App\Filament\Resources\DonationSubmissionResource;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\EditRecord;

class EditDonationSubmission extends EditRecord
{
    protected static string $resource = DonationSubmissionResource::class;

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->disabled(),
            Forms\Components\TextInput::make('email')->disabled(),
            Forms\Components\TextInput::make('amount')->disabled(),
            Forms\Components\Select::make('status')->options([
                'pending' => 'قيد المراجعة',
                'confirmed' => 'مؤكد',
                'cancelled' => 'ملغي',
            ]),
        ]);
    }
}
