<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WorkerRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }
    
    public function rules()
    {
        return [
            'last_name' => 'required|string|max:50|regex:/^[а-яА-ЯёЁa-zA-Z-]+$/u',
            'first_name' => 'required|string|max:50|regex:/^[а-яА-ЯёЁa-zA-Z-]+$/u',
            'patronymic' => 'nullable|string|max:50|regex:/^[а-яА-ЯёЁa-zA-Z-]*$/u',
            'age' => 'required|integer|min:18|max:100',
            'experience' => 'required|integer|min:0|max:80',
            'gender' => 'required|in:0,1'
        ];
    }
    
    public function messages()
    {
        return [
            'last_name.required' => 'Фамилия обязательна',
            'first_name.required' => 'Имя обязательно',
            'age.min' => 'Возраст должен быть не менее 18 лет',
            'experience.min' => 'Стаж не может быть отрицательным'
        ];
    }
}