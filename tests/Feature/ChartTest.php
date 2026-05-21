<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Worker;
use App\Models\WorkGroup;
use App\Models\GroupProductivity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class ChartTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function chart_page_loads_without_group(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        
        $response = $this->get('/charts');
        $response->assertStatus(200);
    }
    
    #[Test]
    public function chart_page_with_selected_group(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        
        $group = WorkGroup::factory()->create([
            'name' => 'Тестовая группа',
            'user_id' => $user->id
        ]);
        
        $worker = Worker::factory()->create([
            'last_name' => 'Тестов',
            'first_name' => 'Тест',
            'patronymic' => 'Тестович',
            'user_id' => $user->id
        ]);
        
        $group->workers()->attach($worker->id);
        
        GroupProductivity::create([
            'work_group_id' => $group->id,
            'worker_id' => $worker->id,
            'volume' => 100,
            'time' => 8,
            'value' => 12.5,
            'user_id' => $user->id
        ]);
        
        $response = $this->get("/charts?group_id={$group->id}");
        $response->assertStatus(200);
        
        // Проверяем, что на странице есть canvas (график) — ищем просто canvas
        $response->assertSee('canvas');
    }
    
    #[Test]
    public function chart_page_shows_message_when_group_has_no_workers(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        
        $group = WorkGroup::factory()->create([
            'name' => 'Пустая группа',
            'user_id' => $user->id
        ]);
        
        $response = $this->get("/charts?group_id={$group->id}");
        $response->assertStatus(200);
        $response->assertSee('В группе нет рабочих');
    }
    
    #[Test]
    public function chart_page_shows_message_when_group_not_selected(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        
        $group = WorkGroup::factory()->create([
            'name' => 'Группа без выбора',
            'user_id' => $user->id
        ]);
        
        // Группа существует, но не выбрана в запросе
        $response = $this->get('/charts');
        $response->assertStatus(200);
        $response->assertSee('Выберите группу для отображения графика');
    }
}