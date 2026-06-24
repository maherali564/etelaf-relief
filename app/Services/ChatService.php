<?php

namespace App\Services;

use App\Events\ChatStatusChanged;
use App\Events\NewChatMessage;
use App\Events\UserTyping;
use App\Models\ChatMessage;
use App\Models\ChatSession;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * ──────────────────────────────────────────────────────────────
 * 📌 ChatService
 * ──────────────────────────────────────────────────────────────
 * 🎯 الغرض:
 *    خدمة متكاملة لإدارة الدردشة المباشرة (Live Chat) بين الزوار
 *    والمشرفين على المنصة. تتولى إنشاء جلسات الدردشة، إرسال
 *    الرسائل، إغلاق الجلسات، تعيين المشرفين، وإدارة الإشعارات
 *    والتخزين المؤقت للجلسات والرسائل غير المقروءة.
 *
 * 🔗 الاعتماديات:
 *    - ChatSession (Model) ← إدارة جلسات الدردشة
 *    - ChatMessage (Model) ← تخزين واسترجاع الرسائل
 *    - User (Model) ← المستخدمون (المشرفون)
 *    - ChatStatusChanged (Event) ← إشعار بتغيير حالة الجلسة
 *    - NewChatMessage (Event) ← إشعار برسالة جديدة
 *    - UserTyping (Event) ← إشعار بحالة الكتابة
 *    - Cache (Facade) ← تخزين مؤقت للجلسات والرسائل غير المقروءة
 * ──────────────────────────────────────────────────────────────
 */
class ChatService
{
    /**
     * ──────────────────────────────────────────────────────────
     * 📌 startSession
     * ──────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    بدء جلسة دردشة جديدة لزائر الموقع. تنشئ سجل الجلسة
     *    في قاعدة البيانات، تنشئ رسالة ترحيبية، وتبث حدث بدء
     *    الجلسة للمشرفين. تمسح ذاكرة التخزين المؤقت للجلسات
     *    والمشرفين لتعكس الجلسة الجديدة.
     *
     * 📥 المدخلات:
     *    - $visitorName: string ← اسم الزائر
     *    - $visitorEmail: string ← البريد الإلكتروني للزائر
     *    - $visitorIp: string ← عنوان IP للزائر
     *    - $visitorUrl: string|null ← رابط الصفحة التي بدأت
     *      منها الدردشة (اختياري)
     *
     * 📤 المخرجات:
     *    - array ← يحتوي على 'session_id' (int) و 'token' (string)
     *      المستخدمين لمصادقة الزائر في الطلبات اللاحقة
     *
     * 🔗 الاعتماديات:
     *    - ChatSession::create() ← إنشاء جلسة جديدة
     *    - ChatMessage::save() ← حفظ الرسالة الترحيبية
     *    - ChatStatusChanged (Event) ← إشعار المشرفين
     *    - Cache::forget() ← مسح الكاش للمشرفين
     * ──────────────────────────────────────────────────────────
     */
    public function startSession(string $visitorName, string $visitorEmail, string $visitorIp, ?string $visitorUrl = null): array
    {
        $session = ChatSession::create([
            'visitor_name' => $visitorName,
            'visitor_email' => $visitorEmail,
            'visitor_ip' => $visitorIp,
            'visitor_url' => $visitorUrl ?? url()->previous(),
            'status' => 'waiting',
        ]);

        $token = Str::random(64);

        $msg = new ChatMessage();
        $msg->fill([
            'visitor_token' => $token,
            'message' => 'مرحباً، أريد المساعدة',
            'is_from_admin' => false,
        ]);
        $msg->chat_session_id = $session->id;
        $msg->save();

        event(new ChatStatusChanged($session, 'waiting', null));

        $adminUsers = User::where('can_chat', true)->orWhereHas('roles', fn ($q) => $q->where('name', 'super_admin'))->get();
        foreach ($adminUsers as $admin) {
            Cache::forget('chat.sessions.'.$admin->id);
            Cache::forget('chat.unread.'.$admin->id);
        }

        return ['session_id' => $session->id, 'token' => $token];
    }

