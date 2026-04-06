<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AlternativeResource extends JsonResource
{
    protected $group;
    protected $result;
    
    public function __construct($group, $result)
    {
        parent::__construct($group);
        $this->group = $group;
        $this->result = $result;
    }
    
    public function toArray(Request $request): array
    {
        $results = [];
        
        foreach ($this->group->workers as $i => $worker) {
            $results[] = [
                'id' => $worker->id,
                'name' => $worker->full_name,
                'productivity' => round($this->result['decisions'][$i] ?? 0, 2)
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
            'L' => $this->result['L'],
            'R' => $this->result['R']
        ];
    }
}