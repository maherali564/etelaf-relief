<?php

use App\Exceptions\PaymentException;
use Illuminate\Validation\ValidationException;
use App\Http\Middleware\ChatAccess;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\SetLocale;
use App\Http\Middleware\ThrottleAdminLogin;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

/**
 * ملف تهيئة تطبيق Laravel الأساسي (Bootstrap App)
 *
 * يقوم هذا الملف بإنشاء كائن Application وتكوين:
 * - المسارات (Routes): web، console، channels، health check
 * - الوسائط (Middleware) العامة والمستعارة
 * - معالجة الاستثناءات (Exception Handling)
 *
 * @return \Illuminate\Foundation\Application
 */
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        /**
         * إضافة وسائط (Middleware) إلى مجموعة web.
         *
         * الوسائط المضافة:
         * - SecurityHeaders: إضافة رؤوس أمان للاستجابة (HSTS, X-Frame-Options, إلخ)
         * - SetLocale: ضبط لغة التطبيق بناءً على مقطع URL الأول
         * - ThrottleAdminLogin: تحديد معدل محاولات دخول لوحة التحكم
         */
        $middleware->web(append: [
            SecurityHeaders::class,
            SetLocale::class,
            ThrottleAdminLogin::class,
        ]);

        /**
         * تعريف أسماء مستعارة للوسائط (Middleware Aliases) لاستخدامها في المسارات.
         *
         * الوسائط المعرفة:
         * - chat-access: التحقق من صلاحية الوصول إلى الدردشة الحية
         */
        $middleware->alias([
            'chat-access' => ChatAccess::class,
        ]);

        /**
         * تكوين الثقة في البروكسيات.
         *
         * يتم الثقة في جميع البروكسيات (*) للتعامل مع خوادم الوكالة
         * مثل Cloudflare، AWS ELB، أو Nginx العكسي.
         */
        $middleware->trustProxies(at: '*');

        /**
         * استثناء مسارات محددة من التحقق من CSRF Token.
         *
         * يتم استثناء جميع مسارات Webhook (webhook/*)
         * لأن بوابات الدفع ترسل طلبات POST بدون CSRF token.
         */
        $middleware->validateCsrfTokens(except: [
            'webhook/*',
        ]);

        /**
         * تحديد مسار إعادة التوجيه للزوار غير المسجلين.
         *
         * إذا كان الطلب موجهاً لمنطقة المتبرع (donor/*)،
         * يتم إعادة التوجيه إلى صفحة دخول المتبرع.
         * وإلا، يتم إعادة التوجيه إلى صفحة دخول لوحة الإدارة (Filament).
         */
        $middleware->redirectGuestsTo(fn (Request $request) => $request->is('*/donor/*') || $request->is('donor/*')
            ? route('donor.login', ['locale' => $request->segment(1) ?? app()->getLocale()])
            : route('filament.admin.auth.login'));
    })
    ->withExceptions(function (Exceptions $exceptions) {
        /**
         * تسجيل استثناءات الدفع (PaymentException).
         * يمكن تخصيص سلوك التسجيل أو الإبلاغ هنا.
         */
        $exceptions->reportable(function (PaymentException $e) {
            //
        });

        /**
         * تنسيق استجابة أخطاء التحقق (ValidationException).
         *
         * إذا كان الطلب يتوقع JSON (API) أو كان موجهاً لـ Webhook،
         * يتم إرجاع استجابة JSON تحتوي على رسائل الخطأ.
         */
        $exceptions->renderable(function (ValidationException $e, Request $request) {
            if ($request->expectsJson() || $request->is('webhook/*')) {
                return response()->json([
                    'error' => 'validation_failed',
                    'messages' => $e->errors(),
                ], 422);
            }
        });

        /**
         * تنسيق استجابة أخطاء التوثيق (AuthenticationException).
         *
         * إذا كان الطلب يتوقع JSON (API) أو كان موجهاً لـ Webhook،
         * يتم إرجاع استجابة JSON تفيد بأن المستخدم غير موثّق.
         */
        $exceptions->renderable(function (AuthenticationException $e, Request $request) {
            if ($request->expectsJson() || $request->is('webhook/*')) {
                return response()->json(['error' => 'unauthenticated'], 401);
            }
        });
    })->create();
