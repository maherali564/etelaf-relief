<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\HasPermissionBasedAuthorization;
use App\Filament\Resources\VolunteerResource\Pages;
use App\Models\Volunteer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class VolunteerResource extends Resource
{
    use HasPermissionBasedAuthorization;

    protected static ?string $model = Volunteer::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make(__('filament.resources.volunteer.personal_info'))->schema([
                Forms\Components\TextInput::make('name')->label(__('filament.resources.donation_submission.column_name'))->required(),
                Forms\Components\TextInput::make('email')->label(__('filament.resources.newsletter.column_email'))->email()->required(),
                Forms\Components\TextInput::make('phone')->label(__('filament.resources.volunteer.column_phone'))->required(),
                Forms\Components\TextInput::make('national_id')->label(__('filament.resources.volunteer.national_id')),
                Forms\Components\DatePicker::make('date_of_birth')->label(__('filament.resources.volunteer.date_of_birth')),
                Forms\Components\Textarea::make('address')->label(__('filament.resources.volunteer.address')),
            ])->columns(2),

            Forms\Components\Section::make(__('filament.resources.volunteer.skills_section'))->schema([
                Forms\Components\Select::make('volunteer_opportunity_id')->label(__('filament.resources.volunteer.opportunity'))
                    ->relationship('opportunity', 'title'),
                Forms\Components\Textarea::make('skills')->label(__('filament.resources.volunteer.column_skills')),
                Forms\Components\Textarea::make('availability')->label(__('filament.resources.volunteer.availability')),
                Forms\Components\Textarea::make('message')->label(__('filament.resources.volunteer.message')),
            ]),

            Forms\Components\Section::make(__('filament.resources.volunteer.emergency_section'))->schema([
                Forms\Components\TextInput::make('emergency_contact_name')->label(__('filament.resources.volunteer.emergency_name')),
                Forms\Components\TextInput::make('emergency_contact_phone')->label(__('filament.resources.volunteer.emergency_phone')),
            ])->columns(2),

            Forms\Components\Section::make(__('filament.resources.volunteer.status_section'))->schema([
                Forms\Components\Select::make('status')->label(__('filament.widgets.latest_donations.column_status'))
                    ->options([
                        'pending' => __('filament.resources.donation_submission.status_pending'),
                        'approved' => __('filament.resources.volunteer.status_approved'),
                        'rejected' => __('filament.resources.volunteer.status_rejected'),
                    ])->required()->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state === 'approved') {
                            $set('approved_at', now());
                            $set('rejected_at', null);
                        } elseif ($state === 'rejected') {
                            $set('rejected_at', now());
                            $set('approved_at', null);
                        } else {
                            $set('approved_at', null);
                            $set('rejected_at', null);
                        }
                    }),
                Forms\Components\DateTimePicker::make('approved_at')->label(__('filament.resources.volunteer.approved_at')),
                Forms\Components\DateTimePicker::make('rejected_at')->label(__('filament.resources.volunteer.rejected_at')),
                Forms\Components\Select::make('reviewed_by')->label(__('filament.resources.volunteer.reviewed_by'))
                    ->relationship('reviewer', 'name'),
                Forms\Components\Textarea::make('notes')->label(__('filament.resources.volunteer.notes')),
                Forms\Components\TextInput::make('locale')->label(__('filament.resources.contact_submission.column_locale')),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('name')->label(__('filament.resources.donation_submission.column_name'))->searchable()->sortable(),
            Tables\Columns\TextColumn::make('email')->label(__('filament.resources.newsletter.column_email'))->searchable(),
            Tables\Columns\TextColumn::make('phone')->label(__('filament.resources.volunteer.column_phone')),
            Tables\Columns\SelectColumn::make('status')->label(__('filament.widgets.latest_donations.column_status'))->options([
                'pending' => __('filament.resources.donation_submission.status_pending'),
                'approved' => __('filament.resources.volunteer.status_approved'),
                'rejected' => __('filament.resources.volunteer.status_rejected'),
            ]),
            Tables\Columns\TextColumn::make('skills')->label(__('filament.resources.volunteer.column_skills'))->limit(30)->toggleable(),
            Tables\Columns\TextColumn::make('opportunity.title')->label(__('filament.resources.volunteer.opportunity'))->toggleable(),
            Tables\Columns\TextColumn::make('created_at')->label(__('filament.resources.volunteer.column_created'))->dateTime()->sortable(),
        ])->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')->options([
                    'pending' => __('filament.resources.donation_submission.status_pending'),
                    'approved' => __('filament.resources.volunteer.status_approved'),
                    'rejected' => __('filament.resources.volunteer.status_rejected'),
                ]),
                Filter::make('created_at')->form([
                    Forms\Components\DatePicker::make('created_from')->label(__('filament.resources.volunteer.from_date')),
                    Forms\Components\DatePicker::make('created_until')->label(__('filament.resources.volunteer.to_date')),
                ])->query(function (Builder $query, array $data) {
                    return $query
                        ->when($data['created_from'], fn ($q, $d) => $q->whereDate('created_at', '>=', $d))
                        ->when($data['created_until'], fn ($q, $d) => $q->whereDate('created_at', '<=', $d));
                }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('approve')->label(__('filament.resources.volunteer.approve'))
                    ->icon('heroicon-o-check-circle')->color('success')
                    ->visible(fn ($record) => $record->status === 'pending')
                    ->action(fn ($record) => $record->update([
                        'status' => 'approved', 'approved_at' => now(), 'rejected_at' => null, 'reviewed_by' => auth()->id(),
                    ]))
                    ->requiresConfirmation(),
                Tables\Actions\Action::make('reject')->label(__('filament.resources.volunteer.reject'))
                    ->icon('heroicon-o-x-circle')->color('danger')
                    ->visible(fn ($record) => $record->status === 'pending')
                    ->action(fn ($record) => $record->update([
                        'status' => 'rejected', 'rejected_at' => now(), 'approved_at' => null, 'reviewed_by' => auth()->id(),
                    ]))
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    BulkAction::make('approveAll')->label(__('filament.resources.volunteer.approve_selected'))
                        ->icon('heroicon-o-check-circle')->color('success')
                        ->action(fn (Collection $records) => $records->each->update([
                            'status' => 'approved', 'approved_at' => now(), 'rejected_at' => null, 'reviewed_by' => auth()->id(),
                        ]))
                        ->requiresConfirmation(),
                    BulkAction::make('rejectAll')->label(__('filament.resources.volunteer.reject_selected'))
                        ->icon('heroicon-o-x-circle')->color('danger')
                        ->action(fn (Collection $records) => $records->each->update([
                            'status' => 'rejected', 'rejected_at' => now(), 'approved_at' => null, 'reviewed_by' => auth()->id(),
                        ]))
                        ->requiresConfirmation(),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVolunteers::route('/'),
            'edit' => Pages\EditVolunteer::route('/{record}/edit'),
        ];
    }

    public static function getNavigationLabel(): string
    {
        return __('filament.resources.volunteer.navigation_label');
    }

    public static function getModelLabel(): string
    {
        return __('filament.resources.volunteer.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament.resources.volunteer.plural_label');
    }

    public static function getNavigationGroup(): string
    {
        return __('filament.nav.groups.users');
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::pending()->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return static::getModel()::pending()->count() > 0 ? 'warning' : null;
    }
}
