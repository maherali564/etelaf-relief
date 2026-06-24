<?php

namespace App\Exceptions;

use Exception;

/**
 * استثناء مخصص لأخطاء الدفع (Payment Exception)
 *
 * يُستخدم هذا الاستثناء عند حدوث أي خطأ أثناء معالجة عمليات الدفع
 * عبر بوابات الدفع المختلفة (Stripe, PayPal, Wise).
 *
 * الحالات التي يُرمى فيها هذا الاستثناء:
 * - فشل إنشاء نية الدفع (Payment Intent) في Stripe
 * - فشل التحقق من توقيع Webhook
 * - عدم تطابق المبلغ بين Webhook والتبرع المخزن
 * - فشل طلب الدفع إلى بوابة PayPal
 - فشل معاملة Wise
 * - أي خطأ آخر متعلق بمعالجة الدفع المالي
 */
class PaymentException extends Exception {}
