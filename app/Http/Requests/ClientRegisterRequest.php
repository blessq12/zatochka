<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ClientRegisterRequest extends FormRequest
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
            'full_name' => 'required|string|max:255',
            'phone' => [
                'required',
                'string',
                'max:20',
                Rule::unique('clients', 'phone'),
            ],
            'telegram' => 'nullable|string|max:50',
            'birth_date' => 'nullable|date|before:today',
            'delivery_address' => 'nullable|string|max:1000',
            'password' => 'required|string|min:6|confirmed',
            'password_confirmation' => 'required|string|min:6',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'full_name.required' => 'ФИО обязательно',
            'full_name.string' => 'ФИО должно быть строкой',
            'full_name.max' => 'ФИО не может быть длиннее 255 символов',
            'phone.required' => 'Номер телефона обязателен',
            'phone.string' => 'Номер телефона должен быть строкой',
            'phone.max' => 'Номер телефона не может быть длиннее 20 символов',
            'phone.unique' => 'Клиент с таким номером телефона уже существует',
            'telegram.string' => 'Telegram должен быть строкой',
            'telegram.max' => 'Telegram не может быть длиннее 50 символов',
            'birth_date.date' => 'Дата рождения должна быть корректной датой',
            'birth_date.before' => 'Дата рождения не может быть в будущем',
            'delivery_address.string' => 'Адрес доставки должен быть строкой',
            'delivery_address.max' => 'Адрес доставки не может быть длиннее 1000 символов',
            'password.required' => 'Пароль обязателен',
            'password.string' => 'Пароль должен быть строкой',
            'password.min' => 'Пароль должен содержать минимум 6 символов',
            'password.confirmed' => 'Пароли не совпадают',
            'password_confirmation.required' => 'Подтверждение пароля обязательно',
            'password_confirmation.string' => 'Подтверждение пароля должно быть строкой',
            'password_confirmation.min' => 'Подтверждение пароля должно содержать минимум 6 символов',
        ];
    }
}
