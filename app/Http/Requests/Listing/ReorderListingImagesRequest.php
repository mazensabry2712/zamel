<?php

namespace App\Http\Requests\Listing;

use Illuminate\Foundation\Http\FormRequest;

class ReorderListingImagesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'media_ids' => [
                'required',
                'array',
                'min:1',
                'max:8',
            ],
            'media_ids.*' => [
                'required',
                'integer',
                'distinct',
            ],
        ];
    }
}
