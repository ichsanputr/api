<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // You can check if the user is authorized to make this request
        // For example, you can check if the user is authenticated:
        // return auth()->check();
        return true; // Allow all users (set this to true or implement your own authorization logic)
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'username' => 'required|string|unique:users,username|max:255',
            'password' => 'required|string|min:6',
        ];
    }

    /**
     * Customize the error messages.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'username.required' => 'The username is required.',
            'email.required' => 'The email is required.',
            'password.required' => 'The password is required.',
        ];
    }
}