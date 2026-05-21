<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Worker;
use App\Models\WorkGroup;
use App\Models\GroupProductivity;
use App\Models\User;
use App\Services\ProductivitySolver;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductivitySolverTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }
    
    public function test_it_calculates_individual_productivity_correctly()
    {
        $group = WorkGroup::factory()->create([
            'name' => 'Test Group',
            'user_id' => $this->user->id
        ]);
        
        $worker1 = Worker::factory()->create([
            'last_name' => 'Иванов',
            'first_name' => 'Иван',
            'user_id' => $this->user->id
        ]);
        $worker2 = Worker::factory()->create([
            'last_name' => 'Петров',
            'first_name' => 'Пётр',
            'user_id' => $this->user->id
        ]);
        
        $group->workers()->attach([$worker1->id, $worker2->id]);
        
        GroupProductivity::create([
            'work_group_id' => $group->id,
            'worker_id' => $worker1->id,
            'volume' => 500,
            'time' => 8,
            'value' => 62.5,
            'user_id' => $this->user->id
        ]);
        
        GroupProductivity::create([
            'work_group_id' => $group->id,
            'worker_id' => $worker2->id,
            'volume' => 300,
            'time' => 6,
            'value' => 50,
            'user_id' => $this->user->id
        ]);
        
        $solver = new ProductivitySolver($group);
        $results = $solver->calculateProductivity();
        
        $this->assertEquals(62.5, $results[0]);
        $this->assertEquals(50, $results[1]);
    }
    
    public function test_it_calculates_total_productivity_correctly()
    {
        $group = WorkGroup::factory()->create([
            'name' => 'Test Group',
            'user_id' => $this->user->id
        ]);
        
        $worker1 = Worker::factory()->create(['user_id' => $this->user->id]);
        $worker2 = Worker::factory()->create(['user_id' => $this->user->id]);
        
        $group->workers()->attach([$worker1->id, $worker2->id]);
        
        GroupProductivity::create([
            'work_group_id' => $group->id,
            'worker_id' => $worker1->id,
            'volume' => 500,
            'time' => 8,
            'value' => 62.5,
            'user_id' => $this->user->id
        ]);
        
        GroupProductivity::create([
            'work_group_id' => $group->id,
            'worker_id' => $worker2->id,
            'volume' => 300,
            'time' => 6,
            'value' => 50,
            'user_id' => $this->user->id
        ]);
        
        $solver = new ProductivitySolver($group);
        $solver->calculateProductivity();
        
        $this->assertEquals(112.5, $solver->getTotalProductivity());
        $this->assertEquals(56.25, $solver->getAverageProductivity());
    }
    
    public function test_it_handles_zero_time_correctly()
    {
        $group = WorkGroup::factory()->create([
            'name' => 'Test Group',
            'user_id' => $this->user->id
        ]);
        
        $worker = Worker::factory()->create(['user_id' => $this->user->id]);
        $group->workers()->attach($worker->id);
        
        GroupProductivity::create([
            'work_group_id' => $group->id,
            'worker_id' => $worker->id,
            'volume' => 100,
            'time' => 0,
            'value' => 0,
            'user_id' => $this->user->id
        ]);
        
        $solver = new ProductivitySolver($group);
        $results = $solver->calculateProductivity();
        
        $this->assertEquals(0, $results[0]);
    }
    
    public function test_it_handles_empty_data_correctly()
    {
        $group = WorkGroup::factory()->create([
            'name' => 'Test Group',
            'user_id' => $this->user->id
        ]);
        
        $worker = Worker::factory()->create(['user_id' => $this->user->id]);
        $group->workers()->attach($worker->id);
        
        $solver = new ProductivitySolver($group);
        $results = $solver->calculateProductivity();
        
        $this->assertEquals(0, $results[0]);
    }
}