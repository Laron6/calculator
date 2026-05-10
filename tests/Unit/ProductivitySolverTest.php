<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Worker;
use App\Models\WorkGroup;
use App\Models\GroupProductivity;
use App\Services\ProductivitySolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class ProductivitySolverTest extends TestCase
{
    use RefreshDatabase;

    private WorkGroup $group;
    private Worker $worker1;
    private Worker $worker2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->worker1 = Worker::create([
            'last_name' => 'Иванов',
            'first_name' => 'Иван',
            'patronymic' => 'Иванович',
            'age' => 30,
            'experience' => 10,
            'gender' => 0,
        ]);

        $this->worker2 = Worker::create([
            'last_name' => 'Петров',
            'first_name' => 'Пётр',
            'patronymic' => 'Петрович',
            'age' => 35,
            'experience' => 15,
            'gender' => 0,
        ]);

        $this->group = WorkGroup::create(['name' => 'Тестовая бригада']);
        $this->group->workers()->attach([$this->worker1->id, $this->worker2->id]);

        GroupProductivity::create([
            'work_group_id' => $this->group->id,
            'worker_id' => $this->worker1->id,
            'volume' => 500,
            'time' => 8,
            'value' => 62.50,
        ]);

        GroupProductivity::create([
            'work_group_id' => $this->group->id,
            'worker_id' => $this->worker2->id,
            'volume' => 300,
            'time' => 6,
            'value' => 50.00,
        ]);
    }

    #[Test]
    public function it_calculates_individual_productivity_correctly(): void
    {
        $solver = new ProductivitySolver($this->group);
        $results = $solver->calculateProductivity();

        $this->assertCount(2, $results);
        $this->assertEqualsWithDelta(62.50, $results[0], 0.01);
        $this->assertEqualsWithDelta(50.00, $results[1], 0.01);
    }

    #[Test]
    public function it_calculates_total_productivity_correctly(): void
    {
        $solver = new ProductivitySolver($this->group);
        $solver->calculateProductivity();
        $total = $solver->getTotalProductivity();

        $this->assertEqualsWithDelta(112.50, $total, 0.01);
    }

    #[Test]
    public function it_handles_zero_time_correctly(): void
    {
        GroupProductivity::where('work_group_id', $this->group->id)
            ->where('worker_id', $this->worker1->id)
            ->update(['time' => 0, 'value' => 0]);

        $solver = new ProductivitySolver($this->group);
        $results = $solver->calculateProductivity();

        $this->assertEqualsWithDelta(0, $results[0], 0.01);
    }

    #[Test]
    public function it_handles_empty_data_correctly(): void
    {
        $emptyGroup = WorkGroup::create(['name' => 'Пустая группа']);
        $worker = Worker::create([
            'last_name' => 'Сидоров',
            'first_name' => 'Сидор',
            'patronymic' => null,
            'age' => 25,
            'experience' => 5,
            'gender' => 1,
        ]);
        $emptyGroup->workers()->attach($worker->id);

        $solver = new ProductivitySolver($emptyGroup);
        $results = $solver->calculateProductivity();

        $this->assertCount(1, $results);
        $this->assertEqualsWithDelta(0, $results[0], 0.01);
    }
}