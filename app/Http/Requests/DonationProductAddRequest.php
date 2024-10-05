<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DonationProductAddRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if ($address = $this->user()->addresses()->where('is_default', 1)->first()) {
            # merge with default address
            $this->merge(['address_id' => $address->id]);
            return true;
        }

        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'amount' => 'required|integer|min:1',
            'address_id' => 'required|exists:addresses,id',
            'category_id' => 'array',
            'category_id.*' => 'exists:mysql.donation_categories,id',
            'media' => 'array',
            'media.*' => ['nullable', 'mimes:jpeg,png,jpg', 'max:1024']
        ];
    }
}
