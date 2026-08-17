<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateListingRequest extends FormRequest
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
                'nullable',
                'string',
                'max:5000',
            ],
            'price' => [
                'sometimes',
                'numeric',
                'min:0',
            ],
            'condition' => [
                'sometimes',
                'in:new,like_new,good,fair',
            ],
        ];
    }
}
