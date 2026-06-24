<?php

namespace App\Http\Requests;

use App\Rules\SafeEmail;
use Illuminate\Foundation\Http\FormRequest;

/**
 * طلب التحقق من صحة بيانات تسجيل دخول المتبرع.
 *
 * يتحقق من صحة البريد الإلكتروني وكلمة المرور عند محاولة المتبرع
 * تسجيل الدخول إلى حسابه. يستخدم قاعدة SafeEmail للحماية من هجمات
 * CRLF injection (CVE-2026-48019).
 *
 * @package App\Http\Requests
 */
class DonorLoginRequest extends FormRequest
{
    /**
     * التحقق من صلاحية المستخدم لتسجيل الدخول.
     *
     * يسمح لجميع الزوار (حتى غير المسجلين) بمحاولة تسجيل الدخول.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * قواعد التحقق من صحة بيانات تسجيل الدخول.
     *
     * القواعد تشمل:
     * - `email`: مطلوب، بريد إلكتروني صالح، مع التحقق من SafeEmail
     * - `password`: مطلوب، نص
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email', new SafeEmail],
            'password' => 'required|string',
        ];
    }
}
