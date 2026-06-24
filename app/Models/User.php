<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

/**
 * ──────────────────────────────────────────────────────────────
 * 📌 النموذج: User (المستخدم)
 * ──────────────────────────────────────────────────────────────
 * 🗃️ الجدول: users
 * 
 * 🎯 الغرض:
 *    يمثل مستخدم المنصة بنظام أدوار كامل (super_admin, admin,
 *    editor, supporter, user). يدعم المصادقة عبر Laravel
 *    والصلاحيات عبر spatie/laravel-permission، ولوحة التحكم
 *    عبر Filament. يتتبّع حالة النشاط عبر الإنترنت وحالة
 *    الدردشة.
 * 
 * 📥 الحقول القابلة للتعبئة (fillable):
 *    - name, email, password ← بيانات الاعتماد الأساسية
 *    - is_admin, role ← صلاحيات قديمة (للتوافق مع النظام القديم)
 *    - is_active ← تعطيل/تفعيل الحساب
 *    - avatar ← الصورة الشخصية
 *    - preferred_locale ← اللغة المفضلة
 *    - phone ← رقم الهاتف
 *    - is_online ← متصل حالياً؟
 *    - last_seen_at ← آخر ظهور
 *    - can_chat ← صلاحية الدردشة
 * 
 * 🔄 الـ Casts:
 *    - email_verified_at → datetime
 *    - password → hashed
 *    - is_admin, is_active, is_online, can_chat → boolean
 *    - last_seen_at → datetime
 * 
 * 🔗 الـ Traits:
 *    - HasFactory ← مصانع الاختبار
 *    - HasRoles ← أدوار spatie/laravel-permission
 *    - Notifiable ← إشعارات Laravel
 * 
 * 📌 الواجهات المنفذة:
 *    - FilamentUser ← للوصول إلى لوحة تحكم Filament
 * 
 * 🛡️ دوال التحقق من الصلاحيات:
 *    - canAccessPanel ← التحقق من الوصول للوحة التحكم
 *    - isSuperAdmin ← مدير عام؟
 *    - isAdmin ← مدير؟
 *    - isEditor ← محرر؟
 *    - isSupporter ← داعم؟
 * ──────────────────────────────────────────────────────────────
 */
class User extends Authenticatable implements FilamentUser
{
    use HasFactory, HasRoles, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
        'role',
        'is_active',
        'avatar',
        'preferred_locale',
        'phone',
        'is_online',
        'last_seen_at',
        'can_chat',
    ];

    protected $attributes = [
        'role' => 'user',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'is_active' => 'boolean',
            'is_online' => 'boolean',
            'can_chat' => 'boolean',
            'last_seen_at' => 'datetime',
        ];
    }

    /**
     * ──────────────────────────────────────────────────────────────
     * 📌 الدالة: canAccessPanel
     * ──────────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    التحقق من أن المستخدم يمكنه الوصول إلى لوحة تحكم Filament.
     *    يشترط أن يكون الحساب نشطاً (is_active = true) وأن يكون
     *    إما مديراً (is_admin) أو لديه دور عبر spatie/permission.
     * 
     * 📥 المدخلات:
     *    - $panel: Panel ← لوحة Filament المطلوب الوصول إليها
     * 
     * 📤 المخرجات:
     *    - bool ← true إذا كان يمكن الوصول، false خلاف ذلك
     * ──────────────────────────────────────────────────────────────
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_active && ($this->is_admin || $this->roles()->exists());
    }

    /**
     * ──────────────────────────────────────────────────────────────
     * 📌 الدالة: isSuperAdmin
     * ──────────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    التحقق من أن المستخدم هو مدير عام (Super Admin).
     *    يعود true إذا كانت علامة is_admin مفعّلة أو إذا كان
     *    لديه دور 'super_admin' عبر spatie/permission.
     * 
     * 📤 المخرجات:
     *    - bool ← true إذا كان المستخدم مديراً عاماً
     * ──────────────────────────────────────────────────────────────
     */
    public function isSuperAdmin(): bool
    {
        return $this->is_admin || $this->hasRole('super_admin');
    }

    /**
     * ──────────────────────────────────────────────────────────────
     * 📌 الدالة: isAdmin
     * ──────────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    التحقق من أن المستخدم هو مدير. يشمل المدير العام (super_admin)
     *    ومن لديه دور 'admin'.
     * 
     * 📤 المخرجات:
     *    - bool ← true إذا كان المستخدم مديراً
     * ──────────────────────────────────────────────────────────────
     */
    public function isAdmin(): bool
    {
        return $this->isSuperAdmin() || $this->hasRole('admin');
    }

    /**
     * ──────────────────────────────────────────────────────────────
     * 📌 الدالة: isEditor
     * ──────────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    التحقق من أن المستخدم هو محرر. يشمل المدير والمحرر
     *    (admin أو editor).
     * 
     * 📤 المخرجات:
     *    - bool ← true إذا كان المستخدم محرراً
     * ──────────────────────────────────────────────────────────────
     */
    public function isEditor(): bool
    {
        return $this->isAdmin() || $this->hasRole('editor');
    }

    /**
     * ──────────────────────────────────────────────────────────────
     * 📌 الدالة: isSupporter
     * ──────────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    التحقق من أن المستخدم هو داعم. يشمل المدير والداعم
     *    (admin أو supporter).
     * 
     * 📤 المخرجات:
     *    - bool ← true إذا كان المستخدم داعماً
     * ──────────────────────────────────────────────────────────────
     */
    public function isSupporter(): bool
    {
        return $this->isAdmin() || $this->hasRole('supporter');
    }
}
