<?php

namespace App\Http\Requests;

use App\Rules\SafeEmail;
use Illuminate\Foundation\Http\FormRequest;

/**
 * طلب التحقق من صحة بيانات نموذج الاتصال.
 *
 * يتحقق من صحة الحقول المرسلة عبر نموذج الاتصال بالموقع،
 * بما في ذلك الاسم والبريد الإلكتروني (مع SafeEmail) والموضوع والرسالة.
 * يستخدم حقل خفي (honeypot) لمكافحة البوتات.
 *
 * @package App\Http\Requests
 */
class ContactStoreRequest extends FormRequest
{
    /**
     * التحقق من صلاحية المستخدم لإرسال نموذج الاتصال.
     *
     * يسمح لجميع الزوار بإرسال رسائل الاتصال.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * قواعد التحقق من صحة بيانات الاتصال.
     *
     * القواعد تشمل:
     * - `name`: مطلوب، نص، حد أقصى 255 حرفاً
     * - `email`: مطلوب، بريد إلكتروني صالح، حد أقصى 255 حرفاً، مع التحقق من SafeEmail
     * - `subject`: مطلوب، نص، حد أقصى 255 حرفاً
     * - `message`: مطلوب، نص، حد أقصى 5000 حرف
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', new SafeEmail],
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
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
