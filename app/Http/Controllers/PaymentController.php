<?php

namespace App\Http\Controllers;

use App\Models\Donation;
use Illuminate\Http\Request;

/**
 * ──────────────────────────────────────────────────────────────
 * 📌 الكونترولر: PaymentController
 * ──────────────────────────────────────────────────────────────
 * 🎯 الغرض:
 *    عرض صفحات ما بعد الدفع — النجاح، الإلغاء، وتعليمات الدفع
 *    للمتبرعين. يتحقق من صحة رمز الوصول (idempotency_key) لكل
 *    صفحة قبل عرضها.
 * 
 * 📋 المسارات التي يعالجها:
 *    GET /{locale}/payment/{donation}/success      ← success()
 *    GET /{locale}/payment/{donation}/cancel        ← cancel()
 *    GET /{locale}/payment/{donation}/instructions  ← instructions()
 * 
 * 🔗 الاعتماديات:
 *    - Donation (Model) ← جلب بيانات التبرع
 *    - Illuminate\Http\Request ← معالجة الطلب
 * ──────────────────────────────────────────────────────────────
 */
class PaymentController extends Controller
{
    /**
     * ──────────────────────────────────────────────────────────────
     * 📌 الدالة: success
     * ──────────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    عرض صفحة نجاح الدفع بعد إتمام عملية التبرع بنجاح.
     * 
     * 📥 المدخلات:
     *    - $request: Request ← كائن الطلب
     *    - $locale: string ← رمز اللغة
     *    - $donation: Donation ← كائن التبرع (Route Model Binding)
     * 
     * 📤 المخرجات:
     *    - View ← عرض payment.success مع بيانات التبرع
     * 
     * 🔗 الاعتماديات:
     *    - verifyAccessToken() ← التحقق من صحة رمز الوصول
     * 
     * ⚠️ ملاحظات:
     *    - يتحقق من idempotency_key قبل عرض الصفحة
     * ──────────────────────────────────────────────────────────────
     */
    public function success(Request $request, string $locale, Donation $donation)
    {
        $this->verifyAccessToken($donation);

        return view('payment.success', compact('donation'));
    }

    /**
     * ──────────────────────────────────────────────────────────────
     * 📌 الدالة: cancel
     * ──────────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    عرض صفحة إلغاء الدفع عندما يلغي المستخدم عملية الدفع
     *    أو تفشل.
     * 
     * 📥 المدخلات:
     *    - $request: Request ← كائن الطلب
     *    - $locale: string ← رمز اللغة
     *    - $donation: Donation ← كائن التبرع (Route Model Binding)
     * 
     * 📤 المخرجات:
     *    - View ← عرض payment.cancel مع بيانات التبرع
     * 
     * 🔗 الاعتماديات:
     *    - verifyAccessToken() ← التحقق من صحة رمز الوصول
     * ──────────────────────────────────────────────────────────────
     */
    public function cancel(Request $request, string $locale, Donation $donation)
    {
        $this->verifyAccessToken($donation);

        return view('payment.cancel', compact('donation'));
    }

    /**
     * ──────────────────────────────────────────────────────────────
     * 📌 الدالة: instructions
     * ──────────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    عرض تعليمات الدفع للمتبرع (مثل تفاصيل التحويل البنكي
     *    أو تعليمات الدفع اليدوي).
     * 
     * 📥 المدخلات:
     *    - $request: Request ← كائن الطلب
     *    - $locale: string ← رمز اللغة
     *    - $donation: Donation ← كائن التبرع (Route Model Binding)
     * 
     * 📤 المخرجات:
     *    - View ← عرض payment.instructions مع بيانات التبرع、
     *      إعدادات البوابة、التعليمات、ومعلومات طريقة الدفع
     * 
     * 🔗 الاعتماديات:
     *    - verifyAccessToken() ← التحقق من صحة رمز الوصول
     *    - Donation->paymentMethod->gateway ← جلب إعدادات البوابة
     * 
     * ⚠️ ملاحظات:
     *    - يستخرج config و instructions و driver من علاقات
     *      PaymentMethod و PaymentGateway
     * ──────────────────────────────────────────────────────────────
     */
    public function instructions(Request $request, string $locale, Donation $donation)
    {
        $this->verifyAccessToken($donation);

        $paymentMethod = $donation->paymentMethod;
        $gateway = $paymentMethod?->gateway;
        $config = $gateway?->config ?? [];

        $instructions = $paymentMethod?->instructions ?? '';
        $driver = $gateway?->driver ?? '';

        return view('payment.instructions', compact('donation', 'config', 'instructions', 'paymentMethod', 'driver'));
    }
}
