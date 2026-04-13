<?php

namespace App\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => [
                'required',
                'min:8',
                'confirmed',
                'regex:/[A-Z]/',
                'regex:/[a-z]/',
                'regex:/[0-9]/',
                'regex:/[@$!%*#?&]/',
            ],
        ];
    }
    
    public function messages(): array
    {
        return [
            'name.required' => 'Имя обязательно',
            'name.string' => 'Имя должно быть строкой',
            'name.max' => 'Имя не должно превышать 255 символов',
            
            'email.required' => 'Email обязателен',
            'email.email' => 'Введите корректный email адрес',
            'email.unique' => 'Пользователь с таким email уже зарегистрирован',
            
            'password.required' => 'Пароль обязателен',
            'password.min' => 'Пароль должен быть не менее 8 символов',
            'password.confirmed' => 'Пароли не совпадают',
            'password.regex' => 'Пароль должен содержать заглавные, строчные буквы, цифры и спецсимволы (@$!%*#?&)',
        ];
    }
}