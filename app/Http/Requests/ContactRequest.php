<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'firstname' => ['bail', 'required', 'string', 'max:150'],
            'email' => ['bail', 'required', 'email', 'max:254'],
            'project_type' => ['nullable', 'string', 'max:100'],
            'budget' => ['nullable', 'string', 'max:100'],
            'details' => ['bail', 'required', 'string', 'max:5000'],
            'website' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'firstname.required' => 'Please enter your name.',
            'email.required' => 'Please enter your email address.',
            'email.email' => 'Please enter a valid email address.',
            'details.required' => 'Please tell me a little about your project.',
        ];
    }
}
