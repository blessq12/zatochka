<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ClientPasswordResetRequest extends FormRequest
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
            'phone' => 'required|string|max:20|exists:clients,phone',
            'password' => 'required|string|min:6|confirmed',
            'password_confirmation' => 'required|string|min:6',
            'token' => 'required|string',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'phone.required' => 'Номер телефона обязателен',
            'phone.string' => 'Номер телефона должен быть строкой',
            'phone.max' => 'Номер телефона не может быть длиннее 20 символов',
            'phone.exists' => 'Клиент с таким номером телефона не найден',
            'password.required' => 'Новый пароль обязателен',
            'password.string' => 'Новый пароль должен быть строкой',
            'password.min' => 'Новый пароль должен содержать минимум 6 символов',
            'password.confirmed' => 'Пароли не совпадают',
            'password_confirmation.required' => 'Подтверждение пароля обязательно',
            'password_confirmation.string' => 'Подтверждение пароля должно быть строкой',
            'password_confirmation.min' => 'Подтверждение пароля должно содержать минимум 6 символов',
            'token.required' => 'Токен сброса пароля обязателен',
            'token.string' => 'Токен должен быть строкой',
        ];
    }
}
