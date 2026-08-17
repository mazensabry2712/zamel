<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SuspendUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'suspended_until' => [
                'required',
                'date',
                'after:now',
            ],

            'reason' => [
                'required',
                'string',
                'min:5',
                'max:1000',
            ],
        ];
    }
}