    /**
     * ──────────────────────────────────────────────────────────
     * 📌 sendMessage
     * ──────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    إرسال رسالة جديدة من الزائر في جلسة دردشة موجودة.
     *    تخزّن الرسالة في قاعدة البيانات، وتبثها عبر البث الحي
     *    (Broadcast) للمشرفين المخصصين للجلسة، وتمسح الكاش
     *    لتحديث عدد الرسائل غير المقروءة.
     *
     * 📥 المدخلات:
     *    - $sessionId: int ← معرف جلسة الدردشة
     *    - $token: string ← رمز مصادقة الزائر
     *    - $message: string ← نص الرسالة
     *
     * 📤 المخرجات:
     *    - ChatMessage ← كائن الرسالة المُنشأة
     *
     * ❌ الاستثناءات:
     *    - ModelNotFoundException ← إذا لم توجد الجلسة
     *
     * 🔗 الاعتماديات:
     *    - ChatSession::findOrFail() ← التحقق من وجود الجلسة
     *    - ChatMessage::save() ← حفظ الرسالة
     *    - NewChatMessage (Event) ← إشعار المشرف المختص
     *    - Cache::forget() ← تحديث الكاش
     * ──────────────────────────────────────────────────────────
     */
    public function sendMessage(int $sessionId, string $token, string $message): ChatMessage
    {
        $session = ChatSession::findOrFail($sessionId);

        $msg = new ChatMessage();
        $msg->fill([
            'visitor_token' => $token,
            'message' => $message,
            'is_from_admin' => false,
        ]);
        $msg->chat_session_id = $session->id;
        $msg->save();

        broadcast(new NewChatMessage($msg, $session->id, false))->toOthers();

        if ($session->assigned_to) {
            Cache::forget('chat.sessions.'.$session->assigned_to);
            Cache::forget('chat.unread.'.$session->assigned_to);
        }

        return $msg;
    }

    /**
     * ──────────────────────────────────────────────────────────
     * 📌 closeSession
     * ──────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    إغلاق جلسة الدردشة من طرف الزائر. تستدعي دالة
     *    close() على نموذج الجلسة وتبث حدث تغيير الحالة
     *    للمشرفين.
     *
     * 📥 المدخلات:
     *    - $sessionId: int ← معرف جلسة الدردشة
     *    - $user: User|null ← المستخدم الذي أغلق الجلسة
     *      (اختياري، عادة ما يكون null للزائر)
     *
     * ❌ الاستثناءات:
     *    - ModelNotFoundException ← إذا لم توجد الجلسة
     *
     * 🔗 الاعتماديات:
     *    - ChatSession::close() ← إغلاق الجلسة
     *    - ChatStatusChanged (Event) ← إشعار المشرفين
     * ──────────────────────────────────────────────────────────
     */
    public function closeSession(int $sessionId, ?User $user = null): void
    {
        $session = ChatSession::findOrFail($sessionId);
        $session->close();

        event(new ChatStatusChanged($session, 'closed', $user?->id));
    }

    /**
     * ──────────────────────────────────────────────────────────
     * 📌 broadcastTyping
     * ──────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    بث حالة الكتابة (الزائر يكتب / المشرف يكتب) إلى
     *    الطرف الآخر في جلسة الدردشة عبر الأحداث الحيَة
     *    (Broadcast Events).
     *
     * 📥 المدخلات:
     *    - $sessionId: int ← معرف جلسة الدردشة
     *    - $isTyping: bool ← هل المستخدم يكتب حالياً
     *    - $isAdmin: bool ← هل الكاتب هو مشرف (true) أم زائر (false)
     *
     * 🔗 الاعتماديات:
     *    - UserTyping (Event) ← بث حالة الكتابة
     * ──────────────────────────────────────────────────────────
     */
    public function broadcastTyping(int $sessionId, bool $isTyping, bool $isAdmin = false): void
    {
        broadcast(new UserTyping($sessionId, $isAdmin))->toOthers();
    }

