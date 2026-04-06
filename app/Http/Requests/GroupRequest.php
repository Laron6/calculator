<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GroupRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }
    
    public function rules()
    {
        return [
            'name' => 'required|string|max:100|regex:/^[а-яА-ЯёЁ0-9\s-]+$/u'
        ];
    }
    
    public function messages()
    {
        return [
            'name.required' => 'Название группы обязательно',
            'name.regex' => 'Название может содержать только русские буквы, цифры, пробелы и дефис'
        ];
    }
}