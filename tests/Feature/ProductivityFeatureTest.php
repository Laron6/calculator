<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Worker;
use App\Models\WorkGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class ProductivityFeatureTest extends TestCase
{
    use RefreshDatabase;


    #[Test]
    public function full_productivity_calculation_scenario(): void
    {
        // 1: Регистрация пользователя
        $user = User::create([
            'name' => 'Тестовый Пользователь',
            'email' => 'test@example.com',
            'password' => bcrypt('Test123!@#'),
        ]);
        $this->actingAs($user);

        // Проверяем, что пользователь авторизован
        $this->assertAuthenticated();

        // 2: Главная страница открывается
        $response = $this->get('/');
        $response->assertStatus(200);

        // 3: Создание работника
        $response = $this->post('/worker/add', [
            'last_name' => 'Иванов',
            'first_name' => 'Иван',
            'patronymic' => 'Иванович',
            'age' => 30,
            'experience' => 10,
            'gender' => 0,
        ]);
        $response->assertRedirect();

        $worker1 = Worker::where('last_name', 'Иванов')->first();
        $this->assertNotNull($worker1);

        // Создаём второго работника
        $this->post('/worker/add', [
            'last_name' => 'Петров',
            'first_name' => 'Пётр',
            'patronymic' => 'Петрович',
            'age' => 35,
            'experience' => 15,
            'gender' => 0,
        ]);

        $worker2 = Worker::where('last_name', 'Петров')->first();
        $this->assertNotNull($worker2);

        // 4: Создание группы
        $response = $this->post('/group/create', [
            'name' => 'Тестовая бригада',
        ]);
        $response->assertRedirect();

        $group = WorkGroup::where('name', 'Тестовая бригада')->first();
        $this->assertNotNull($group);

        // 5: Добавление работников в группу
        $response = $this->post("/group/{$group->id}/add-worker", [
            'worker_id' => $worker1->id,
        ]);
        $response->assertRedirect();

        $response = $this->post("/group/{$group->id}/add-worker", [
            'worker_id' => $worker2->id,
        ]);
        $response->assertRedirect();

        // Проверяем, что в группе два работника
        $this->assertEquals(2, $group->workers()->count());

        // 6: Сохранение данных производительности
        $response = $this->post("/group/{$group->id}/productivity", [
            'volumes' => [
                $worker1->id => 500,
                $worker2->id => 300,
            ],
            'times' => [
                $worker1->id => 8,
                $worker2->id => 6,
            ],
        ]);
        $response->assertRedirect();

        // 7: Проверка расчёта через вызов страницы статистики
        $response = $this->get("/?tab=statistics&group_id={$group->id}&calculated=1");
        $response->assertStatus(200);
        $response->assertSee('Результаты расчёта');
        $response->assertSee('62.50'); // 500/8
        $response->assertSee('50.00'); // 300/6

        // 8: Проверка апи
        $apiResponse = $this->get("/api/v1/calculate/{$group->id}");
        $apiResponse->assertStatus(200);
        $apiResponse->assertJson([
            'success' => true,
            'group' => [
                'id' => $group->id,
                'name' => 'Тестовая бригада',
                'workers_count' => 2,
            ],
        ]);

        // Шаг 9: Проверка страницы устройств
        $response = $this->get('/devices');
        $response->assertStatus(200);
    }
}