    /**
     * ──────────────────────────────────────────────────────────
     * 📌 assignSession
     * ──────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    تعيين جلسة دردشة لمشرف معين. يحدث حالة الجلسة
     *    إلى نشطة (active)، ويسجل رسالة نظام ترحيبية تفيد
     *    بانضمام المشرف، ويبث حدث تغيير الحالة.
     *
     * 📥 المدخلات:
     *    - $sessionId: int ← معرف جلسة الدردشة
     *    - $user: User ← كائن المشرف الذي سيتم تعيينه
     *
     * 📤 المخرجات:
     *    - ChatSession ← كائن الجلسة بعد التحديث
     *
     * ❌ الاستثناءات:
     *    - ModelNotFoundException ← إذا لم توجد الجلسة
     *
     * 🔗 الاعتماديات:
     *    - ChatSession::assignTo() ← تعيين المشرف
     *    - ChatStatusChanged (Event) ← إشعار بتغيير الحالة
     * ──────────────────────────────────────────────────────────
     */
    public function assignSession(int $sessionId, User $user): ChatSession
    {
        $session = ChatSession::findOrFail($sessionId);
        $session->assignTo($user);

        event(new ChatStatusChanged($session, 'active', $user->id));

        $msg = new ChatMessage();
        $msg->fill([
            'message' => 'المشرف '.$user->name.' انضم إلى المحادثة',
            'is_from_admin' => true,
        ]);
        $msg->chat_session_id = $session->id;
        $msg->user_id = $user->id;
        $msg->save();

        return $session;
    }

    /**
     * ──────────────────────────────────────────────────────────
     * 📌 sendAdminMessage
     * ──────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    إرسال رسالة من المشرف إلى الزائر في جلسة دردشة
     *    معينة. تخزّن الرسالة، تبثها للزائر عبر البث الحي،
     *    وتمسح الكاش الخاص بالمشرف لتحديث الواجهة.
     *
     * 📥 المدخلات:
     *    - $sessionId: int ← معرف جلسة الدردشة
     *    - $user: User ← كائن المشرف المرسل
     *    - $message: string ← نص الرسالة
     *
     * 📤 المخرجات:
     *    - ChatMessage ← كائن الرسالة المُنشأة
     *
     * ❌ الاستثناءات:
     *    - ModelNotFoundException ← إذا لم توجد الجلسة
     *
     * 🔗 الاعتماديات:
     *    - NewChatMessage (Event) ← إشعار الزائر
     *    - ChatMessage::save() ← حفظ الرسالة
     *    - Cache::forget() ← تحديث الكاش
     * ──────────────────────────────────────────────────────────
     */
    public function sendAdminMessage(int $sessionId, User $user, string $message): ChatMessage
    {
        $session = ChatSession::findOrFail($sessionId);

        $msg = new ChatMessage();
        $msg->fill([
            'message' => $message,
            'is_from_admin' => true,
        ]);
        $msg->chat_session_id = $session->id;
        $msg->user_id = $user->id;
        $msg->save();

        broadcast(new NewChatMessage($msg, $session->id, true))->toOthers();

        Cache::forget('chat.sessions.'.$user->id);
        Cache::forget('chat.unread.'.$user->id);

        return $msg;
    }

