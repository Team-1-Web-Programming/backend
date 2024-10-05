<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DonationProductEditRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'string|max:255',
            'description' => 'string|max:255',
            'amount' => 'integer|min:1',
            'address_id' => 'exists:addresses,id',
            'category_id' => 'array',
            'category_id.*' => 'exists:donation_categories,id',
            'media' => 'array',
            'media.*' => ['nullable', 'mimes:jpeg,png,jpg', 'max:1024']
        ];
    }
}
