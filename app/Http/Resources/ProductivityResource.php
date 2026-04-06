<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductivityResource extends JsonResource
{
    protected $group;
    protected $solver;
    
    public function __construct($group, $solver)
    {
        parent::__construct($group);
        $this->group = $group;
        $this->solver = $solver;
    }
    
    public function toArray(Request $request): array
    {
        $decisions = $this->solver->getDecisions();
        $metrics = $this->solver->getMetrics();
        $results = [];
        
        foreach ($this->group->workers as $i => $worker) {
            $results[] = [
                'id' => $worker->id,
                'name' => $worker->full_name,
                'productivity' => round($decisions[$i] ?? 0, 2)
            ];
        }
        
        return [
            'success' => true,
            'group' => [
                'id' => $this->group->id,
                'name' => $this->group->name,
                'workers_count' => $this->group->workers->count()
            ],
            'results' => $results,
            'metrics' => $metrics
        ];
    }
}