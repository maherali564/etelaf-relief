<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head><meta charset="UTF-8"><style>
    body { font-family: 'DejaVu Sans', sans-serif; padding: 40px; color: #1e293b; }
    .header { text-align: center; border-bottom: 2px solid #059669; padding-bottom: 20px; margin-bottom: 30px; }
    .header h1 { color: #059669; margin: 0; font-size: 24px; }
    .header p { color: #64748b; margin: 5px 0 0; }
    .details { margin: 30px 0; }
    .details table { width: 100%; border-collapse: collapse; }
    .details td { padding: 12px; border-bottom: 1px solid #e2e8f0; }
    .details td:first-child { font-weight: bold; color: #64748b; width: 40%; }
    .amount { text-align: center; font-size: 28px; color: #059669; margin: 30px 0; padding: 20px; background: #f0fdf4; border-radius: 8px; }
    .footer { text-align: center; color: #94a3b8; font-size: 12px; margin-top: 50px; border-top: 1px solid #e2e8f0; padding-top: 20px; }
    .status { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 12px; }
    .status.completed { background: #dcfce7; color: #166534; }
</style></head>
<body>
    <div class="header">
        <h1>{{ $site_name }}</h1>
        <p>إيصال تبرع</p>
    </div>

    <div class="amount">
        ${{ $amount }}
    </div>

    <div class="details">
        <table>
            <tr><td>اسم المتبرع</td><td>{{ $donor_name }}</td></tr>
            <tr><td>المبلغ</td><td>${{ $amount }} {{ $currency }}</td></tr>
            <tr><td>رقم العملية</td><td>{{ $transaction_id }}</td></tr>
            <tr><td>التاريخ</td><td>{{ $date }}</td></tr>
            <tr><td>الحالة</td><td><span class="status completed">مكتمل</span></td></tr>
            <tr><td>طريقة الدفع</td><td>{{ $payment_method ?? '—' }}</td></tr>
            @if($project)<tr><td>المشروع</td><td>{{ $project }}</td></tr>@endif
            @if($campaign)<tr><td>الحملة</td><td>{{ $campaign }}</td></tr>@endif
            @if($story)<tr><td>القصة</td><td>{{ $story }}</td></tr>@endif
        </table>
    </div>

    <div class="footer">
        <p>شكراً لك على دعمك السخي</p>
        <p>{{ $site_name }} &copy; {{ date('Y') }}</p>
    </div>
</body>
</html>
