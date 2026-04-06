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
            'last_name' => 'required|string|max:50|regex:/^[а-яА-ЯёЁ-]+$/u',
            'first_name' => 'required|string|max:50|regex:/^[а-яА-ЯёЁ-]+$/u',
            'patronymic' => 'nullable|string|max:50|regex:/^[а-яА-ЯёЁ-]*$/u',
            'age' => 'required|integer|min:18|max:100',
            'experience' => 'required|integer|min:0|max:80',
            'gender' => 'required|in:0,1'
        ];
    }
    
    public function messages()
    {
        return [
            'last_name.required' => 'Фамилия обязательна',
            'last_name.regex' => 'Фамилия может содержать только русские буквы и дефис',
            'first_name.required' => 'Имя обязательно',
            'first_name.regex' => 'Имя может содержать только русские буквы и дефис',
            'patronymic.regex' => 'Отчество может содержать только русские буквы и дефис',
            'age.min' => 'Возраст должен быть не менее 18 лет',
            'age.max' => 'Возраст не должен превышать 100 лет',
            'age.integer' => 'Возраст должен быть целым числом',
            'experience.min' => 'Стаж не может быть отрицательным',
            'gender.required' => 'Выберите пол'
        ];
    }
    
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $age = $this->input('age');
            $experience = $this->input('experience');
            
            if ($experience > ($age - 18)) {
                $validator->errors()->add('experience', 'Стаж не может быть больше возраста минус 18 лет');
            }
        });
    }
}