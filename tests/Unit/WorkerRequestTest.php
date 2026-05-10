<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Requests\WorkerRequest;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\Test;

class WorkerRequestTest extends TestCase
{
    #[Test]
    public function valid_data_passes_validation(): void
    {
        $data = [
            'last_name' => 'Иванов',
            'first_name' => 'Иван',
            'patronymic' => 'Иванович',
            'age' => 30,
            'experience' => 10,
            'gender' => 0,
        ];

        $request = new WorkerRequest();
        $validator = Validator::make($data, $request->rules(), $request->messages());

        $this->assertFalse($validator->fails());
    }

    #[Test]
    public function empty_last_name_fails(): void
    {
        $data = [
            'last_name' => '',
            'first_name' => 'Иван',
            'age' => 30,
            'experience' => 10,
            'gender' => 0,
        ];

        $request = new WorkerRequest();
        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('last_name', $validator->errors()->toArray());
    }

    #[Test]
    public function latin_letters_in_last_name_fails(): void
    {
        $data = [
            'last_name' => 'Ivanov',
            'first_name' => 'Иван',
            'age' => 30,
            'experience' => 10,
            'gender' => 0,
        ];

        $request = new WorkerRequest();
        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->fails());
    }

    #[Test]
    public function age_below_18_fails(): void
    {
        $data = [
            'last_name' => 'Иванов',
            'first_name' => 'Иван',
            'age' => 16,
            'experience' => 0,
            'gender' => 0,
        ];

        $request = new WorkerRequest();
        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('age', $validator->errors()->toArray());
    }

    #[Test]
    public function experience_exceeding_age_minus_18_fails(): void
    {
        $request = new WorkerRequest();
        $request->merge([
            'last_name' => 'Иванов',
            'first_name' => 'Иван',
            'age' => 25,
            'experience' => 15,
            'gender' => 0,
        ]);

        $validator = Validator::make(
            $request->all(),
            $request->rules(),
            $request->messages()
        );

        $request->withValidator($validator);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('experience', $validator->errors()->toArray());
    }

    #[Test]
    public function invalid_gender_fails(): void
    {
        $data = [
            'last_name' => 'Иванов',
            'first_name' => 'Иван',
            'age' => 30,
            'experience' => 10,
            'gender' => 5,
        ];

        $request = new WorkerRequest();
        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('gender', $validator->errors()->toArray());
    }
}