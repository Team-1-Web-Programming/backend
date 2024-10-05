<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddressEditRequest extends FormRequest
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
            'is_default' => 'boolean',
            'name' => 'string|max:255',
            'phone' => 'string|max:255',
            'address' => 'string|max:255',
            'city' => 'string|max:255',
            'province' => 'string|max:255',
            'country' => 'string|max:255',
            'postal_code' => 'string|max:255',
        ];
    }
}
