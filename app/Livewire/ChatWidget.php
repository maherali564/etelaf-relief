<?php

namespace App\Livewire;

use App\Events\ChatStatusChanged;
use App\Models\ChatMessage;
use App\Models\ChatSession;
use Livewire\Component;

/**
 * ──────────────────────────────────────────────────────────────
 * 📌 ChatWidget
 * ──────────────────────────────────────────────────────────────
 * 🎯 الغرض:
 *    مكون Livewire لواجهة الدردشة المباشرة (Live Chat)
 *    للزوار على الموقع. يدير دورة حياة الدردشة بالكامل:
 *    نموذج ما قبل الدردشة (prechat)، بدء الجلسة، إرسال
 *    الرسائل، عرض الرسائل، وإغلاق الجلسة. يتكامل مع أحداث
 *    البث الحي (Broadcast Events) لتحديث الواجهة فورياً.
 *
 * 📥 المدخلات (خصائص):
 *    - showChat: bool ← إظهار/إخفاء نافذة الدردشة
 *    - step: string ← المرحلة الحالية (prechat, chat)
 *    - visitorName, visitorEmail: string ← معلومات الزائر
 *    - sessionId, token: mixed ← بيانات جلسة الدردشة
 *    - message: string ← نص الرسالة المراد إرسالها
 *    - messages: array ← قائمة الرسائل المعروضة
 *    - isAdminTyping: bool ← مؤشر كتابة المشرف
 *    - activeSessions, waitingSessions: int ← إحصائيات
 *      للمشرفين
 *
 * 🔗 الاعتماديات:
 *    - ChatSession (Model) ← إدارة جلسات الدردشة
 *    - ChatMessage (Model) ← إدارة الرسائل
 *    - ChatStatusChanged (Event) ← الاستماع لتغييرات الحالة
 *    - Livewire\Component (Framework) ← المكون الأساسي
 * ──────────────────────────────────────────────────────────────
 */
class ChatWidget extends Component
{
    public $showChat = false;

    public $step = 'prechat';

    public $visitorName = '';

    public $visitorEmail = '';

    public $sessionId = null;

    public $token = null;

    public $message = '';

    public $messages = [];

    public $isAdminTyping = false;

    public $activeSessions = 0;

    public $waitingSessions = 0;

    protected $rules = [
        'visitorName' => 'required|string|max:255',
        'visitorEmail' => 'required|email|max:255',
        'message' => 'nullable|string|max:2000',
    ];

    /**
     * ──────────────────────────────────────────────────────────
     * 📌 getListeners
     * ──────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    تسجيل مستمعي أحداث البث الحي (Echo/Broadcast).
     *    تستمع لتغييرات حالة جلسات الدردشة (ChatStatusChanged)
     *    على القناة العامة للمشرفين (admin-chats) وتحدث
     *    الإحصائيات تلقائياً.
     *
     * 📤 المخرجات:
     *    - array ← خريطة أحداث البث مع دوال المعالجة
     * ──────────────────────────────────────────────────────────
     */
    protected function getListeners()
    {
        return [
            'echo:admin-chats,ChatStatusChanged' => 'refreshStats',
        ];
    }

    /**
     * ──────────────────────────────────────────────────────────
     * 📌 mount
     * ──────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    دالة بدء التشغيل. تتحقق من وجود جلسة دردشة مخزنة
     *    في جلسة المستخدم (Session). إذا وُجدت جلسة سابقة
     *    ولم تكن مغلقة، تستعيد حالة الدردشة تلقائياً وتنتقل
     *    إلى مرحلة المحادثة دون حاجة الزائر لإعادة التسجيل.
     *
     * 🔗 الاعتماديات:
     *    - session('chat_session') ← استرجاع بيانات الجلسة
     *    - ChatSession::find() ← التحقق من صحة الجلسة
     * ──────────────────────────────────────────────────────────
     */
    public function mount()
    {
        if ($stored = session('chat_session')) {
            $session = ChatSession::find($stored['session_id']);
            if ($session && $session->status !== 'closed') {
                $this->sessionId = $stored['session_id'];
                $this->token = $stored['token'];
                $this->visitorName = $stored['visitor_name'];
                $this->visitorEmail = $stored['visitor_email'];
                $this->step = 'chat';
                $this->loadMessages();
            }
        }
    }

    /**
     * ──────────────────────────────────────────────────────────
     * 📌 startChat
     * ──────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    بدء جلسة دردشة جديدة للزائر بعد التحقق من صحة
     *    اسمه وبريده الإلكتروني. تنشئ جلسة جديدة في قاعدة
     *    البيانات، تولد رمز مصادقة (Token)، ترسل رسالة
     *    ترحيبية، تبث حدث البدء للمشرفين، تخزن بيانات
     *    الجلسة في Session المستخدم، وتنتقل إلى مرحلة
     *    المحادثة.
     *
     * 🔗 الاعتماديات:
     *    - ChatSession::create() ← إنشاء جلسة جديدة
     *    - ChatMessage::save() ← حفظ الرسالة الترحيبية
     *    - ChatStatusChanged (Event) ← إشعار المشرفين
     *    - session() ← تخزين بيانات الجلسة
     *    - $this->dispatch() ← إشعار واجهة المستخدم
     * ──────────────────────────────────────────────────────────
     */
    public function startChat()
    {
        $this->validate([
            'visitorName' => 'required|string|max:255',
            'visitorEmail' => 'required|email|max:255',
        ]);

        $session = ChatSession::create([
            'visitor_name' => $this->visitorName,
            'visitor_email' => $this->visitorEmail,
            'visitor_ip' => request()->ip(),
            'visitor_url' => url()->current(),
            'status' => 'waiting',
        ]);

        $this->token = hash('sha256', $session->id.$this->visitorEmail.time());
        $this->sessionId = $session->id;

        $msg = new ChatMessage();
        $msg->fill([
            'visitor_token' => $this->token,
            'message' => 'مرحباً، أريد المساعدة',
            'is_from_admin' => false,
        ]);
        $msg->chat_session_id = $session->id;
        $msg->save();

        event(new ChatStatusChanged($session, 'waiting', null));

        $this->step = 'chat';

        session(['chat_session' => [
            'session_id' => $this->sessionId,
            'token' => $this->token,
            'visitor_name' => $this->visitorName,
            'visitor_email' => $this->visitorEmail,
        ]]);

        $this->dispatch('chat-started', sessionId: $this->sessionId);
        $this->loadMessages();
    }

