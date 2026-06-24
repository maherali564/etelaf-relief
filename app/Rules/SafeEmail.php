<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * قاعدة التحقق من سلامة البريد الإلكتروني من هجمات CRLF Injection.
 *
 * **ما تخفف منه هذه القاعدة:**
 * تقوم بالتخفيف من ثغرة **CVE-2026-48019** — وهي هجوم حقن CRLF
 * (Carriage Return \r / Line Feed \n) في قاعدة التحقق الافتراضية
 * `email` في Laravel 11. لا يوجد تصحيح رسمي (patch) لهذه الثغرة
 * للإصدار 11، لذا تم إنشاء هذه القاعدة المخصصة كإجراء وقائي.
 *
 * **كيف تعمل:**
 * تتحقق من عدم وجود الأحرف `\r` (CR) أو `\n` (LF) في قيمة البريد
 * الإلكتروني. إذا وُجد أي منهما، يفشل التحقق ويعيد رسالة الخطأ
 * المترجمة من ملف `validation.email`.
 *
 * **لماذا هذا مهم:**
 * يمكن للمهاجم استخدام CRLF Injection لـ:
 * - إضافة ترويسات HTTP إضافية (HTTP Header Injection)
 * - تزوير البريد الإلكتروني (Email Header Injection)
 * - شن هجمات انشقاق الاستجابة (HTTP Response Splitting)
 *
 * @package App\Rules
 */
class SafeEmail implements ValidationRule
{
    /**
     * التحقق من أن البريد الإلكتروني لا يحتوي على أحرف CRLF.
     *
     * يستخدم تعبيراً منتظماً (regex) للبحث عن `\r` أو `\n` في النص.
     * إذا وُجدت، يتم استدعاء `$fail` مع رسالة الخطأ المترجمة.
     *
     * @param string $attribute اسم الحقل الذي يتم التحقق منه
     * @param mixed $value القيمة المدخلة (البريد الإلكتروني)
     * @param Closure $fail دالة يتم استدعاؤها عند فشل التحقق
     * @return void
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $email = (string) $value;

        if (preg_match('/[\r\n]/', $email)) {
            $fail(__('validation.email'))->translate();
        }
    }
}
