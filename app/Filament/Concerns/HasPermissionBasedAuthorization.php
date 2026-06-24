<?php

namespace App\Filament\Concerns;

use Illuminate\Support\Str;

/**
 * ──────────────────────────────────────────────────────────────
 * 📌 التريت: HasPermissionBasedAuthorization
 * ──────────────────────────────────────────────────────────────
 * 🎯 الغرض:
 *    توفير طبقة صلاحيات ديناميكية لموارد Filament.
 *    يشتق اسم الإذن تلقائياً من اسم الكلاس (Resource)
 *    باستخدام الصيغة: {action}_{singular_snake_case}.
 *    مثال: DonationResource ← view_any_donation, create_donation.
 *
 * 📋 المدخلات:
 *    تستخدم static::class لاشتقاق اسم الإذن ديناميكياً.
 *
 * 📤 المخرجات:
 *    تعيد true/false بناءً على قدرة المستخدم على الإذن.
 * ──────────────────────────────────────────────────────────────
 */
trait HasPermissionBasedAuthorization
{
    /**
     * ──────────────────────────────────────────────────────────────
     * 📌 الدالة: getPermissionSlug
     * ──────────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    اشتقاق اسم الإذن من اسم الكلاس (Resource).
     *    يحوّل PascalCase إلى snake_case ويزيل لاحقة 'Resource'.
     *    مثال: DonationResource → 'donation'.
     *
     * 📥 المدخلات:
     *    - لا توجد (تستخدم static::class)
     *
     * 📤 المخرجات:
     *    - string ← اسم الإذن بصيغة snake_case
     * ──────────────────────────────────────────────────────────────
     */
    public static function getPermissionSlug(): string
    {
        $class = class_basename(static::class);

        return Str::snake(Str::before($class, 'Resource'));
    }

    /**
     * ──────────────────────────────────────────────────────────────
     * 📌 الدالة: canViewAny
     * ──────────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    التحقق من صلاحية المستخدم لعرض قائمة الموارد.
     *    يستخدم الإذن: view_any_{slug}.
     *
     * 📥 المدخلات:
     *    - لا توجد
     *
     * 📤 المخرجات:
     *    - bool ← true إذا كان المستخدم يملك الإذن
     * ──────────────────────────────────────────────────────────────
     */
    public static function canViewAny(): bool
    {
        $user = auth()->user();
        if (! $user) {
            return false;
        }
        $permission = 'view_any_'.static::getPermissionSlug();

        return $user->can($permission);
    }

    /**
     * ──────────────────────────────────────────────────────────────
     * 📌 الدالة: canView
     * ──────────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    التحقق من صلاحية المستخدم لعرض سجل معين.
     *    يستخدم الإذن: view_{slug}.
     *
     * 📥 المدخلات:
     *    - $record: mixed ← السجل المراد عرضه
     *
     * 📤 المخرجات:
     *    - bool ← true إذا كان المستخدم يملك الإذن
     * ──────────────────────────────────────────────────────────────
     */
    public static function canView($record): bool
    {
        $user = auth()->user();
        if (! $user) {
            return false;
        }
        $permission = 'view_'.static::getPermissionSlug();

        return $user->can($permission);
    }

    /**
     * ──────────────────────────────────────────────────────────────
     * 📌 الدالة: canCreate
     * ──────────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    التحقق من صلاحية المستخدم لإنشاء سجل جديد.
     *    يستخدم الإذن: create_{slug}.
     *
     * 📥 المدخلات:
     *    - لا توجد
     *
     * 📤 المخرجات:
     *    - bool ← true إذا كان المستخدم يملك الإذن
     * ──────────────────────────────────────────────────────────────
     */
    public static function canCreate(): bool
    {
        $user = auth()->user();
        if (! $user) {
            return false;
        }
        $permission = 'create_'.static::getPermissionSlug();

        return $user->can($permission);
    }

    /**
     * ──────────────────────────────────────────────────────────────
     * 📌 الدالة: canEdit
     * ──────────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    التحقق من صلاحية المستخدم لتعديل سجل معين.
     *    يستخدم الإذن: update_{slug}.
     *
     * 📥 المدخلات:
     *    - $record: mixed ← السجل المراد تعديله
     *
     * 📤 المخرجات:
     *    - bool ← true إذا كان المستخدم يملك الإذن
     * ──────────────────────────────────────────────────────────────
     */
    public static function canEdit($record): bool
    {
        $user = auth()->user();
        if (! $user) {
            return false;
        }
        $permission = 'update_'.static::getPermissionSlug();

        return $user->can($permission);
    }

    /**
     * ──────────────────────────────────────────────────────────────
     * 📌 الدالة: canDelete
     * ──────────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    التحقق من صلاحية المستخدم لحذف سجل معين.
     *    يستخدم الإذن: delete_{slug}.
     *
     * 📥 المدخلات:
     *    - $record: mixed ← السجل المراد حذفه
     *
     * 📤 المخرجات:
     *    - bool ← true إذا كان المستخدم يملك الإذن
     * ──────────────────────────────────────────────────────────────
     */
    public static function canDelete($record): bool
    {
        $user = auth()->user();
        if (! $user) {
            return false;
        }
        $permission = 'delete_'.static::getPermissionSlug();

        return $user->can($permission);
    }

    /**
     * ──────────────────────────────────────────────────────────────
     * 📌 الدالة: getGloballySearchableAttributes
     * ──────────────────────────────────────────────────────────────
     * 🎯 الغرض:
     *    تعطيل البحث العام في Filament للموارد الحساسة.
     *    يُعاد تعريفه في الموارد التي تحتاج حماية خصوصية.
     *
     * 📥 المدخلات:
     *    - لا توجد
     *
     * 📤 المخرجات:
     *    - array ← مصفوفة فارغة (تعطيل البحث)
     * ──────────────────────────────────────────────────────────────
     */
    public static function getGloballySearchableAttributes(): array
    {
        return [];
    }
}
