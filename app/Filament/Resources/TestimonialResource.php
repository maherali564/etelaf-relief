<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\HasLocaleTabs;
use App\Filament\Concerns\HasPermissionBasedAuthorization;
use App\Filament\Resources\TestimonialResource\Pages;
use App\Models\Testimonial;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class TestimonialResource extends Resource
{
    use HasLocaleTabs;
    use HasPermissionBasedAuthorization;

    protected static ?string $model = Testimonial::class;

    protected static ?string $navigationIcon = 'heroicon-o-star';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('donor_name')->label(__('filament.resources.testimonial.column_donor'))->required(),
            static::localeTabs('content', __('filament.pages.manage_site_settings.about_content'), 'textarea'),
            Forms\Components\TextInput::make('rating')->label(__('filament.resources.testimonial.column_rating'))->numeric()->minValue(1)->maxValue(5)->default(5),
            FileUpload::make('image')->label(__('filament.resources.testimonial.image'))->image()->directory('testimonials')->nullable()
                ->afterStateHydrated(function (FileUpload $component, $state) {
                    if (is_string($state) && filled($state)) {
                        $component->state([$state]);
                    }
                })
                ->deleteUploadedFileUsing(fn ($file) => Storage::disk('public')->delete($file))
                ->removeUploadedFileButtonPosition('right'),
            Forms\Components\Toggle::make('is_active')->label(__('filament.resources.user.column_active'))->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('donor_name')->label(__('filament.resources.testimonial.column_donor'))->searchable(),
            Tables\Columns\TextColumn::make('content')->label(__('filament.pages.manage_site_settings.about_content'))->formatStateUsing(fn ($state, $record) => $record?->getTranslation('content', 'ar') ?? '')->limit(50),
            Tables\Columns\TextColumn::make('rating')->label(__('filament.resources.testimonial.column_rating')),
            Tables\Columns\IconColumn::make('is_active')->label(__('filament.resources.user.column_active'))->boolean(),
        ])->defaultSort('created_at', 'desc')
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTestimonials::route('/'),
            'create' => Pages\CreateTestimonial::route('/create'),
            'edit' => Pages\EditTestimonial::route('/{record}/edit'),
        ];
    }

    public static function getNavigationLabel(): string
    {
        return __('filament.resources.testimonial.navigation_label');
    }

    public static function getModelLabel(): string
    {
        return __('filament.resources.testimonial.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament.resources.testimonial.plural_label');
    }

    public static function getNavigationGroup(): string
    {
        return __('filament.nav.groups.campaigns_donations');
    }
}
