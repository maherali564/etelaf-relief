<?php

namespace App\Services\Payment;

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * ──────────────────────────────────────────────────────────────
 * 📌 IdempotencyHelper
 * ──────────────────────────────────────────────────────────────
 * 🎯 الغرض:
 *    أداة مساعدة لمنع معالجة طلبات الدفع المكررة (Idempotency).
 *    تضمن عدم تنفيذ نفس طلب الدفع أكثر من مرة باستخدام
 *    مفتاح تفرد (Idempotency Key) يتم تسجيله في جدول
 *    مخصص بقاعدة البيانات. في حال تكرار المفتاح، يتم
 *    رفض الطلب المكرر تلقائياً.
 *
 * 📥 المدخلات:
 *    - تعتمد على جدول idempotency_keys في قاعدة البيانات
 *      (يجب إنشاؤه في migration منفصل)
 *
 * 📤 المخرجات:
 *    - دوال static يمكن استدعاؤها مباشرة دون إنشاء كائن
 *
 * 🔗 الاعتماديات:
 *    - DB (Facade) ← إدراج المفتاح في قاعدة البيانات
 *    - Str::random() ← توليد مفاتيح عشوائية آمنة
 *    - Log (Facade) ← تسجيل حالات التكرار
 * ──────────────────────────────────────────────────────────────
 */
class IdempotencyHelper
{
    /**
     * ──────────────────────────────────────────────────────────
     * 📌 generateKey
     * ──────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    توليد مفتاح تفرد (Idempotency Key) عشوائي جديد
     *    مكون من 32 حرفاً مع بادئة اختيارية. يُستخدم هذا
     *    المفتاح لضمان عدم تكرار معالجة طلب الدفع.
     *
     * 📥 المدخلات:
     *    - $prefix: string ← بادئة اختيارية للمفتاح (مثل
     *      'donation' أو 'payment')
     *
     * 📤 المخرجات:
     *    - string ← مفتاح تفرد بالصيغة: {prefix}_{32 حرف عشوائي}
     *
     * 🔗 الاعتماديات:
     *    - Str::random(32) ← توليد سلسلة عشوائية آمنة
     * ──────────────────────────────────────────────────────────
     */
    public static function generateKey(string $prefix = ''): string
    {
        return $prefix.'_'.Str::random(32);
    }

    /**
     * ──────────────────────────────────────────────────────────
     * 📌 checkAndMark
     * ──────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    التحقق مما إذا كان مفتاح التفرد مستخدماً بالفعل
     *    (أي تمت معالجة هذا الطلب سابقاً). يحاول إدراج
     *    المفتاح في قاعدة البيانات؛ إذا نجح الإدراج فهذا
     *    يعني أن الطلب جديد ويعيد false. إذا فشل الإدراج
     *    بسبب انتهاك قيد uniqueness فهذا يعني أن الطلب
     *    مكرر ويعيد true.
     *
     * 📥 المدخلات:
     *    - $idempotencyKey: string ← مفتاح التفرد المراد
     *      التحقق منه
     *
     * 📤 المخرجات:
     *    - bool ← false إذا كان المفتاح جديداً (لم يُستخدم
     *      من قبل)، true إذا كان مكرراً
     *
     * ❌ الاستثناءات:
     *    - QueryException أو PDOException (يتم التقاطها
     *      داخلياً) ← عند فشل الإدراج بسبب تكرار المفتاح
     *
     * 🔗 الاعتماديات:
     *    - DB::table('idempotency_keys')->insert() ← إدراج
     *      المفتاح في جدول idempotency_keys
     *    - Log::info() ← تسجيل حالة التكرار
     * ──────────────────────────────────────────────────────────
     */
    public static function checkAndMark(string $idempotencyKey): bool
    {
        if (empty($idempotencyKey)) {
            return false;
        }

        if (DB::table('idempotency_keys')->where('key', $idempotencyKey)->exists()) {
            Log::info('IdempotencyHelper: duplicate detected', [
                'idempotency_key' => $idempotencyKey,
            ]);
            return true;
        }

        try {
            DB::table('idempotency_keys')->insert(['key' => $idempotencyKey]);
        } catch (QueryException) {
            Log::info('IdempotencyHelper: duplicate detected (race)', [
                'idempotency_key' => $idempotencyKey,
            ]);
            return true;
        }

        return false;
    }
}
