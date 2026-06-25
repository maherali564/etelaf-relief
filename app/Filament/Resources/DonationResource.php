<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\HasPermissionBasedAuthorization;
use App\Filament\Resources\DonationResource\Pages;
use App\Mail\DonationReceipt;
use App\Models\Donation;
use App\PDF\DonationReceiptPDF;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

/**
 * ──────────────────────────────────────────────────────────────
 * 📌 المورد: DonationResource
 * ──────────────────────────────────────────────────────────────
 * 🎯 الغرض:
 *    إدارة التبرعات في لوحة التحكم (Filament).
 *    يتيح عرض، تعديل، مراجعة، تصدير، وإعادة إرسال إيصالات
 *    التبرعات مع دعم كامل للصلاحيات والترشيحات.
 * ──────────────────────────────────────────────────────────────
 */
class DonationResource extends Resource
{
    use HasPermissionBasedAuthorization;

    protected static ?string $model = Donation::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    /**
     * ──────────────────────────────────────────────────────────────
     * 📌 الدالة: canCreate
     * ──────────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    منع إنشاء تبرعات جديدة من لوحة التحكم؛ يتم الإنشاء
     *    حصراً عبر واجهة المستخدم العامة.
     *
     * 📥 المدخلات:
     *    - لا توجد
     *
     * 📤 المخرجات:
     *    - bool ← false دائماً (التعطيل)
     * ──────────────────────────────────────────────────────────────
     */
    public static function canCreate(): bool
    {
        return false;
    }

    /**
     * ──────────────────────────────────────────────────────────────
     * 📌 الدالة: getGloballySearchableAttributes
     * ──────────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    تعطيل البحث العام في شريط Filament لبيانات التبرعات
     *    الحساسة؛ لحماية الخصوصية ومنع تسرب المعلومات.
     *
     * 📥 المدخلات:
     *    - لا توجد
     *
     * 📤 المخرجات:
     *    - array ← مصفوفة فارغة (تعطيل البحث)
     * ──────────────────────────────────────────────────────────────
     */
    public static function getGloballySearchableAttributes(): array
    {
        return [];
    }

    /**
     * ──────────────────────────────────────────────────────────────
     * 📌 الدالة: form
     * ──────────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    تعريف نموذج عرض/تعديل التبرع في Filament.
     *    يحتوي على أقسام: بيانات المتبرع، بيانات التبرع،
     *    العلاقات، المراجعة، التأكيد، والإضافات.
     *
     * 📥 المدخلات:
     *    - $form: Form ← كائن النموذج من Filament
     *
     * 📤 المخرجات:
     *    - Form ← النموذج المزود بالمخطط (schema)
     * ──────────────────────────────────────────────────────────────
     */
    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make(__('filament.resources.donation.section.donor'))->schema([
                Forms\Components\TextInput::make('donor_name')->label(__('filament.resources.testimonial.column_donor')),
                Forms\Components\TextInput::make('email')->label(__('filament.resources.newsletter.column_email')),
                Forms\Components\TextInput::make('phone')->label(__('filament.resources.volunteer.column_phone')),
            ])->columns(3),

            Forms\Components\Section::make(__('filament.resources.donation.section.donation'))->schema([
                Forms\Components\TextInput::make('amount')->label(__('filament.widgets.latest_donations.column_amount'))->numeric()->required()->minValue(0.01),
                Forms\Components\TextInput::make('currency')->label(__('filament.resources.payment_confirmation.currency')),
                Forms\Components\Select::make('payment_method_id')->label(__('filament.resources.donation.column_method'))->relationship('paymentMethod', 'name'),
                Forms\Components\TextInput::make('transaction_id')->label(__('filament.resources.donation.transaction_id')),
                Forms\Components\Select::make('status')->label(__('filament.widgets.latest_donations.column_status'))->options([
                    'pending' => __('filament.resources.donation.status_pending'),
                    'under_review' => __('filament.resources.donation_submission.status_pending'),
                    'completed' => __('filament.resources.donation.status_completed'),
                    'failed' => __('filament.resources.donation.status_failed'),
                    'cancelled' => __('filament.resources.donation_submission.status_cancelled'),
                ])->required(),
                Forms\Components\Toggle::make('is_anonymous')->label(__('filament.resources.donation.is_anonymous')),
                Forms\Components\Toggle::make('is_recurring')->label(__('filament.resources.donation.is_recurring')),
                Forms\Components\TextInput::make('recurring_interval')->label(__('filament.resources.donation.recurring_interval')),
            ])->columns(2),

