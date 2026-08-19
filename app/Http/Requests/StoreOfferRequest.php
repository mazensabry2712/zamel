<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOfferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'price' => [
                'required',
                'numeric',
                'min:0',
            ],
            'condition' => [
                'required',
                'in:new,like_new,good,fair',
            ],
            'message' => [
                'nullable',
                'string',
                'max:2000',
            ],
            'expires_at' => [
                'nullable',
                'date',
                'after:now',
            ],
        ];
    }
}
