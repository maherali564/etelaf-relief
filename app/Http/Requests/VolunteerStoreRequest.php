<?php

namespace App\Http\Requests;

use App\Rules\SafeEmail;
use Illuminate\Foundation\Http\FormRequest;

/**
 * طلب التحقق من صحة بيانات تقديم متطوع جديد.
 *
 * يتحقق من صحة جميع الحقول المرسلة عند تقديم شخص للتطوع،
 * بما في ذلك المعلومات الشخصية (الاسم، البريد، الهاتف، العنوان)
 * ومعلومات الطوارئ والمهارات ومدى التوفر. يستخدم حقل خفي (honeypot)
 * لمكافحة البوتات.
 *
 * @package App\Http\Requests
 */
class VolunteerStoreRequest extends FormRequest
{
    /**
     * التحقق من صلاحية المستخدم لتقديم طلب تطوع.
     *
     * يسمح لجميع الزوار بتقديم طلب تطوع.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * قواعد التحقق من صحة بيانات التطوع.
     *
     * القواعد تشمل:
     * - `name`: مطلوب، نص، حد أقصى 255 حرفاً
     * - `email`: مطلوب، بريد إلكتروني صالح، حد أقصى 255 حرفاً، مع التحقق من SafeEmail
     * - `phone`: مطلوب، نص، حد أقصى 20 حرفاً
     * - `national_id`: اختياري، نص، حد أقصى 50 حرفاً
     * - `date_of_birth`: اختياري، تاريخ صالح
     * - `address`: اختياري، نص، حد أقصى 1000 حرف
     * - `emergency_contact_name`: اختياري، نص، حد أقصى 255 حرفاً
     * - `emergency_contact_phone`: اختياري، نص، حد أقصى 50 حرفاً
     * - `skills`: اختياري، نص، حد أقصى 2000 حرف
     * - `availability`: اختياري، نص، حد أقصى 1000 حرف
     * - `message`: اختياري، نص، حد أقصى 2000 حرف
     * - `volunteer_opportunity_id`: اختياري، يجب أن يكون موجوداً في جدول volunteer_opportunities
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', new SafeEmail],
            'phone' => 'required|string|max:20',
            'national_id' => 'nullable|string|max:50',
            'date_of_birth' => 'nullable|date',
            'address' => 'nullable|string|max:1000',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:50',
            'skills' => 'nullable|string|max:2000',
            'availability' => 'nullable|string|max:1000',
            'message' => 'nullable|string|max:2000',
            'volunteer_opportunity_id' => 'nullable|exists:volunteer_opportunities,id',
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
