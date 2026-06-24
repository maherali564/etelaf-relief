<?php

namespace App\Http\Requests;

use App\Models\PaymentMethod;
use App\Rules\SafeEmail;
use Illuminate\Foundation\Http\FormRequest;

/**
 * طلب التحقق من صحة بيانات التبرع الجديد.
 *
 * يتحقق هذا الطلب من جميع الحقول المرسلة عند إنشاء تبرع جديد، بما في ذلك:
 * - بيانات المتبرع (الاسم، البريد الإلكتروني، الهاتف)
 * - المبلغ وطريقة الدفع
 * - التبرع المتكرر (شهري/ربع سنوي/سنوي)
 * - الارتباط بحملة أو مشروع أو قصة أو منشور
 * - التحقق من طريقة الدفع المشفرة (crypto) عند اختيارها
 *
 * @package App\Http\Requests
 */
class DonationStoreRequest extends FormRequest
{
    /**
     * التحقق من صلاحية المستخدم لإنشاء تبرع.
     *
     * يسمح لجميع الزوار (حتى غير المسجلين) بإنشاء تبرع.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * قواعد التحقق من صحة بيانات التبرع.
     *
     * القواعد تشمل:
     * - `donor_name`: مطلوب، نص، حد أقصى 255 حرفاً
     * - `email`: مطلوب، بريد إلكتروني صالح، حد أقصى 255 حرفاً، مع التحقق من SafeEmail
     * - `phone`: اختياري، نص، حد أقصى 20 حرفاً
     * - `amount`: مطلوب، رقمي، بين 1 و 1,000,000
     * - `payment_method_id`: مطلوب، يجب أن يكون موجوداً في جدول payment_methods
     * - `is_anonymous`: اختياري، قيمة منطقية
     * - `is_recurring`: اختياري، قيمة منطقية
     * - `recurring_interval`: اختياري، نص، إحدى القيم: monthly, quarterly, yearly
     * - `project_id`: اختياري، يجب أن يكون موجوداً في جدول projects
 * - `story_id`: اختياري، يجب أن يكون موجوداً في جدول stories
     * - `cryptocurrency_id`: اختياري (إلزامي إذا كانت طريقة الدفع مشفرة)، موجود في جدول cryptocurrencies
     * - `crypto_network_id`: اختياري (إلزامي إذا كانت طريقة الدفع مشفرة)، موجود في جدول crypto_networks
     * - `notes`: اختياري، نص، حد أقصى 2000 حرف
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = [
            'donor_name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', new SafeEmail],
            'phone' => 'nullable|string|max:20',
            'amount' => 'required|numeric|min:1|max:1000000',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'is_anonymous' => 'nullable|boolean',
            'is_recurring' => 'nullable|boolean',
            'recurring_interval' => 'nullable|string|in:monthly,quarterly,yearly',
            'project_id' => 'nullable|exists:projects,id',
            'story_id' => 'nullable|exists:stories,id',
            'cryptocurrency_id' => 'nullable|exists:cryptocurrencies,id',
            'crypto_network_id' => 'nullable|exists:crypto_networks,id',
            'notes' => 'nullable|string|max:2000',
        ];

        if ($this->filled('payment_method_id')) {
            $method = PaymentMethod::with('gateway')->find($this->payment_method_id);
            if ($method && $method->gateway?->driver === 'crypto') {
                $rules['cryptocurrency_id'] = 'required|exists:cryptocurrencies,id';
                $rules['crypto_network_id'] = 'required|exists:crypto_networks,id';
            }
        }

        return $rules;
    }

    /**
     * التحضير للتحقق من الصحة قبل تطبيق القواعد.
     *
     * يتحقق من وجود حقل خفي (honeypot) `hp_website` — إذا كان مملوءاً،
     * فهذا يعني أن الطلب مرسل بواسطة بوت (روبوت) ويتم رفضه برمز 422.
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
