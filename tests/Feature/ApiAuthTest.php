<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\WorkGroup;
use App\Models\Worker;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ApiAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_cannot_access_api()
    {
        $response = $this->getJson('/api/v1/calculate/1');
        $response->assertStatus(401);
    }
    
    public function test_authenticated_can_access_api()
    {
        $user = User::factory()->create();
        $group = WorkGroup::factory()->create(['user_id' => $user->id]);
        $worker = Worker::factory()->create(['user_id' => $user->id]);
        $group->workers()->attach($worker->id);
        
        Sanctum::actingAs($user);
        
        $response = $this->getJson('/api/v1/calculate/' . $group->id);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'group' => ['id', 'name', 'workers_count'],
            'results'
        ]);
    }
    
    public function test_api_returns_404_for_nonexistent_group()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        
        $response = $this->getJson('/api/v1/calculate/99999');
        $response->assertStatus(404);
    }
    
    public function test_api_returns_only_user_own_group()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        $group1 = WorkGroup::factory()->create(['user_id' => $user1->id, 'name' => 'Group User 1']);
        $group2 = WorkGroup::factory()->create(['user_id' => $user2->id, 'name' => 'Group User 2']);
        
        Sanctum::actingAs($user1);
        
        $response = $this->getJson('/api/v1/calculate/' . $group2->id);
        $response->assertStatus(404);
    }
}