<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Worker;
use App\Models\WorkGroup;
use App\Models\GroupProductivity;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_see_other_users_workers()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        $worker1 = Worker::factory()->create(['user_id' => $user1->id, 'last_name' => 'Рабочий1']);
        $worker2 = Worker::factory()->create(['user_id' => $user2->id, 'last_name' => 'Рабочий2']);
        
        $this->actingAs($user1);
        $response = $this->get('/');
        
        $response->assertSee($worker1->last_name);
        $response->assertDontSee($worker2->last_name);
    }
    
    public function test_user_cannot_edit_other_users_worker()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        $worker = Worker::factory()->create([
            'user_id' => $user2->id,
            'last_name' => 'Иванов',
            'first_name' => 'Иван'
        ]);
        
        $this->actingAs($user1);
        $response = $this->post("/worker/update/{$worker->id}", [
            'last_name' => 'Петров',
            'first_name' => 'Петр',
            'age' => 30,
            'experience' => 5,
            'gender' => 0,
        ]);
        
        $response->assertStatus(403);
        $this->assertDatabaseMissing('workers', [
            'id' => $worker->id,
            'last_name' => 'Петров',
        ]);
    }
    
    public function test_user_cannot_delete_other_users_worker()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        $worker = Worker::factory()->create(['user_id' => $user2->id]);
        
        $this->actingAs($user1);
        $response = $this->delete("/worker/delete/{$worker->id}");
        
        $response->assertStatus(403);
        $this->assertDatabaseHas('workers', ['id' => $worker->id]);
    }
    
    public function test_user_cannot_see_other_users_groups()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        $group1 = WorkGroup::factory()->create(['user_id' => $user1->id, 'name' => 'Группа1']);
        $group2 = WorkGroup::factory()->create(['user_id' => $user2->id, 'name' => 'Группа2']);
        
        $this->actingAs($user1);
        $response = $this->get('/');
        
        $response->assertSee($group1->name);
        $response->assertDontSee($group2->name);
    }
    
    public function test_user_cannot_add_other_users_worker_to_group()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        $group = WorkGroup::factory()->create(['user_id' => $user1->id]);
        $worker = Worker::factory()->create(['user_id' => $user2->id]);
        
        $this->actingAs($user1);
        $response = $this->post("/group/{$group->id}/add-worker", [
            'worker_id' => $worker->id,
        ]);
        
        $response->assertStatus(404);
        $this->assertDatabaseMissing('group_worker', [
            'work_group_id' => $group->id,
            'worker_id' => $worker->id,
        ]);
    }
    
    public function test_user_cannot_add_worker_to_other_users_group()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        $group = WorkGroup::factory()->create(['user_id' => $user2->id]);
        $worker = Worker::factory()->create(['user_id' => $user1->id]);
        
        $this->actingAs($user1);
        $response = $this->post("/group/{$group->id}/add-worker", [
            'worker_id' => $worker->id,
        ]);
        
        $response->assertStatus(403);
    }
    
    public function test_user_cannot_view_other_users_productivity_data()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        $group1 = WorkGroup::factory()->create(['user_id' => $user1->id, 'name' => 'Группа1']);
        $group2 = WorkGroup::factory()->create(['user_id' => $user2->id, 'name' => 'Группа2']);
        
        $worker1 = Worker::factory()->create(['user_id' => $user1->id]);
        $worker2 = Worker::factory()->create(['user_id' => $user2->id]);
        
        $group1->workers()->attach($worker1->id);
        $group2->workers()->attach($worker2->id);
        
        GroupProductivity::create([
            'work_group_id' => $group2->id,
            'worker_id' => $worker2->id,
            'volume' => 100,
            'time' => 10,
            'value' => 10,
            'user_id' => $user2->id,
        ]);
        
        $this->actingAs($user1);
        
        $response = $this->get("/?tab=statistics&group_id={$group2->id}");
        $response->assertStatus(200);
        
        // Проверяем, что данные пользователя user2 не отображаются
        $response->assertDontSee('100');
        $response->assertDontSee('10');
    }
    
    public function test_user_cannot_save_productivity_for_other_users_group()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        $group = WorkGroup::factory()->create(['user_id' => $user2->id]);
        $worker = Worker::factory()->create(['user_id' => $user2->id]);
        $group->workers()->attach($worker->id);
        
        $this->actingAs($user1);
        $response = $this->post("/group/{$group->id}/productivity", [
            'volumes' => [$worker->id => 999],
            'times' => [$worker->id => 999],
        ]);
        
        $response->assertStatus(403);
        
        $this->assertDatabaseMissing('group_productivities', [
            'work_group_id' => $group->id,
            'volume' => 999,
        ]);
    }
}