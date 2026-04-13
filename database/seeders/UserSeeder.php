<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserDevice;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Очищаем таблицы, связанные с сессиями и устройствами
        DB::table('user_devices')->truncate();
        DB::table('sessions')->truncate();
        
        // Очищаем таблицу пользователей
        User::truncate();
        
        // Создаём тестовых пользователей
        $users = [
            [
                'name' => 'Администратор',
                'email' => 'admin@example.com',
                'password' => 'Admin123!@#',
            ],
            [
                'name' => 'Иван Иванов',
                'email' => 'ivan@example.com',
                'password' => 'Ivan123!@#',
            ],
            [
                'name' => 'Мария Петрова',
                'email' => 'maria@example.com',
                'password' => 'Maria123!@#',
            ],
        ];
        
        foreach ($users as $userData) {
            User::create([
                'name' => $userData['name'],
                'email' => $userData['email'],
                'password' => Hash::make($userData['password']),
            ]);
        }
        
        $this->command->info('Пользователи созданы:');
        $this->command->info('admin@example.com / Admin123!@#');
        $this->command->info('ivan@example.com / Ivan123!@#');
        $this->command->info('maria@example.com / Maria123!@#');
    }
}