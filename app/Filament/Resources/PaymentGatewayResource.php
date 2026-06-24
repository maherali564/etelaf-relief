<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\HasPermissionBasedAuthorization;
use App\Filament\Resources\PaymentGatewayResource\Pages;
use App\Models\PaymentGateway;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * ──────────────────────────────────────────────────────────────
 * 📌 المورد: PaymentGatewayResource
 * ──────────────────────────────────────────────────────────────
 * 🎯 الغرض:
 *    إدارة بوابات الدفع في لوحة التحكم (Filament).
 *    يتيح إنشاء وتعديل وحذف بوابات الدفع (Stripe, PayPal,
 *    Wise, bank_transfer, crypto, manual) مع إعدادات
 *    خاصة لكل بوابة: المفاتيح، التوكنات، تعليمات الدفع،
 *    العملات المدعومة، والرسوم.
 * ──────────────────────────────────────────────────────────────
 */
class PaymentGatewayResource extends Resource
{
    use HasPermissionBasedAuthorization;

    protected static ?string $model = PaymentGateway::class;

    protected static ?string $navigationIcon = 'heroicon-o-server-stack';

    /**
     * ──────────────────────────────────────────────────────────────
     * 📌 الدالة: form
     * ──────────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    تعريف نموذج إنشاء/تعديل بوابة الدفع.
     *    يحتوي على أقسام مشروطة (conditional sections) تظهر
     *    حسب نوع المحرك (driver) المختار: Stripe, PayPal,
     *    Wise, bank_transfer, crypto, manual/other.
     *    يتضمن أيضاً رفع الشعار وتعليمات الدفع متعددة اللغات.
     *
     * 📥 المدخلات:
     *    - $form: Form ← كائن النموذج من Filament
     *
     * 📤 المخرجات:
     *    - Form ← النموذج المزود بالمخطط الديناميكي
     * ──────────────────────────────────────────────────────────────
     */
    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make(__('filament.resources.payment_gateway.section.general'))->schema([
                Forms\Components\TextInput::make('name')->label(__('filament.resources.payment_gateway.name'))->required()
                    ->afterStateUpdated(fn (Forms\Set $set, $state) => $set('slug', Str::slug($state))),
                Forms\Components\TextInput::make('slug')->label(__('filament.resources.payment_gateway.slug'))->required()->unique(ignoreRecord: true),
                Forms\Components\Select::make('driver')->label(__('filament.resources.donation.column_driver'))->options([
                    'stripe' => __('filament.resources.payment_gateway.driver_stripe'),
                    'paypal' => __('filament.resources.payment_gateway.driver_paypal'),
                    'bank_transfer' => __('filament.resources.payment_confirmation.filter_type_bank'),
                    'wise' => __('filament.resources.payment_gateway.driver_wise'),
                    'crypto' => __('filament.resources.payment_gateway.driver_crypto'),
                    'manual' => __('filament.resources.payment_gateway.driver_manual'),
                    'other' => __('filament.resources.payment_gateway.driver_other'),
                ])->required()->reactive()
                    ->afterStateUpdated(fn (Forms\Set $set, $state) => $set('type', match ($state) {
                        'stripe', 'paypal' => 'traditional',
                        'bank_transfer', 'wise' => 'bank_transfer',
                        'crypto' => 'crypto',
                        default => 'traditional',
                    })),
                Forms\Components\Select::make('type')->label(__('filament.resources.statistic.column_type'))->options([
                    'traditional' => __('filament.resources.payment_gateway.type_traditional'),
                    'crypto' => __('filament.resources.payment_confirmation.filter_type_crypto'),
                    'bank_transfer' => __('filament.resources.payment_confirmation.filter_type_bank'),
                ])->required(),
                Forms\Components\Select::make('supported_currencies')->label(__('filament.resources.payment_gateway.currencies'))
                    ->multiple()->options([
                        'USD' => 'USD', 'EUR' => 'EUR', 'GBP' => 'GBP', 'SAR' => 'SAR', 'AED' => 'AED',
                    ])->default(['USD']),
                Forms\Components\Grid::make(3)->schema([
                    Forms\Components\TextInput::make('min_amount')->label(__('filament.resources.payment_gateway.min_amount'))->numeric()->prefix('$'),
                    Forms\Components\TextInput::make('max_amount')->label(__('filament.resources.payment_gateway.max_amount'))->numeric()->prefix('$'),
                    Forms\Components\TextInput::make('processing_fee')->label(__('filament.resources.payment_gateway.processing_fee'))->numeric()->step(0.01)->suffix('%'),
                ]),
                Forms\Components\TextInput::make('webhook_url')->label(__('filament.resources.payment_gateway.webhook_url'))->url()->disabled()->dehydrated(false)
                    ->helperText(__('filament.resources.payment_gateway.webhook_auto')),
                Forms\Components\Toggle::make('is_active')->label(__('filament.resources.user.column_active'))->default(true),
                Forms\Components\TextInput::make('sort_order')->label(__('filament.resources.gaza_stat.sort_order'))->numeric()->default(0),
                FileUpload::make('logo')->label(__('filament.pages.manage_site_settings.logo'))->image()->directory('gateways')->disk('public')->visibility('public')->nullable()
                    ->afterStateHydrated(function (FileUpload $component, $state) {
                        if (is_string($state) && filled($state)) {
                            $component->state([$state]);
                        }
                    })
                    ->deleteUploadedFileUsing(fn ($file) => Storage::disk('public')->delete($file))
                    ->removeUploadedFileButtonPosition('right'),
            ]),