    /**
     * ──────────────────────────────────────────────────────────
     * 📌 closeAdminSession
     * ──────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    إغلاق جلسة الدردشة من طرف المشرف. يغلق الجلسة،
     *    ويسجل رسالة نظام تفيد بانتهاء المحادثة، ويبث حدث
     *    تغيير الحالة للزائر.
     *
     * 📥 المدخلات:
     *    - $sessionId: int ← معرف جلسة الدردشة
     *    - $user: User ← كائن المشرف الذي أغلق الجلسة
     *
     * ❌ الاستثناءات:
     *    - ModelNotFoundException ← إذا لم توجد الجلسة
     *
     * 🔗 الاعتماديات:
     *    - ChatSession::close() ← إغلاق الجلسة
     *    - ChatStatusChanged (Event) ← إشعار الزائر
     * ──────────────────────────────────────────────────────────
     */
    public function closeAdminSession(int $sessionId, User $user): void
    {
        $session = ChatSession::findOrFail($sessionId);
        $session->close();

        $msg = new ChatMessage();
        $msg->fill([
            'message' => 'تم إنهاء المحادثة',
            'is_from_admin' => true,
        ]);
        $msg->chat_session_id = $session->id;
        $msg->user_id = $user->id;
        $msg->save();

        event(new ChatStatusChanged($session, 'closed', $user->id));
    }

    /**
     * ──────────────────────────────────────────────────────────
     * 📌 getSessions
     * ──────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    استرجاع جميع جلسات الدردشة النشطة والمغلقة اليوم
     *    للمشرف، مع عدد الرسائل غير المقروءة. تستخدم التخزين
     *    المؤقت (Cache) لمدة 30 ثانية لتحسين الأداء.
     *
     * 📥 المدخلات:
     *    - $user: User ← كائن المشرف المستعلم
     *
     * 📤 المخرجات:
     *    - array ← يحتوي على:
     *        'sessions' (Collection) ← قائمة الجلسات
     *        'unread_count' (int) ← عدد الرسائل غير المقروءة
     *
     * 🔗 الاعتماديات:
     *    - Cache::remember() ← تخزين مؤقت بزمن 30 ثانية
     *    - ChatSession مع assignedAgent (Relationship) ← تحميل
     *      العلاقات مسبقاً لتجنب N+1
     * ──────────────────────────────────────────────────────────
     */
    public function getSessions(User $user): array
    {
        $cacheKey = 'chat.sessions.'.$user->id;

        return Cache::remember($cacheKey, 30, function () use ($user) {
            $sessions = ChatSession::with('assignedAgent')
                ->whereIn('status', ['waiting', 'active'])
                ->orWhere(function ($q) {
                    $q->where('status', 'closed')
                        ->whereDate('updated_at', today());
                })
                ->orderByRaw("FIELD(status, 'waiting', 'active', 'closed')")
                ->orderBy('updated_at', 'desc')
                ->get();

            $unreadCount = ChatMessage::whereHas('session', function ($q) use ($user) {
                $q->where('assigned_to', $user->id)->where('status', 'active');
            })
                ->where('is_read', false)
                ->where('is_from_admin', false)
                ->count();

            return ['sessions' => $sessions, 'unread_count' => $unreadCount];
        });
    }

