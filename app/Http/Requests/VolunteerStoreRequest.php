<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VolunteerStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
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

    protected function prepareForValidation(): void
    {
        if ($this->filled('hp_website')) {
            abort(422, 'Spam detected');
        }
    }
}
