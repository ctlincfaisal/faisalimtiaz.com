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
            'firstname'     =>      'required',
            'lastname'     =>      'required',
            'email'     =>      'required',
            'budget'     =>      'required',
            'details'     =>      'required'
        ];
    }

    public function messages(): array
    {
        return [
            'firstname.required'     =>      1,
            'lastname.required'     =>      2,
            'email.required'     =>      3,
            'budget.required'     =>      4,
            'details.required'     =>      5
        ];
    }

}
