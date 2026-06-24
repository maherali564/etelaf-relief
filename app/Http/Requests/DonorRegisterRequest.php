<?php

namespace App\Http\Requests;

use App\Rules\SafeEmail;
use Illuminate\Foundation\Http\FormRequest;

/**
 * طلب التحقق من صحة بيانات تسجيل متبرع جديد.
 *
 * يتحقق من صحة جميع الحقول المطلوبة لإنشاء حساب متبرع جديد،
 * بما في ذلك الاسم والبريد الإلكتروني (مع التحقق من uniqueness)
 * وكلمة المرور مع التأكيد. يستخدم حقل خفي (honeypot) لمكافحة البوتات.
 *
 * @package App\Http\Requests
 */
class DonorRegisterRequest extends FormRequest
{
    /**
     * التحقق من صلاحية المستخدم للتسجيل.
     *
     * يسمح لجميع الزوار بإنشاء حساب جديد.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * قواعد التحقق من صحة بيانات التسجيل.
     *
     * القواعد تشمل:
     * - `name`: مطلوب، نص، حد أقصى 255 حرفاً
     * - `email`: مطلوب، بريد إلكتروني صالح، حد أقصى 255 حرفاً،
     *   فريد في جدول `donors` (unique:donors,email)، مع التحقق من SafeEmail
     * - `phone`: اختياري، نص، حد أقصى 20 حرفاً
     * - `password`: مطلوب، نص، حد أدنى 8 أحرف، يجب تأكيده (confirmed)
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', 'unique:donors,email', new SafeEmail],
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8|confirmed',
        ];
    }

    /**
     * التحضير للتحقق من الصحة قبل تطبيق القواعد.
     *
     * يتحقق من وجود حقل خفي (honeypot) `hp_website` — إذا كان مملوءاً،
     * فهذا يعني أن الطلب مرسل بواسطة بوت ويتم رفضه برمز 422.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        if ($this->filled('hp_website')) {
            abort(422, 'Spam detected');
        }
    }
}
