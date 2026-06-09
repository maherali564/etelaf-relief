<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\HasPermissionBasedAuthorization;
use App\Filament\Resources\VolunteerOpportunityResource\Pages;
use App\Models\VolunteerOpportunity;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class VolunteerOpportunityResource extends Resource
{
    use HasPermissionBasedAuthorization;

    protected static ?string $model = VolunteerOpportunity::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make(__('filament.resources.volunteer_opportunity.details'))->schema([
                Forms\Components\TextInput::make('title.ar')->label(__('filament.resources.volunteer_opportunity.title_ar'))->required(),
                Forms\Components\TextInput::make('title.en')->label(__('filament.resources.volunteer_opportunity.title_en')),
                Forms\Components\Textarea::make('description.ar')->label(__('filament.resources.volunteer_opportunity.description_ar')),
                Forms\Components\Textarea::make('description.en')->label(__('filament.resources.volunteer_opportunity.description_en')),
                Forms\Components\Textarea::make('requirements')->label(__('filament.resources.volunteer_opportunity.requirements')),
                Forms\Components\TextInput::make('location')->label(__('filament.resources.volunteer_opportunity.location')),
            ])->columns(2),

            Forms\Components\Section::make(__('filament.resources.volunteer_opportunity.settings'))->schema([
                Forms\Components\TextInput::make('slots')->label(__('filament.resources.volunteer_opportunity.slots'))->numeric()->minValue(0),
                Forms\Components\TextInput::make('hours_required')->label(__('filament.resources.volunteer_opportunity.hours_required'))->numeric()->minValue(0),
                Forms\Components\DatePicker::make('start_date')->label(__('filament.resources.volunteer_opportunity.start_date')),
                Forms\Components\DatePicker::make('end_date')->label(__('filament.resources.volunteer_opportunity.end_date')),
                Forms\Components\Select::make('status')->label(__('filament.widgets.latest_donations.column_status'))
                    ->options(['active' => __('filament.resources.volunteer_opportunity.active'), 'closed' => __('filament.resources.volunteer_opportunity.closed')])
                    ->required(),
                Forms\Components\Toggle::make('is_active')->label(__('filament.resources.volunteer_opportunity.is_active'))->default(true),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('title')->label(__('filament.resources.volunteer_opportunity.title'))->searchable()
                ->formatStateUsing(fn ($state) => is_array($state) ? ($state[app()->getLocale()] ?? $state['ar'] ?? reset($state)) : $state),
            Tables\Columns\TextColumn::make('location')->label(__('filament.resources.volunteer_opportunity.location'))->searchable(),
            Tables\Columns\TextColumn::make('slots')->label(__('filament.resources.volunteer_opportunity.slots'))->sortable(),
            Tables\Columns\IconColumn::make('is_active')->label(__('filament.resources.volunteer_opportunity.is_active'))->boolean()->sortable(),
            Tables\Columns\TextColumn::make('status')->label(__('filament.widgets.latest_donations.column_status'))
                ->badge()->color(fn ($state) => $state === 'active' ? 'success' : 'danger'),
            Tables\Columns\TextColumn::make('created_at')->label(__('filament.resources.volunteer.column_created'))->dateTime()->sortable(),
        ])->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options([
                    'active' => __('filament.resources.volunteer_opportunity.active'),
                    'closed' => __('filament.resources.volunteer_opportunity.closed'),
                ]),
            ])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVolunteerOpportunities::route('/'),
            'create' => Pages\CreateVolunteerOpportunity::route('/create'),
            'edit' => Pages\EditVolunteerOpportunity::route('/{record}/edit'),
        ];
    }

    public static function getNavigationLabel(): string
    {
        return __('filament.resources.volunteer_opportunity.navigation_label');
    }

    public static function getModelLabel(): string
    {
        return __('filament.resources.volunteer_opportunity.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament.resources.volunteer_opportunity.plural_label');
    }

    public static function getNavigationGroup(): string
    {
        return __('filament.nav.groups.users');
    }
}
