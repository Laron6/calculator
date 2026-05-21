<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\UserDevice;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SessionTest extends TestCase
{
    use RefreshDatabase;

    public function test_terminate_device_deactivates_device()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        
        $sessionId = 'test_session_' . uniqid();
        
        $device = UserDevice::create([
            'user_id' => $user->id,
            'session_id' => $sessionId,
            'is_active' => true,
        ]);
        
        $response = $this->delete(route('devices.terminate', $device->id));
        
        $response->assertStatus(302);
        
        $device->refresh();
        $this->assertFalse($device->is_active, 'Чужое устройство должно быть деактивировано');
    }
    
    public function test_cannot_terminate_current_device()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        
        // Делаем запрос, чтобы сессия сохранилась в БД
        $this->get('/');
        
        // Получаем реальный session_id из БД
        $dbSession = DB::table('sessions')->where('user_id', $user->id)->first();
        $realSessionId = $dbSession->id;
        
        $device = UserDevice::create([
            'user_id' => $user->id,
            'session_id' => $realSessionId,
            'is_active' => true,
        ]);
        
        $response = $this->delete(route('devices.terminate', $device->id));
        
        $response->assertStatus(302);
        
        $device->refresh();
        $this->assertTrue($device->is_active, 'Текущее устройство не должно быть деактивировано');
    }
    
    public function test_terminate_other_devices()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        
        // Делаем запрос, чтобы текущая сессия сохранилась в БД
        $this->get('/');
        
        // Получаем реальный session_id текущей сессии
        $currentDbSession = DB::table('sessions')->where('user_id', $user->id)->first();
        $currentRealSessionId = $currentDbSession->id;
        
        // Текущее устройство
        $currentDevice = UserDevice::create([
            'user_id' => $user->id,
            'session_id' => $currentRealSessionId,
            'is_active' => true,
        ]);
        
        // Чужое устройство
        $otherDevice = UserDevice::create([
            'user_id' => $user->id,
            'session_id' => 'other_session_' . uniqid(),
            'is_active' => true,
        ]);
        
        $response = $this->post(route('devices.terminate-others'));
        
        $response->assertStatus(302);
        
        $otherDevice->refresh();
        $this->assertFalse($otherDevice->is_active, 'Чужое устройство должно быть деактивировано');
        
        $currentDevice->refresh();
        $this->assertTrue($currentDevice->is_active, 'Текущее устройство должно остаться активным');
    }
    
    public function test_logout_deactivates_device()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        
        // Делаем запрос, чтобы сессия сохранилась в БД
        $this->get('/');
        
        // Получаем реальный session_id из БД
        $dbSession = DB::table('sessions')->where('user_id', $user->id)->first();
        $realSessionId = $dbSession->id;
        
        $device = UserDevice::create([
            'user_id' => $user->id,
            'session_id' => $realSessionId,
            'is_active' => true,
        ]);
        
        $response = $this->post(route('logout'));
        
        $response->assertRedirect('/');
        
        $device->refresh();
        $this->assertFalse($device->is_active, 'Устройство должно быть деактивировано при выходе');
    }
}