            Forms\Components\Section::make(__('filament.resources.payment_gateway.section.instructions'))
                ->schema([
                    Forms\Components\Repeater::make('payment_instructions')->label('')
                        ->schema([
                            Forms\Components\Select::make('locale')->label(__('filament.resources.contact_submission.column_locale'))->options([
                                'ar' => __('filament.resources.user.locale_ar'),
                                'en' => __('filament.resources.user.locale_en'),
                                'fr' => 'Français',
                                'tr' => 'Türkçe',
                            ])->required(),
                            Forms\Components\Textarea::make('instructions')->label(__('filament.resources.payment_gateway.instructions_text'))->rows(4)->required(),
                        ])->columns(2)->defaultItems(1)->addActionLabel(__('filament.resources.payment_gateway.add_language')),
                ]),

            Forms\Components\Section::make(__('filament.resources.payment_gateway.section.stripe'))
                ->visible(fn (Forms\Get $get) => $get('driver') === 'stripe')
                ->schema([
                    Forms\Components\TextInput::make('config.publishable_key')->label(__('filament.resources.payment_gateway.stripe_publishable'))
                        ->placeholder('pk_live_...')->password()->revealable(),
                    Forms\Components\TextInput::make('config.secret_key')->label(__('filament.resources.payment_gateway.stripe_secret'))
                        ->placeholder('sk_live_...')->password()->revealable(),
                    Forms\Components\TextInput::make('config.webhook_secret')->label(__('filament.resources.payment_gateway.wise_webhook_secret'))
                        ->placeholder('whsec_...')->password()->revealable(),
                ]),

            Forms\Components\Section::make(__('filament.resources.payment_gateway.section.paypal'))
                ->visible(fn (Forms\Get $get) => $get('driver') === 'paypal')
                ->schema([
                    Forms\Components\TextInput::make('config.client_id')->label(__('filament.resources.payment_gateway.paypal_client_id'))
                        ->placeholder('AeF7x...')->password()->revealable(),
                    Forms\Components\TextInput::make('config.client_secret')->label(__('filament.resources.payment_gateway.paypal_client_secret'))
                        ->placeholder('EHuP7...')->password()->revealable(),
                    Forms\Components\Select::make('config.mode')->label(__('filament.resources.payment_gateway.paypal_mode'))->options([
                        'sandbox' => __('filament.resources.payment_gateway.paypal_sandbox'),
                        'live' => __('filament.resources.payment_gateway.paypal_live'),
                    ])->default('sandbox'),
                ]),

            Forms\Components\Section::make(__('filament.resources.payment_gateway.section.wise'))
                ->visible(fn (Forms\Get $get) => $get('driver') === 'wise')
                ->schema([
                    Forms\Components\TextInput::make('config.api_token')->label(__('filament.resources.payment_gateway.wise_api_token'))
                        ->placeholder('wise-api-token')->password()->revealable(),
                    Forms\Components\TextInput::make('config.profile_id')->label(__('filament.resources.payment_gateway.wise_profile_id'))
                        ->placeholder('12345678'),
                    Forms\Components\TextInput::make('config.webhook_secret')->label(__('filament.resources.payment_gateway.wise_webhook_secret'))
                        ->placeholder('whsec_...')->password()->revealable(),
                ]),

