<?php

namespace App\Http\Requests\Auth;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
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
            'name' => [
                'required',
                'string',
                'min:2',
                'max:100',
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email',
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],
            'education_type' => [
                'required',
                Rule::in(['secondary', 'university', 'other']),
            ],
            'university_id' => [
                'nullable',
                'integer',
                'exists:universities,id',
                'required_if:education_type,university',
            ],
            'faculty_id' => [
                'nullable',
                'integer',
                'exists:faculties,id',
                'required_if:education_type,university',
            ],
            'school_id' => [
                'nullable',
                'integer',
                'exists:schools,id',
                'required_if:education_type,secondary',
            ],
            'academic_year_id' => [
                'nullable',
                'integer',
                'exists:academic_years,id',
                'required_if:education_type,university',
            ],
        ];
    }
}
