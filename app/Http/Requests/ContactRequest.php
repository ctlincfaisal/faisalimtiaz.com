<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ContactRequest extends FormRequest
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
            'firstname' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:255'],
            'budget' => ['nullable', 'string', 'max:100'],
            'details' => ['required', 'string', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return [
            'firstname.required' => 1,
            'email.required' => 3,
            'details.required' => 5,
        ];
    }

}
