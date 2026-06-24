<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * وسيط (Middleware) لتقييد الوصول إلى واجهة الدردشة.
 *
 * يتحقق من أن المستخدم مُسجّل الدخول (Authenticated) وأن لديه
 * صلاحية الوصول إلى الدردشة (`can_chat`). يُستخدم هذا الوسيط
 * لحماية مسارات الدردشة بحيث لا يتمكن إلا المشرفون المصرح لهم
 * من الوصول إلى المحادثات المباشرة مع زوار الموقع.
 *
 * شروط الوصول:
 * - يجب أن يكون المستخدم مسجلاً للدخول إلى لوحة التحكم
 * - يجب أن يكون لديه صلاحية `can_chat` أو أن يكون مشرفاً عاماً (Super Admin)
 *
 * @package App\Http\Middleware
 */
class ChatAccess
{
    /**
     * معالجة الطلب والتحقق من صلاحية الوصول إلى الدردشة.
     *
     * إذا لم يكن المستخدم مسجلاً للدخول، يتم إعادة توجيهه إلى صفحة
     * تسجيل الدخول. إذا لم يكن لديه صلاحية الدردشة، يتم إرجاع خطأ 403.
     *
     * @param Request $request الطلب الوارد
     * @param Closure $next الدالة التالية في سلسلة الوسائط
     * @return Response الاستجابة (صفحة الدردشة، تسجيل الدخول، أو خطأ 403)
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return redirect()->route('filament.admin.auth.login');
        }

        $user = Auth::user();
        if (! $user->can_chat && ! $user->isSuperAdmin()) {
            abort(403, 'Unauthorized. You do not have chat access.');
        }

        return $next($request);
    }
}
