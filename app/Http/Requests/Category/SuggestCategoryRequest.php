<?php

namespace App\Http\Requests\Category;

use Illuminate\Foundation\Http\FormRequest;

class SuggestCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'min:2',
                'max:100',
                'unique:categories,name',
            ],
            'description' => [
                'nullable',
                'string',
                'max:1000',
            ],
            'seo_title' => [
                'nullable',
                'string',
                'max:255',
            ],
            'seo_description' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ];
    }
}