            Forms\Components\Section::make(__('filament.resources.donation.section.relations'))->schema([
                Forms\Components\Select::make('project_id')->label(__('filament.resources.donation.column_project'))->relationship('project', 'title')->nullable()->searchable(),
                Forms\Components\Select::make('story_id')->label(__('filament.resources.donation.column_story'))->relationship('story', 'title')->nullable()->searchable(),
                Forms\Components\Select::make('cryptocurrency_id')->label(__('filament.resources.crypto_network.column_crypto'))->relationship('cryptocurrency', 'name')->nullable()->searchable(),
                Forms\Components\Select::make('crypto_network_id')->label(__('filament.resources.donation.crypto_network'))->relationship('cryptoNetwork', 'network_name')->nullable()->searchable(),
            ])->columns(3),

            Forms\Components\Section::make(__('filament.resources.donation.section.review'))->schema([
                Forms\Components\Placeholder::make('reviewed_by')->label(__('filament.resources.donation.column_reviewer'))
                    ->content(fn ($record) => $record?->reviewer?->name ?? '—'),
                Forms\Components\DateTimePicker::make('reviewed_at')->label(__('filament.resources.donation.column_reviewed')),
                Forms\Components\Textarea::make('rejection_reason')->label(__('filament.resources.donation.rejection_reason'))->rows(3),
            ])->columns(2)->visible(fn ($record) => $record && in_array($record->status, ['completed', 'failed', 'under_review'])),

            Forms\Components\Section::make(__('filament.resources.donation.section.confirmation'))->schema([
                Forms\Components\KeyValue::make('confirmation_details')->label(__('filament.resources.donation.confirmation_details'))
                    ->keyLabel(__('filament.resources.donation.confirmation_key'))->valueLabel(__('filament.resources.gaza_stat.column_value')),
            ])->visible(fn ($record) => $record && filled($record->confirmation_details)),

