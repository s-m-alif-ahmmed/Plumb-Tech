<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
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
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed',
            'role' => 'required|string|in:customer,engineer',
            'address' => 'required_if:role,customer|string|max:100',
            'service' => 'required_if:role,engineer|string|max:100',
            'skills' => 'required_if:role,engineer|array',
            'skills.*' => 'required_if:role,engineer|exists:skills,id',
            'about' => 'required_if:role,engineer|string|max:500',
            'portfolio' => 'required_if:role,engineer|array',
            'portfolio.*' => 'required_if:role,engineer|image|mimes:jpeg,jpg,png|max:10240',
        ];
    }
}
