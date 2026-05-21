<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Services\ImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;

class ImportExportTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    #[Test]
    public function valid_file_is_imported_correctly(): void
    {
        $content = "Иванов;Иван;Иванович;30;10;0\nПетров;Пётр;Петрович;35;15;0\n";

        $importService = app(ImportService::class);
        $result = $importService->importFromContent($content, $this->user->id);

        $this->assertEquals(2, $result['added']);
        $this->assertEquals(0, $result['duplicates']);
        $this->assertCount(0, $result['errors']);
        
        $this->assertDatabaseHas('workers', [
            'last_name' => 'Иванов',
            'first_name' => 'Иван',
            'user_id' => $this->user->id,
        ]);
        $this->assertDatabaseHas('workers', [
            'last_name' => 'Петров',
            'first_name' => 'Пётр',
            'user_id' => $this->user->id,
        ]);
    }

    #[Test]
    public function duplicates_are_skipped(): void
    {
        $content = "Иванов;Иван;Иванович;30;10;0\n";
        $importService = app(ImportService::class);
        $result = $importService->importFromContent($content, $this->user->id);
        $this->assertEquals(1, $result['added']);

        $result = $importService->importFromContent($content, $this->user->id);
        $this->assertEquals(0, $result['added']);
        $this->assertEquals(1, $result['duplicates']);
    }

    #[Test]
    public function invalid_lines_are_reported(): void
    {
        $content = "Иванов;Иван;Иванович;30;10;0\nНекорректная;Строка;Без;Возраста\n";

        $importService = app(ImportService::class);
        $result = $importService->importFromContent($content, $this->user->id);

        $this->assertEquals(1, $result['added']);
        $this->assertGreaterThan(0, count($result['errors']));
    }

    #[Test]
    public function export_returns_valid_file(): void
    {
        $this->post('/worker/add', [
            'last_name' => 'Тестов',
            'first_name' => 'Тест',
            'patronymic' => 'Тестович',
            'age' => 30,
            'experience' => 10,
            'gender' => 0,
        ]);
        
        $response = $this->get('/workers/export');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/plain; charset=UTF-8');
        
        $content = $response->getContent();
        $this->assertStringContainsString('Тестов;Тест;Тестович', $content);
    }

    #[Test]
    public function windows_1251_file_is_imported_correctly(): void
    {
        $content = mb_convert_encoding("Сидоров;Сидор;Сидорович;40;20;0\n", 'Windows-1251', 'UTF-8');

        $encoding = mb_detect_encoding($content, ['UTF-8', 'Windows-1251'], true);
        if ($encoding && $encoding !== 'UTF-8') {
            $content = mb_convert_encoding($content, 'UTF-8', $encoding);
        }

        $importService = app(ImportService::class);
        $result = $importService->importFromContent($content, $this->user->id);

        $this->assertEquals(1, $result['added']);
        
        $this->assertDatabaseHas('workers', [
            'last_name' => 'Сидоров',
            'first_name' => 'Сидор',
            'user_id' => $this->user->id,
        ]);
    }
    
    #[Test]
    public function http_import_with_file_upload_works(): void
    {
        Storage::fake('local');
        
        $content = "Захаров;Генадий;Анатольевич;42;19;0\n";
        
        // Создаём временный файл с правильным mime-типом
        $tempPath = tempnam(sys_get_temp_dir(), 'test_');
        file_put_contents($tempPath, $content);
        
        $file = new \Illuminate\Http\UploadedFile(
            $tempPath,
            'workers.lst',
            'text/plain',  // Явно указываем mime-тип
            null,
            true
        );
        
        $response = $this->post('/workers/import', ['file' => $file]);
        
        // Проверяем редирект
        $response->assertStatus(302);
        
        // Проверяем, что рабочий создался
        $this->assertDatabaseHas('workers', [
            'last_name' => 'Захаров',
            'first_name' => 'Генадий',
            'user_id' => $this->user->id,
        ]);
        
        // Очищаем временный файл
        unlink($tempPath);
    }
    
    #[Test]
    public function http_import_rejects_invalid_file_type(): void
    {
        $file = UploadedFile::fake()->create('document.pdf', 100);
        
        $response = $this->post('/workers/import', ['file' => $file]);
        
        $response->assertSessionHasErrors(['file']);
    }
}