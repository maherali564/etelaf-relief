<?php

namespace App\Exceptions;

use Exception;

/**
 * استثناء مخصص لأخطاء Webhook (Webhook Exception)
 *
 * يُستخدم هذا الاستثناء عند حدوث أي خطأ أثناء معالجة طلبات Webhook
 * الواردة من بوابات الدفع (Stripe, PayPal, Wise).
 *
 * الحالات التي يُرمى فيها هذا الاستثناء:
 * - توقيع Webhook غير صالح (فشل التحقق من التوقيع)
 * - طلب Webhook مكرر (تمت معالجته مسبقاً)
 * - نقص معرف الدفع (payment_intent_id) في طلب Webhook
 * - عدم تطابق المبلغ في Webhook مع المبلغ المخزن
 * - خطأ في تحليل بيانات JSON الواردة في الطلب
 * - أي خطأ آخر أثناء معالجة إشعار Webhook
 */
class WebhookException extends Exception {}
