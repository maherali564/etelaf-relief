<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"></head>
<body style="font-family: 'Segoe UI', Tahoma, sans-serif; background: #f5f5f5; margin: 0; padding: 0;">
    <table width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;margin:20px auto;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,0.1)">
        <tr><td style="background: linear-gradient(135deg, #059669, #047857); padding: 30px; text-align: center;">
            <h1 style="color: #fff; margin: 0; font-size: 24px;">
                @switch($type)
                    @case('instant') شكراً لتبرعك! @break
                    @case('under_review') تم استلام طلب التبرع @break
                    @case('completed') تم تأكيد تبرعك @break
                    @case('failed') تعذر إتمام التبرع @break
                    @default شكراً لتبرعك
                @endswitch
            </h1>
        </td></tr>
        <tr><td style="padding: 30px;">
            <p style="font-size: 18px; color: #1e293b;">عزيزي {{ $donation->donor_name }}،</p>

            @if($type === 'instant' || $type === 'completed')
            <p style="color: #64748b; line-height: 1.8;">نشكر لك كرمك وتبرعك السخي. تم استلام تبرعك بنجاح.</p>
            @elseif($type === 'under_review')
            <p style="color: #64748b; line-height: 1.8;">تم استلام طلب التبرع الخاص بك. سنقوم بمراجعته وتأكيده في أقرب وقت ممكن.</p>
            @elseif($type === 'failed')
            <p style="color: #64748b; line-height: 1.8;">نأسف لإبلاغك أن عملية التبرع لم تتم بنجاح.</p>
            @if($donation->rejection_reason)
            <p style="color: #dc2626; background: #fef2f2; padding: 12px; border-radius: 8px;">السبب: {{ $donation->rejection_reason }}</p>
            @endif
            @endif

            <table style="width:100%;margin:20px 0;background:#f8fafc;border-radius:8px;padding:16px">
                <tr><td style="padding:8px;color:#64748b;">المبلغ</td><td style="padding:8px;font-weight:bold;">${{ number_format($donation->amount, 2) }}</td></tr>
                <tr><td style="padding:8px;color:#64748b;">رقم العملية</td><td style="padding:8px;font-weight:bold;">{{ $donation->transaction_id }}</td></tr>
                <tr><td style="padding:8px;color:#64748b;">التاريخ</td><td style="padding:8px;font-weight:bold;">{{ $donation->donated_at?->format('Y-m-d') ?: $donation->created_at->format('Y-m-d') }}</td></tr>
                @if($donation->project)<tr><td style="padding:8px;color:#64748b;">المشروع</td><td style="padding:8px;font-weight:bold;">{{ trans_field($donation->project, 'title') }}</td></tr>@endif

            </table>

            <a href="{{ route('home', ['locale' => $donation->locale]) }}" style="display:inline-block;background:#059669;color:#fff;padding:12px 30px;border-radius:8px;text-decoration:none;margin-top:20px;">العودة إلى الموقع</a>
        </td></tr>
        <tr><td style="background:#f8fafc;padding:20px;text-align:center;color:#94a3b8;font-size:12px;">
            &copy; {{ date('Y') }} {{ config('app.name') }}. جميع الحقوق محفوظة.
        </td></tr>
    </table>
</body>
</html>
