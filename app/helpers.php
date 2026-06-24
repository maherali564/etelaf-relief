<?php

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

/**
 * الحصول على قيمة حقل قابل للترجمة من النموذج.
 *
 * تحاول هذه الدالة الحصول على ترجمة الحقل باللغة المحددة،
 * وإذا لم تكن متوفرة تعود إلى لغة التطبيق الافتراضية (fallback_locale).
 * إذا كان النموذج لا يستخدم Trait الترجمة، تعيد قيمة الحقل مباشرة.
 *
 * @param  Model|null  $model   النموذج المراد استخراج الحقل منه (يمكن أن يكون null)
 * @param  string      $field   اسم الحقل المراد الحصول على ترجمته
 * @param  string|null $locale  رمز اللغة المطلوبة (اختياري، الافتراضي لغة التطبيق الحالية)
 * @return string|null          قيمة الحقل المُترجَم أو null إذا كان النموذج غير موجود
 */
if (! function_exists('trans_field')) {
    function trans_field(?Model $model, string $field, ?string $locale = null): ?string
    {
        if (! $model) {
            return null;
        }

        $locale = $locale ?? app()->getLocale();

        if (in_array(HasTranslations::class, class_uses_recursive($model), true)) {
            return $model->getTranslation($field, $locale, false) ?: $model->getTranslation($field, config('app.fallback_locale'), false);
        }

        return $model->{$field} ?? null;
    }
}

/**
 * تنظيف نص HTML للسماح فقط بالوسوم الآمنة.
 *
 * تزيل هذه الدالة جميع وسوم HTML غير المسموح بها من النص،
 * مع الاحتفاظ بالوسوم الأساسية الآمنة للعرض مثل الفقرات، الروابط، القوائم، والجداول.
 * تستخدم للحماية من هجمات XSS عند عرض محتوى HTML من المستخدمين.
 *
 * @param  string|null $html  نص HTML المراد تنظيفه
 * @return string|null        النص بعد تنظيفه من الوسوم غير المسموح بها، أو null إذا كان المدخل null
 */
if (! function_exists('safeHtml')) {
    function safeHtml(?string $html): ?string
    {
        if ($html === null) {
            return null;
        }

        $allowed = '<p><br><b><strong><i><em><u><a><ul><ol><li><h1><h2><h3><h4><h5><h6><blockquote><pre><code><hr><table><tr><td><th><span><div><img><sup><sub><del><ins>';

        return strip_tags($html, $allowed);
    }
}

/**
 * إنشاء رابط كامل مع بادئة اللغة.
 *
 * تبني رابط URL كاملاً يتضمن رمز اللغة المحدد في المسار.
 * تستخدم لإنشاء روابط داخلية في التطبيق مع دعم التوجيه حسب اللغة.
 *
 * @param  string $locale  رمز اللغة (مثال: 'ar', 'en', 'es')
 * @param  string $path    المسار النسبي (اختياري، مثل 'donate' أو 'about')
 * @return string          الرابط الكامل مع اللغة (مثال: http://example.com/ar/donate)
 */
if (! function_exists('locale_url')) {
    function locale_url(string $locale, string $path = ''): string
    {
        $path = ltrim($path, '/');

        return url('/'.$locale.($path ? '/'.$path : ''));
    }
}
