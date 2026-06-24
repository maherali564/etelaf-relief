<!DOCTYPE html>
<html dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 15px; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #1e293b; }
        .invoice { width: 100%; padding: 10px; }
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #059669; padding-bottom: 10px; margin-bottom: 15px; }
        .header h1 { font-size: 20px; color: #059669; }
        .header .org { font-size: 10px; color: #64748b; }
        .info-table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        .info-table td { padding: 4px 6px; font-size: 10px; }
        .info-table td:first-child { font-weight: 600; color: #475569; width: 120px; }
        .amount-box { text-align: center; padding: 15px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 6px; margin: 10px 0; }
        .amount-box .label { font-size: 10px; color: #64748b; }
        .amount-box .value { font-size: 28px; font-weight: 800; color: #059669; }
        .footer { border-top: 1px solid #e2e8f0; padding-top: 10px; margin-top: 15px; font-size: 9px; color: #94a3b8; text-align: center; }
        .footer strong { color: #475569; }
        .stamp { text-align: center; margin-top: 10px; }
        .stamp .circle { display: inline-block; width: 50px; height: 50px; border-radius: 50%; border: 3px solid #059669; line-height: 44px; font-size: 18px; font-weight: bold; color: #059669; }
    </style>
</head>
<body>
    <div class="invoice">
        <div class="header">
            <div>
                <h1>{{ __('tax_invoice.title') }}</h1>
                <p class="org">{{ config('app.name') }} — {{ __('tax_invoice.organization') }}</p>
            </div>
            <div style="text-align:right">
                <div class="stamp">
                    <div class="circle">{{ strtoupper(substr(__('tax_invoice.stamp'), 0, 1)) }}</div>
                </div>
            </div>
        </div>

        <table class="info-table">
            <tr><td>{{ __('tax_invoice.invoice_no') }}</td><td>{{ config('app.name') }}-INV-{{ str_pad($donation->id, 6, '0', STR_PAD_LEFT) }}-{{ $donation->created_at->format('Y') }}</td></tr>
            <tr><td>{{ __('tax_invoice.date') }}</td><td>{{ $donation->donated_at?->format('Y-m-d') ?: $donation->created_at->format('Y-m-d') }}</td></tr>
            <tr><td>{{ __('tax_invoice.donor_name') }}</td><td>{{ $donation->is_anonymous ? __('common.anonymous') : $donation->donor_name }}</td></tr>
            <tr><td>{{ __('common.email') }}</td><td>{{ $donation->email }}</td></tr>
            <tr><td>{{ __('tax_invoice.transaction_id') }}</td><td>{{ $donation->transaction_id ?? 'TXN-' . $donation->id }}</td></tr>
            @if($donation->project)<tr><td>{{ __('tax_invoice.project') }}</td><td>{{ trans_field($donation->project, 'title') }}</td></tr>@endif
            <tr><td>{{ __('tax_invoice.payment_method') }}</td><td>{{ $donation->paymentMethod?->name ?? '-' }}</td></tr>
        </table>

        <div class="amount-box">
            <p class="label">{{ __('tax_invoice.total_donated') }}</p>
            <p class="value">${{ number_format($donation->amount, 2) }}</p>
        </div>

        <div class="footer">
            <p>{{ __('tax_invoice.footer_text', ['name' => config('app.name')]) }}</p>
            <p style="margin-top:4px"><strong>{{ config('app.name') }}</strong> — {{ __('tax_invoice.thanks') }}</p>
        </div>
    </div>
</body>
</html>