            Forms\Components\Section::make(__('filament.resources.payment_gateway.section.bank'))
                ->visible(fn (Forms\Get $get) => $get('driver') === 'bank_transfer')
                ->schema([
                    Forms\Components\TextInput::make('config.bank_name')->label(__('filament.resources.payment_gateway.bank_name')),
                    Forms\Components\TextInput::make('config.account_name')->label(__('filament.resources.payment_gateway.bank_account_name')),
                    Forms\Components\TextInput::make('config.account_number')->label(__('filament.resources.payment_gateway.bank_account_number')),
                    Forms\Components\TextInput::make('config.iban')->label(__('filament.resources.payment_gateway.bank_iban')),
                    Forms\Components\TextInput::make('config.swift_code')->label(__('filament.resources.payment_gateway.bank_swift')),
                ]),

            Forms\Components\Section::make(__('filament.resources.payment_gateway.section.crypto'))
                ->visible(fn (Forms\Get $get) => $get('driver') === 'crypto')
                ->schema([
                    Forms\Components\Placeholder::make('crypto_info')
                        ->label('')
                        ->content(__('filament.resources.payment_gateway.crypto_placeholder')),
                ]),

            Forms\Components\Section::make(__('filament.resources.payment_gateway.section.extra'))
                ->visible(fn (Forms\Get $get) => in_array($get('driver'), ['manual', 'other', '']))
                ->schema([
                    Forms\Components\KeyValue::make('config')->label(__('filament.pages.manage_site_settings.navigation_group'))
                        ->keyLabel(__('filament.resources.payment_gateway.config_key'))->valueLabel(__('filament.resources.gaza_stat.column_value'))
                        ->addActionLabel(__('filament.resources.payment_gateway.add_field')),
                ]),
        ]);
    }

    /**
     * ──────────────────────────────────────────────────────────────
     * 📌 الدالة: table
     * ──────────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    تعريف جدول بوابات الدفع في Filament مع الأعمدة،
     *    الترشيحات (حسب النوع والمحرك والحالة)، والإجراءات.
     *
     * 📥 المدخلات:
     *    - $table: Table ← كائن الجدول من Filament
     *
     * 📤 المخرجات:
     *    - Table ← الجدول المزود بالأعمدة والترشيحات
     * ──────────────────────────────────────────────────────────────
     */
    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('name')->label(__('filament.resources.payment_gateway.column_gateway'))->searchable(),
            Tables\Columns\TextColumn::make('slug')->label(__('filament.resources.payment_gateway.slug'))->searchable()->toggleable(),
            Tables\Columns\TextColumn::make('type')->label(__('filament.resources.statistic.column_type'))->badge()->color(fn ($state) => match ($state) {
                'traditional' => 'success',
                'crypto' => 'warning',
                'bank_transfer' => 'info',
                default => 'gray',
            }),
            Tables\Columns\TextColumn::make('driver')->label(__('filament.resources.donation.column_driver'))->badge()->color(fn ($state) => match ($state) {
                'paypal' => 'info',
                'stripe' => 'success',
                'bank_transfer' => 'warning',
                'crypto' => 'danger',
                'wise' => 'primary',
                default => 'gray',
            }),
            Tables\Columns\TextColumn::make('paymentMethods_count')->label(__('filament.resources.payment_method.navigation_label'))->counts('paymentMethods'),
            Tables\Columns\TextColumn::make('supported_currencies')->label(__('filament.resources.payment_gateway.column_currencies'))->badge()
                ->formatStateUsing(fn ($state) => is_array($state) ? implode(', ', $state) : $state),
            Tables\Columns\TextColumn::make('min_amount')->label(__('filament.resources.payment_gateway.column_min'))->money('USD')->toggleable(),
            Tables\Columns\TextColumn::make('max_amount')->label(__('filament.resources.payment_gateway.column_max'))->money('USD')->toggleable(),
            Tables\Columns\TextColumn::make('processing_fee')->label(__('filament.resources.payment_gateway.column_fee'))->suffix('%')->toggleable(),
            Tables\Columns\IconColumn::make('is_active')->label(__('filament.resources.user.column_active'))->boolean(),
            Tables\Columns\TextColumn::make('sort_order')->label(__('filament.resources.gaza_stat.sort_order'))->sortable(),
        ])->defaultSort('sort_order')
            ->filters([
                Tables\Filters\SelectFilter::make('type')->label(__('filament.resources.statistic.column_type'))->options([
                    'traditional' => __('filament.resources.payment_gateway.type_traditional'),
                    'crypto' => __('filament.resources.payment_confirmation.filter_type_crypto'),
                    'bank_transfer' => __('filament.resources.payment_confirmation.filter_type_bank'),
                ]),
                Tables\Filters\SelectFilter::make('driver')->label(__('filament.resources.donation.column_driver'))->options([
                    'stripe' => __('filament.resources.payment_gateway.driver_stripe'),
                    'paypal' => __('filament.resources.payment_gateway.driver_paypal'),
                    'bank_transfer' => __('filament.resources.payment_confirmation.filter_type_bank'),
                    'wise' => __('filament.resources.payment_gateway.driver_wise'),
                    'crypto' => __('filament.resources.payment_confirmation.filter_type_crypto'),
                    'manual' => __('filament.resources.payment_gateway.driver_manual'),
                ]),
                Tables\Filters\TernaryFilter::make('is_active')->label(__('filament.widgets.latest_donations.column_status'))->trueLabel(__('filament.resources.user.column_active'))->falseLabel(__('filament.resources.payment_gateway.filter_inactive')),
            ])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    /**
     * ──────────────────────────────────────────────────────────────
     * 📌 الدالة: getPages
     * ──────────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    تعريف صفحات المورد: القائمة، الإنشاء، والتعديل.
     *
     * 📥 المدخلات:
     *    - لا توجد
     *
     * 📤 المخرجات:
     *    - array ← مسارات الصفحات مع روابطها
     * ──────────────────────────────────────────────────────────────
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPaymentGateways::route('/'),
            'create' => Pages\CreatePaymentGateway::route('/create'),
            'edit' => Pages\EditPaymentGateway::route('/{record}/edit'),
        ];
    }

    /**
     * ──────────────────────────────────────────────────────────────
     * 📌 الدالة: getNavigationLabel
     * ──────────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    تسمية عنصر التنقل في الشريط الجانبي.
     *
     * 📥 المدخلات:
     *    - لا توجد
     *
     * 📤 المخرجات:
     *    - string ← النص المترجم للتسمية
     * ──────────────────────────────────────────────────────────────
     */
    public static function getNavigationLabel(): string
    {
        return __('filament.resources.payment_gateway.navigation_label');
    }

    /**
     * ──────────────────────────────────────────────────────────────
     * 📌 الدالة: getModelLabel
     * ──────────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    تسمية المفرد للنموذج (للاستخدام في العناوين).
     *
     * 📥 المدخلات:
     *    - لا توجد
     *
     * 📤 المخرجات:
     *    - string ← النص المترجم للمفرد
     * ──────────────────────────────────────────────────────────────
     */
    public static function getModelLabel(): string
    {
        return __('filament.resources.payment_gateway.label');
    }

    /**
     * ──────────────────────────────────────────────────────────────
     * 📌 الدالة: getPluralModelLabel
     * ──────────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    تسمية الجمع للنموذج (للاستخدام في العناوين).
     *
     * 📥 المدخلات:
     *    - لا توجد
     *
     * 📤 المخرجات:
     *    - string ← النص المترجم للجمع
     * ──────────────────────────────────────────────────────────────
     */
    public static function getPluralModelLabel(): string
    {
        return __('filament.resources.payment_gateway.plural_label');
    }

    /**
     * ──────────────────────────────────────────────────────────────
     * 📌 الدالة: getNavigationGroup
     * ──────────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    تحديد المجموعة التي ينتمي إليها المورد في الشريط الجانبي.
     *
     * 📥 المدخلات:
     *    - لا توجد
     *
     * 📤 المخرجات:
     *    - string ← اسم المجموعة المترجم
     * ──────────────────────────────────────────────────────────────
     */
    public static function getNavigationGroup(): string
    {
        return __('filament.nav.groups.campaigns_donations');
    }
}
