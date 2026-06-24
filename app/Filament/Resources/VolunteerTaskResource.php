<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\HasPermissionBasedAuthorization;
use App\Filament\Resources\VolunteerTaskResource\Pages;
use App\Models\VolunteerTask;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class VolunteerTaskResource extends Resource
{
    use HasPermissionBasedAuthorization;

    protected static ?string $model = VolunteerTask::class;

    protected static ?string $navigationIcon = 'heroicon-o-check-badge';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make(__('filament.resources.volunteer_task.details'))->schema([
                Forms\Components\Select::make('volunteer_id')->label(__('filament.resources.volunteer_task.volunteer'))
                    ->relationship('volunteer', 'name')->searchable()->required(),
                Forms\Components\Select::make('volunteer_opportunity_id')->label(__('filament.resources.volunteer_task.opportunity'))
                    ->relationship('opportunity', 'title')->searchable(),
                Forms\Components\TextInput::make('title')->label(__('filament.resources.volunteer_task.title'))->required(),
                Forms\Components\Textarea::make('description')->label(__('filament.resources.volunteer_task.description')),
            ])->columns(2),

            Forms\Components\Section::make(__('filament.resources.volunteer_task.tracking'))->schema([
                Forms\Components\Select::make('status')->label(__('filament.widgets.latest_donations.column_status'))
                    ->options([
                        'assigned' => __('filament.resources.volunteer_task.status_assigned'),
                        'in_progress' => __('filament.resources.volunteer_task.status_in_progress'),
                        'completed' => __('filament.resources.volunteer_task.status_completed'),
                        'cancelled' => __('filament.resources.volunteer_task.status_cancelled'),
                    ])->required(),
                Forms\Components\TextInput::make('hours_logged')->label(__('filament.resources.volunteer_task.hours_logged'))
                    ->numeric()->minValue(0)->step(0.5),
                Forms\Components\DateTimePicker::make('started_at')->label(__('filament.resources.volunteer_task.started_at')),
                Forms\Components\DateTimePicker::make('completed_at')->label(__('filament.resources.volunteer_task.completed_at')),
                Forms\Components\Select::make('assigned_by')->label(__('filament.resources.volunteer_task.assigned_by'))
                    ->relationship('assigner', 'name')->default(auth()->id()),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('volunteer.name')->label(__('filament.resources.volunteer_task.volunteer'))->searchable()->sortable(),
            Tables\Columns\TextColumn::make('title')->label(__('filament.resources.volunteer_task.title'))->searchable()->limit(30),
            Tables\Columns\TextColumn::make('status')->label(__('filament.widgets.latest_donations.column_status'))
                ->badge()->color(fn ($state) => match ($state) {
                    'assigned' => 'info',
                    'in_progress' => 'warning',
                    'completed' => 'success',
                    'cancelled' => 'danger',
                    default => 'gray',
                }),
            Tables\Columns\TextColumn::make('hours_logged')->label(__('filament.resources.volunteer_task.hours_logged'))->sortable(),
            Tables\Columns\TextColumn::make('created_at')->label(__('filament.resources.volunteer.column_created'))->dateTime()->sortable(),
        ])->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options([
                    'assigned' => __('filament.resources.volunteer_task.status_assigned'),
                    'in_progress' => __('filament.resources.volunteer_task.status_in_progress'),
                    'completed' => __('filament.resources.volunteer_task.status_completed'),
                    'cancelled' => __('filament.resources.volunteer_task.status_cancelled'),
                ]),
                Tables\Filters\SelectFilter::make('volunteer_id')->label(__('filament.resources.volunteer_task.volunteer'))
                    ->relationship('volunteer', 'name')->searchable(),
            ])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVolunteerTasks::route('/'),
            'create' => Pages\CreateVolunteerTask::route('/create'),
            'edit' => Pages\EditVolunteerTask::route('/{record}/edit'),
        ];
    }

    public static function getNavigationLabel(): string
    {
        return __('filament.resources.volunteer_task.navigation_label');
    }

    public static function getModelLabel(): string
    {
        return __('filament.resources.volunteer_task.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament.resources.volunteer_task.plural_label');
    }

    public static function getNavigationGroup(): string
    {
        return __('filament.nav.groups.users');
    }
}
