<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function registration_creates_user_and_redirects(): void
    {
        $response = $this->post('/register', [
            'name' => 'Новый Пользователь',
            'email' => 'new@example.com',
            'password' => 'Secret123!@#',
            'password_confirmation' => 'Secret123!@#',
        ]);

        $response->assertRedirect('/');
        $this->assertDatabaseHas('users', ['email' => 'new@example.com']);
    }

    #[Test]
    public function login_with_valid_credentials_redirects_to_home(): void
    {
        User::create([
            'name' => 'Тестовый',
            'email' => 'login@example.com',
            'password' => bcrypt('Secret123!@#'),
        ]);

        $response = $this->post('/login', [
            'email' => 'login@example.com',
            'password' => 'Secret123!@#',
        ]);

        $response->assertRedirect('/');
        $this->assertAuthenticated();
    }

    #[Test]
    public function login_with_invalid_credentials_fails(): void
    {
        User::create([
            'name' => 'Тестовый',
            'email' => 'fail@example.com',
            'password' => bcrypt('Secret123!@#'),
        ]);

        $response = $this->post('/login', [
            'email' => 'fail@example.com',
            'password' => 'WrongPassword',
        ]);

        $response->assertStatus(302);
        $this->assertGuest();
    }

    #[Test]
    public function api_returns_404_for_nonexistent_group(): void
    {
        $response = $this->get('/api/v1/calculate/999');
        $response->assertStatus(404);
    }
}