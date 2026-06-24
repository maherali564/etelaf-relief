<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * وسيط (Middleware) لإضافة ترويسات الأمان (Security Headers) إلى كل استجابة.
 *
 * يعمل هذا الوسيط على جميع المسارات ويضيف ترويسات HTTP ضرورية
 * للحماية من هجمات الويب الشائعة:
 *
 * - **X-Frame-Options: DENY** — حماية من Clickjacking (منع عرض الموقع في iframe)
 * - **X-Content-Type-Options: nosniff** — منع المتصفح من تخمين نوع المحتوى
 * - **Referrer-Policy** — التحكم في معلومات المُحيل
 * - **Permissions-Policy** — تقييد صلاحيات المتصفح (GPS، كاميرا، ميكروفون)
 * - **Content-Security-Policy (CSP)** — تقييد المصادر المسموح بها للـ scripts،
 *   styles، fonts، images، connections، frames، forms
 * - **Strict-Transport-Security (HSTS)** — فرض HTTPS لمدة سنة
 * - إزالة ترويسة `X-Powered-By` لإخفاء معلومات الخادم
 *
 * @package App\Http\Middleware
 */
class SecurityHeaders
{
    /**
     * معالجة الطلب وإضافة ترويسات الأمان إلى الاستجابة.
     *
     * يتم تنفيذ ذلك بعد معالجة الطلب بالكامل (بعد `$next($request)`)
     * لإضافة الترويسات إلى الاستجابة النهائية قبل إرسالها للمتصفح.
     *
     * @param Request $request الطلب الوارد
     * @param Closure $next الدالة التالية في سلسلة الوسائط
     * @return Response الاستجابة مع ترويسات الأمان المضافة
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=(), payment=()');

        $csp = "default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://www.googletagmanager.com https://www.google-analytics.com; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.googleapis.com; img-src 'self' data: https:; font-src 'self' https://fonts.gstatic.com https://cdn.jsdelivr.net; connect-src 'self' https://api.stripe.com https://api-m.paypal.com https://api-m.sandbox.paypal.com https://api.sandbox.transferwise.tech https://api.wise.com https://www.google-analytics.com; frame-src 'self' https://www.youtube.com https://player.vimeo.com; form-action 'self';";
        $response->headers->set('Content-Security-Policy', $csp);

        $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');

        $response->headers->remove('X-Powered-By');

        return $response;
    }
}
