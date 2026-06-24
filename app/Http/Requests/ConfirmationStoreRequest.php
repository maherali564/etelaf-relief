<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * طلب التحقق من صحة بيانات تأكيد التحويل البنكي.
 *
 * يتحقق من صحة الحقول المرسلة عند تأكيد المتبرع لإجراء تحويل بنكي
 * يدوي، بما في ذلك رقم المرجع والمبلغ والعملة وبيانات المرسل
 * وتاريخ التحويل والملاحظات ومستند الإثبات (مرفق).
 *
 * @package App\Http\Requests
 */
class ConfirmationStoreRequest extends FormRequest
{
    /**
     * التحقق من صلاحية المستخدم لتأكيد التحويل.
     *
     * يسمح لجميع الزوار بتأكيد التحويلات البنكية.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * قواعد التحقق من صحة بيانات تأكيد التحويل.
     *
     * القواعد تشمل:
     * - `reference_number`: اختياري، نص، حد أقصى 255 حرفاً (رقم مرجع التحويل)
     * - `amount`: اختياري، قيمة رقمية (المبلغ المحول)
     * - `currency`: اختياري، نص، حد أقصى 10 أحرف (رمز العملة)
     * - `sender_name`: اختياري، نص، حد أقصى 255 حرفاً (اسم المرسل)
     * - `sender_account`: اختياري، نص، حد أقصى 255 حرفاً (رقم حساب المرسل)
     * - `transfer_date`: اختياري، تاريخ صالح (تاريخ التحويل)
     * - `notes`: اختياري، نص، حد أقصى 2000 حرف (ملاحظات إضافية)
     * - `proof_document`: اختياري، ملف من نوع jpg/jpeg/png/pdf،
     *   حد أقصى 5 ميجابايت (5120 كيلوبايت) (مستند إثبات التحويل)
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'reference_number' => 'nullable|string|max:255',
            'amount' => 'nullable|numeric',
            'currency' => 'nullable|string|max:10',
            'sender_name' => 'nullable|string|max:255',
            'sender_account' => 'nullable|string|max:255',
            'transfer_date' => 'nullable|date',
            'notes' => 'nullable|string|max:2000',
            'proof_document' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ];
    }
}