    /**
     * ──────────────────────────────────────────────────────────
     * 📌 markMessagesRead
     * ──────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    تعليم جميع رسائل الزائر في جلسة معينة كمقروءة
     *    بعد أن يطلع عليها المشرف. تحدث حقل is_read و read_at
     *    لكل الرسائل غير المقروءة من الزائر.
     *
     * 📥 المدخلات:
     *    - $sessionId: int ← معرف جلسة الدردشة
     *
     * ❌ الاستثناءات:
     *    - ModelNotFoundException ← إذا لم توجد الجلسة
     *
     * 🔗 الاعتماديات:
     *    - ChatMessage::update() ← تحديث شامل للرسائل
     * ──────────────────────────────────────────────────────────
     */
    public function markMessagesRead(int $sessionId): void
    {
        $session = ChatSession::with('messages.user')->findOrFail($sessionId);

        $session->messages()
            ->where('is_from_admin', false)
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);
    }

    /**
     * ──────────────────────────────────────────────────────────
     * 📌 getSessionMessages
     * ──────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    استرجاع رسائل جلسة دردشة للمشرف مع أسماء المستخدمين
     *    (المشرفين). تعيد آخر 500 رسالة مرتبة زمنياً مع
     *    تحويلها إلى مصفوفة منسقة للعرض.
     *
     * 📥 المدخلات:
     *    - $sessionId: int ← معرف جلسة الدردشة
     *
     * 📤 المخرجات:
     *    - Collection ← مصفوفة من الرسائل، كل رسالة تحتوي:
     *        id, message, is_from_admin, user_name, created_at
     *
     * ❌ الاستثناءات:
     *    - ModelNotFoundException ← إذا لم توجد الجلسة
     *
     * 🔗 الاعتماديات:
     *    - ChatSession مع messages.user (Relationship)
     * ──────────────────────────────────────────────────────────
     */
    public function getSessionMessages(int $sessionId)
    {
        $session = ChatSession::with('messages.user')->findOrFail($sessionId);

        return $session->messages()->orderBy('created_at')->limit(500)->get()->map(function ($m) {
            return [
                'id' => $m->id,
                'message' => $m->message,
                'is_from_admin' => $m->is_from_admin,
                'user_name' => $m->user?->name,
                'created_at' => $m->created_at->toISOString(),
            ];
        });
    }

    /**
     * ──────────────────────────────────────────────────────────
     * 📌 getVisitorMessages
     * ──────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    استرجاع رسائل جلسة دردشة للزائر (بدون أسماء
     *    المستخدمين). تعيد آخر 500 رسالة مرتبة زمنياً
     *    مع تحويلها إلى مصفوفة منسقة للعرض في واجهة
     *    الزائر.
     *
     * 📥 المدخلات:
     *    - $sessionId: int ← معرف جلسة الدردشة
     *
     * 📤 المخرجات:
     *    - Collection ← مصفوفة من الرسائل، كل رسالة تحتوي:
     *        id, message, is_from_admin, created_at
     *
     * ❌ الاستثناءات:
     *    - ModelNotFoundException ← إذا لم توجد الجلسة
     *
     * 🔗 الاعتماديات:
     *    - ChatSession::messages() (Relationship)
     * ──────────────────────────────────────────────────────────
     */
    public function getVisitorMessages(int $sessionId)
    {
        $session = ChatSession::findOrFail($sessionId);

        return $session->messages()->orderBy('created_at')->limit(500)->get()->map(function ($m) {
            return [
                'id' => $m->id,
                'message' => $m->message,
                'is_from_admin' => $m->is_from_admin,
                'created_at' => $m->created_at->toISOString(),
            ];
        });
    }

    /**
     * ──────────────────────────────────────────────────────────
     * 📌 getUnreadCount
     * ──────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    استرجاع عدد الرسائل غير المقروءة من الزوار لمشرف
     *    معين. تستخدم التخزين المؤقت (Cache) بزمن 30 ثانية
     *    لتقليل استعلامات قاعدة البيانات.
     *
     * 📥 المدخلات:
     *    - $user: User ← كائن المشرف
     *
     * 📤 المخرجات:
     *    - int ← عدد الرسائل غير المقروءة
     *
     * 🔗 الاعتماديات:
     *    - Cache::remember() ← تخزين مؤقت
     *    - ChatMessage مع session (Relationship) ← استعلام
     *      عبر العلاقة
     * ──────────────────────────────────────────────────────────
     */
    public function getUnreadCount(User $user): int
    {
        return Cache::remember('chat.unread.'.$user->id, 30, function () use ($user) {
            return ChatMessage::whereHas('session', function ($q) use ($user) {
                $q->where('assigned_to', $user->id)->where('status', 'active');
            })
                ->where('is_read', false)
                ->where('is_from_admin', false)
                ->count();
        });
    }
}
