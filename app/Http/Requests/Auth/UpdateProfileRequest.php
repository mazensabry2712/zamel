<?php

namespace App\Http\Requests\Auth;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'education_type' => [
                'sometimes',
                Rule::in(['secondary', 'university', 'other']),
            ],
            'university_id' => [
                'sometimes',
                'nullable',
                'integer',
                'exists:universities,id',
                'required_if:education_type,university',
            ],
            'faculty_id' => [
                'sometimes',
                'nullable',
                'integer',
                'exists:faculties,id',
                'required_if:education_type,university',
            ],
            'school_id' => [
                'sometimes',
                'nullable',
                'integer',
                'exists:schools,id',
                'required_if:education_type,secondary',
            ],
            'academic_year_id' => [
                'sometimes',
                'nullable',
                'integer',
                'exists:academic_years,id',
                'required_if:education_type,university',
            ],
            'phone' => [
                'sometimes',
                'nullable',
                'string',
                'max:30',
            ],
            'bio' => [
                'sometimes',
                'nullable',
                'string',
                'max:1000',
            ],
            'avatar' => [
                'sometimes',
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
        ];
    }
}
