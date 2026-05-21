<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\App;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Создаём demo-пользователей ТОЛЬКО в локальной среде
        if (App::environment('local', 'testing')) {
            $this->createDemoUsers();
            $this->command->info('✓ Demo users created (local/testing environment)');
        } else {
            $this->command->info('ℹ Skipping demo users in production environment');
        }
    }
    
    private function createDemoUsers(): void
    {
        $demoUsers = [
            [
                'name' => 'Demo Admin',
                'email' => 'demo@example.com',
                'password' => Hash::make('demo123456'),
            ],
            [
                'name' => 'Demo User',
                'email' => 'user@example.com',
                'password' => Hash::make('user123456'),
            ],
        ];
        
        foreach ($demoUsers as $userData) {
            User::firstOrCreate(
                ['email' => $userData['email']],
                $userData
            );
        }
    }
}