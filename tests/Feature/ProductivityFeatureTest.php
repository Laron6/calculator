<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Worker;
use App\Models\WorkGroup;
use App\Models\GroupProductivity;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class ProductivityFeatureTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function full_productivity_calculation_scenario(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->assertAuthenticated();

        $response = $this->get('/');
        $response->assertStatus(200);

        $response = $this->post('/worker/add', [
            'last_name' => 'Иванов',
            'first_name' => 'Иван',
            'patronymic' => 'Иванович',
            'age' => 30,
            'experience' => 10,
            'gender' => 0,
        ]);
        $response->assertRedirect();

        $worker1 = Worker::where('last_name', 'Иванов')->where('user_id', $user->id)->first();
        $this->assertNotNull($worker1);

        $this->post('/worker/add', [
            'last_name' => 'Петров',
            'first_name' => 'Пётр',
            'patronymic' => 'Петрович',
            'age' => 35,
            'experience' => 15,
            'gender' => 0,
        ]);

        $worker2 = Worker::where('last_name', 'Петров')->where('user_id', $user->id)->first();
        $this->assertNotNull($worker2);

        $response = $this->post('/group/create', [
            'name' => 'Тестовая бригада',
        ]);
        $response->assertRedirect();

        $group = WorkGroup::where('name', 'Тестовая бригада')->where('user_id', $user->id)->first();
        $this->assertNotNull($group);

        $this->post("/group/{$group->id}/add-worker", ['worker_id' => $worker1->id]);
        $this->post("/group/{$group->id}/add-worker", ['worker_id' => $worker2->id]);

        $this->assertEquals(2, $group->workers()->count());

        GroupProductivity::where('work_group_id', $group->id)->delete();

        $this->post("/group/{$group->id}/productivity", [
            'volumes' => [$worker1->id => 500, $worker2->id => 300],
            'times' => [$worker1->id => 8, $worker2->id => 6],
        ]);

        $response = $this->get("/?tab=statistics&group_id={$group->id}&calculated=1");
        $response->assertStatus(200);
        $response->assertSee('Результаты расчёта');
        $response->assertSee('62.50');
        $response->assertSee('50.00');

        Sanctum::actingAs($user);
        $apiResponse = $this->get("/api/v1/calculate/{$group->id}");
        $apiResponse->assertStatus(200);
        $apiResponse->assertJson([
            'success' => true,
            'group' => ['id' => $group->id, 'name' => 'Тестовая бригада', 'workers_count' => 2],
        ]);

        $response = $this->get('/devices');
        $response->assertStatus(200);
    }

    #[Test]
    public function decimal_time_calculation_works_correctly(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $worker = Worker::factory()->create([
            'last_name' => 'Тестов',
            'first_name' => 'Тест',
            'user_id' => $user->id,
        ]);

        $group = WorkGroup::factory()->create([
            'name' => 'Дробная группа',
            'user_id' => $user->id,
        ]);

        $group->workers()->attach($worker->id);

        GroupProductivity::where('work_group_id', $group->id)->delete();

        $this->post("/group/{$group->id}/productivity", [
            'volumes' => [$worker->id => 100],
            'times' => [$worker->id => 6.5],
        ]);

        $response = $this->get("/?tab=statistics&group_id={$group->id}&calculated=1");
        $response->assertStatus(200);

        $response->assertSee('15.38');
    }

    #[Test]
    public function empty_workers_are_excluded_from_average(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $group = WorkGroup::factory()->create([
            'name' => 'Тестовая группа',
            'user_id' => $user->id,
        ]);

        $worker1 = Worker::factory()->create([
            'last_name' => 'Рабочий1',
            'first_name' => 'Тест',
            'user_id' => $user->id,
        ]);

        $worker2 = Worker::factory()->create([
            'last_name' => 'Рабочий2',
            'first_name' => 'Тест',
            'user_id' => $user->id,
        ]);

        $group->workers()->attach([$worker1->id, $worker2->id]);

        GroupProductivity::where('work_group_id', $group->id)->delete();

        $this->post("/group/{$group->id}/productivity", [
            'volumes' => [$worker1->id => 100, $worker2->id => null],
            'times' => [$worker1->id => 8, $worker2->id => null],
        ]);

        $response = $this->get("/?tab=statistics&group_id={$group->id}&calculated=1");
        $response->assertStatus(200);

        $response->assertSee('12.50');
        $response->assertDontSee('6.25');
    }
}