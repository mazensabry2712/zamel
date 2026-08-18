<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMarketplaceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'category_id' => [
                'sometimes',
                'integer',
                'exists:categories,id',
            ],
            'title' => [
                'sometimes',
                'string',
                'min:3',
                'max:255',
            ],
            'description' => [
                'sometimes',
                'string',
                'max:5000',
            ],
            'budget' => [
                'sometimes',
                'nullable',
                'numeric',
                'min:0',
            ],
            'expires_at' => [
                'sometimes',
                'nullable',
                'date',
                'after:now',
            ],
        ];
    }
}