    /**
     * ──────────────────────────────────────────────────────────
     * 📌 sendMessage
     * ──────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    إرسال رسالة نصية من الزائر في جلسة الدردشة الحالية.
     *    تتحقق من صحة نص الرسالة ومن وجود جلسة نشطة، ثم
     *    تخزّن الرسالة في قاعدة البيانات، تضيفها إلى قائمة
     *    الرسائل المعروضة في الواجهة، وتُفرغ حقل الإدخال.
     *
     * 🔗 الاعتماديات:
     *    - ChatMessage::save() ← حفظ الرسالة
     *    - $this->dispatch() ← إشعار واجهة المستخدم
     * ──────────────────────────────────────────────────────────
     */
    public function sendMessage()
    {
        $this->validateOnly('message');

        if (! $this->message || ! $this->sessionId) {
            return;
        }

        $msg = new ChatMessage();
        $msg->fill([
            'visitor_token' => $this->token,
            'message' => $this->message,
            'is_from_admin' => false,
        ]);
        $msg->chat_session_id = $this->sessionId;
        $msg->save();

        $this->messages[] = [
            'id' => $msg->id,
            'message' => $this->message,
            'is_from_admin' => false,
            'created_at' => $msg->created_at->toISOString(),
        ];
        $this->message = '';

        $this->dispatch('message-sent');
    }

    /**
     * ──────────────────────────────────────────────────────────
     * 📌 loadMessages
     * ──────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    تحميل جميع رسائل جلسة الدردشة الحالية من قاعدة
     *    البيانات وترتيبها زمنياً. تُحول الرسائل إلى مصفوفة
     *    منسقة للعرض في واجهة المستخدم (id, message,
     *    is_from_admin, created_at).
     *
     * 🔗 الاعتماديات:
     *    - ChatMessage::where('chat_session_id') ← استعلام
     *      لجلب رسائل الجلسة
     * ──────────────────────────────────────────────────────────
     */
    public function loadMessages()
    {
        if (! $this->sessionId) {
            return;
        }

        $this->messages = ChatMessage::where('chat_session_id', $this->sessionId)
            ->orderBy('created_at')
            ->get()
            ->map(fn ($m) => [
                'id' => $m->id,
                'message' => $m->message,
                'is_from_admin' => $m->is_from_admin,
                'created_at' => $m->created_at->toISOString(),
            ])
            ->toArray();
    }

    /**
     * ──────────────────────────────────────────────────────────
     * 📌 closeChat
     * ──────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    إغلاق جلسة الدردشة من طرف الزائر. تغلق الجلسة
     *    في قاعدة البيانات، تبث حدث الإغلاق للمشرفين،
     *    تعيد تعيين جميع خصائص المكون إلى قيمها الافتراضية،
     *    وتحذف بيانات الجلسة من Session المستخدم.
     *
     * 🔗 الاعتماديات:
     *    - ChatSession::close() ← إغلاق الجلسة
     *    - ChatStatusChanged (Event) ← إشعار المشرفين
     *    - session()->forget() ← مسح بيانات الجلسة
     * ──────────────────────────────────────────────────────────
     */
    public function closeChat()
    {
        if ($this->sessionId) {
            $session = ChatSession::find($this->sessionId);
            if ($session) {
                $session->close();
                event(new ChatStatusChanged($session, 'closed', null));
            }
        }

        $this->reset(['showChat', 'step', 'visitorName', 'visitorEmail', 'sessionId', 'token', 'messages', 'message']);
        session()->forget('chat_session');
    }

    /**
     * ──────────────────────────────────────────────────────────
     * 📌 refreshStats
     * ──────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    تحديث إحصائيات الجلسات النشطة والمنتظرة في واجهة
     *    المشرفين. تُستدعى تلقائياً عند استقبال حدث
     *    ChatStatusChanged عبر البث الحي (Echo).
     *
     * 🔗 الاعتماديات:
     *    - ChatSession::active() ← عدد الجلسات النشطة
     *    - ChatSession::waiting() ← عدد الجلسات المنتظرة
     * ──────────────────────────────────────────────────────────
     */
    public function refreshStats()
    {
        $this->activeSessions = ChatSession::active()->count();
        $this->waitingSessions = ChatSession::waiting()->count();
    }

    /**
     * ──────────────────────────────────────────────────────────
     * 📌 render
     * ──────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    عرض قالب Blade لمكون الدردشة.
     *
     * 📤 المخرجات:
     *    - View ← عرض livewire.chat-widget
     * ──────────────────────────────────────────────────────────
     */
    public function render()
    {
        return view('livewire.chat-widget');
    }
}