            Forms\Components\Section::make(__('filament.resources.donation.section.extra'))->schema([
                Forms\Components\DateTimePicker::make('donated_at')->label(__('filament.resources.donation.filter_date')),
                Forms\Components\Textarea::make('notes')->label(__('filament.resources.payment_confirmation.notes')),
                Forms\Components\TextInput::make('locale')->label(__('filament.resources.contact_submission.column_locale')),
            ])->columns(2),
        ]);
    }

    /**
     * ──────────────────────────────────────────────────────────────
     * 📌 الدالة: table
     * ──────────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    تعريف جدول التبرعات في Filament مع الأعمدة،
     *    الترشيحات، الإجراءات الفردية والجماعية.
     *    يتضمن: مراجعة، شهادة، إعادة إرسال الإيصال،
     *    وتصدير CSV.
     *
     * 📥 المدخلات:
     *    - $table: Table ← كائن الجدول من Filament
     *
     * 📤 المخرجات:
     *    - Table ← الجدول المزود بالأعمدة والإجراءات
     * ──────────────────────────────────────────────────────────────
     */
    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('donor_name')->label(__('filament.resources.testimonial.column_donor'))->searchable(),
            Tables\Columns\TextColumn::make('email')->label(__('filament.resources.newsletter.column_email'))->searchable(),
            Tables\Columns\TextColumn::make('amount')->label(__('filament.widgets.latest_donations.column_amount'))->money('USD')->sortable(),
            Tables\Columns\TextColumn::make('paymentMethod.name')->label(__('filament.resources.donation.column_method')),
            Tables\Columns\TextColumn::make('paymentMethod.gateway.name')->label(__('filament.resources.donation.filter_gateway'))->badge()->color('info'),
            Tables\Columns\TextColumn::make('paymentMethod.gateway.driver')->label(__('filament.resources.donation.column_driver'))->badge()->color(fn ($state) => match ($state) {
                'paypal' => 'info',
                'stripe' => 'success',
                'bank_transfer' => 'warning',
                'wise' => 'info',
                'crypto' => 'danger',
                default => 'gray',
            })->formatStateUsing(fn ($state) => match ($state) {
                'stripe' => __('filament.resources.payment_gateway.driver_stripe'),
                'paypal' => __('filament.resources.payment_gateway.driver_paypal'),
                'wise' => __('filament.resources.payment_gateway.driver_wise'),
                'bank_transfer' => __('filament.resources.payment_confirmation.filter_type_bank'),
                'crypto' => __('filament.resources.payment_confirmation.filter_type_crypto'),
                default => $state,
            })->toggleable(),
            Tables\Columns\SelectColumn::make('status')->label(__('filament.widgets.latest_donations.column_status'))->options([
                'pending' => __('filament.resources.donation.status_pending'),
                'under_review' => __('filament.resources.donation_submission.status_pending'),
                'completed' => __('filament.resources.donation.status_completed'),
                'failed' => __('filament.resources.donation.status_failed'),
                'cancelled' => __('filament.resources.donation_submission.status_cancelled'),
            ]),
            Tables\Columns\TextColumn::make('campaign.title')->label(__('filament.resources.donation.column_campaign'))->formatStateUsing(fn ($state) => Str::limit($state, 20))->toggleable(),
            Tables\Columns\TextColumn::make('project.title')->label(__('filament.resources.donation.column_project'))->formatStateUsing(fn ($state) => Str::limit($state, 20))->toggleable(),
            Tables\Columns\TextColumn::make('story.title')->label(__('filament.resources.donation.column_story'))->formatStateUsing(fn ($state) => Str::limit($state, 20))->toggleable(),
            Tables\Columns\TextColumn::make('cryptocurrency.name')->label(__('filament.resources.crypto_network.column_crypto'))->toggleable(),
            Tables\Columns\TextColumn::make('cryptoNetwork.network_name')->label(__('filament.resources.crypto_network.column_network'))->toggleable(),
            Tables\Columns\TextColumn::make('reviewed_by')->label(__('filament.resources.donation.column_reviewer'))
                ->formatStateUsing(fn ($state, $record) => $record?->reviewer?->name ?? '—')->toggleable(),
            Tables\Columns\TextColumn::make('reviewed_at')->label(__('filament.resources.donation.column_reviewed'))->dateTime()->sortable()->toggleable(),
            Tables\Columns\TextColumn::make('donated_at')->label(__('filament.resources.donation.filter_date'))->dateTime()->sortable()->toggleable(),
            Tables\Columns\TextColumn::make('notes')->label(__('filament.resources.payment_confirmation.notes'))->limit(30)->toggleable(),
            Tables\Columns\TextColumn::make('created_at')->label(__('filament.resources.donation.column_created'))->dateTime()->sortable(),
        ])->defaultSort('created_at', 'desc')
            ->modifyQueryUsing(function (Builder $query) {
                $user = auth()->user();
                if ($user && ! $user->hasRole('super_admin')) {
                    $query->whereIn('status', ['pending', 'under_review', 'completed']);
                }
            })
            ->filters([
                Tables\Filters\SelectFilter::make('status')->label(__('filament.widgets.latest_donations.column_status'))->options([
                    'pending' => __('filament.resources.donation.status_pending'),
                    'under_review' => __('filament.resources.donation_submission.status_pending'),
                    'completed' => __('filament.resources.donation.status_completed'),
                    'failed' => __('filament.resources.donation.status_failed'),
                    'cancelled' => __('filament.resources.donation_submission.status_cancelled'),
                ]),
                Tables\Filters\SelectFilter::make('driver')->label(__('filament.resources.donation.filter_gateway'))
                    ->relationship('paymentMethod.gateway', 'driver')
                    ->options([
                        'stripe' => __('filament.resources.payment_gateway.driver_stripe'),
                        'paypal' => __('filament.resources.payment_gateway.driver_paypal'),
                        'bank_transfer' => __('filament.resources.payment_confirmation.filter_type_bank'),
                        'crypto' => __('filament.resources.payment_confirmation.filter_type_crypto'),
                    ]),
                Tables\Filters\Filter::make('created_at')->label(__('filament.resources.donation.filter_date'))
                    ->form([Forms\Components\DatePicker::make('from'), Forms\Components\DatePicker::make('until')])
                    ->query(fn ($query, array $data) => $query->when($data['from'], fn ($q, $d) => $q->whereDate('created_at', '>=', $d))->when($data['until'], fn ($q, $d) => $q->whereDate('created_at', '<=', $d))),
                Tables\Filters\Filter::make('amount_range')->label(__('filament.resources.donation.filter_amount'))
                    ->form([
                        Forms\Components\TextInput::make('amount_from')->label(__('filament.resources.donation.filter_amount_from'))->numeric()->prefix('$'),
                        Forms\Components\TextInput::make('amount_to')->label(__('filament.resources.donation.filter_amount_to'))->numeric()->prefix('$'),
                    ])
                    ->query(fn ($query, array $data) => $query
                        ->when($data['amount_from'], fn ($q, $d) => $q->where('amount', '>=', $d))
                        ->when($data['amount_to'], fn ($q, $d) => $q->where('amount', '<=', $d))
                    ),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->visible(fn () => auth()->user()->can('view_donation')),
                Tables\Actions\EditAction::make()->visible(fn () => auth()->user()->can('update_donation')),
                Tables\Actions\Action::make('review')->label(__('filament.resources.donation.action_review'))
                    ->icon('heroicon-o-eye')
                    ->color('warning')
                    ->visible(fn ($record) => in_array($record->status, ['pending', 'under_review']))
                    ->form([
                        Forms\Components\Select::make('action')->label('الإجراء')->options([
                            'approve' => __('filament.resources.donation.action_approve'),
                            'reject' => __('filament.resources.donation.action_reject'),
                        ])->required()->reactive(),
                        Forms\Components\Textarea::make('rejection_reason')->label(__('filament.resources.donation.rejection_reason'))
                            ->required(fn (Forms\Get $get) => $get('action') === 'reject')
                            ->visible(fn (Forms\Get $get) => $get('action') === 'reject'),
                    ])
                    ->action(function (array $data, Donation $record): void {
                        if ($data['action'] === 'approve') {
                            $record->markCompleted(auth()->id());
                            Notification::make()->title(__('filament.resources.donation.action_approved'))->success()->send();
                        } else {
                            $record->markFailed($data['rejection_reason'] ?? null, auth()->id());
                            Notification::make()->title(__('filament.resources.donation.action_rejected'))->danger()->send();
                        }
                    }),
                Tables\Actions\Action::make('certificate')->label(__('filament.resources.donation.action_certificate'))
                    ->icon('heroicon-o-document-text')
                    ->color('info')
                    ->visible(fn ($record) => $record->status === 'completed')
                    ->url(fn ($record) => URL::route('payment.certificate', ['locale' => app()->getLocale(), 'donation' => $record]), shouldOpenInNewTab: true),
                Tables\Actions\Action::make('receipt_preview')->label(__('filament.resources.donation.action_receipt'))
                    ->icon('heroicon-o-eye')
                    ->color('primary')
                    ->visible(fn ($record) => $record->status === 'completed')
                    ->action(function (Donation $record) {
                        $pdf = app(DonationReceiptPDF::class);
                        return $pdf->stream($record);
                    }),
                Tables\Actions\DeleteAction::make()->label(__('filament.resources.donation.action_delete'))->visible(fn () => auth()->user()->can('delete_donation')),
                Tables\Actions\Action::make('resend_receipt')->label(__('filament.resources.donation.action_resend'))
                    ->icon('heroicon-o-envelope')
                    ->color('success')
                    ->visible(fn ($record) => $record->status === 'completed' && filled($record->email))
                    ->requiresConfirmation()
                    ->action(function (Donation $record): void {
                        try {
                            Mail::to($record->email)->send(new DonationReceipt($record));
                            Notification::make()->title(__('filament.resources.donation.action_receipt_sent'))->success()->send();
                        } catch (\Exception $e) {
                            Notification::make()->title('فشل إرسال الإيصال: '.$e->getMessage())->danger()->send();
                        }
                    }),
            ])
            ->headerActions([
                Tables\Actions\Action::make('export_csv')->label(__('filament.resources.donation.export_csv'))
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(fn () => self::exportCsvResponse(
                        'donations-'.now()->format('Y-m-d').'.csv',
                        Donation::query()->orderByDesc('created_at')
                    )),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('approve_selected')->label(__('filament.resources.donation.action_approve_selected'))
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each(fn ($r) => $r->markCompleted(auth()->id()))),
                    Tables\Actions\BulkAction::make('export_selected_csv')->label(__('filament.resources.donation.export_selected'))
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('info')
                        ->action(fn ($records) => self::exportCsvResponse(
                            'donations-selected-'.now()->format('Y-m-d').'.csv',
                            $records
                        )),
                ]),
            ]);
    }

    /**
     * ──────────────────────────────────────────────────────────────
     * 📌 الدالة: getPages
     * ──────────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    تعريف صفحات المورد: القائمة، التعديل، والعرض.
     *
     * 📥 المدخلات:
     *    - لا توجد
     *
     * 📤 المخرجات:
     *    - array ← مسارات الصفحات مع روابطها
     * ──────────────────────────────────────────────────────────────
     */
    private static function exportCsvResponse(string $filename, \Illuminate\Database\Eloquent\Builder|\Illuminate\Support\Collection $query): \Illuminate\Http\Response
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        $callback = function () use ($query) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, [
                __('filament.resources.testimonial.column_donor'),
                __('filament.resources.newsletter.column_email'),
                __('filament.widgets.latest_donations.column_amount'),
                __('filament.resources.donation.column_method'),
                __('filament.widgets.latest_donations.column_status'),
                __('filament.resources.donation.filter_date'),
            ]);

            $items = $query instanceof \Illuminate\Support\Collection ? $query : $query->cursor();
            foreach ($items as $d) {
                fputcsv($file, [
                    "\t".$d->donor_name,
                    "\t".$d->email,
                    number_format($d->amount, 2),
                    $d->paymentMethod?->name ?? '—',
                    $d->status,
                    $d->created_at->format('Y-m-d H:i'),
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDonations::route('/'),
            'edit' => Pages\EditDonation::route('/{record}/edit'),
            'view' => Pages\ViewDonation::route('/{record}'),
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
        return __('filament.resources.donation.navigation_label');
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
        return __('filament.resources.donation.label');
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
        return __('filament.resources.donation.plural_label');
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
