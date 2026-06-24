<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"></head>
<body style="font-family: 'Segoe UI', Tahoma, sans-serif; background: #f5f5f5; margin: 0; padding: 0;">
    <table width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;margin:20px auto;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,0.1)">
        <tr><td style="background: linear-gradient(135deg, #059669, #047857); padding: 30px; text-align: center;">
            <h1 style="color: #fff; margin: 0;">إيصال تبرع</h1>
        </td></tr>
        <tr><td style="padding: 30px; text-align: center;">
            <p style="font-size: 18px;">شكراً لك {{ $donation->donor_name }} على تبرعك السخي</p>
            <div style="font-size: 48px; margin: 20px 0; color: #059669;">✓</div>
            <p style="color: #64748b;">تم إرفاق إيصال التبرع بهذا البريد بصيغة PDF</p>
            <p style="color: #64748b;">يمكنك تحميل الإيصال من المرفقات</p>
        </td></tr>
    </table>
</body>
</html>
