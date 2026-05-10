<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Services\ImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class ImportExportTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::create([
            'name' => 'Тестовый Пользователь',
            'email' => 'test@example.com',
            'password' => bcrypt('Test123!@#'),
        ]);
        $this->actingAs($this->user);
    }

    #[Test]
    public function valid_file_is_imported_correctly(): void
    {
        $content = "Иванов;Иван;Иванович;30;10;0\nПетров;Пётр;Петрович;35;15;0\n";

        $importService = app(ImportService::class);
        $result = $importService->importFromContent($content);

        $this->assertEquals(2, $result['added']);
        $this->assertEquals(0, $result['duplicates']);
        $this->assertCount(0, $result['errors']);
    }

    #[Test]
    public function duplicates_are_skipped(): void
    {
        $content = "Иванов;Иван;Иванович;30;10;0\n";
        $importService = app(ImportService::class);
        $result = $importService->importFromContent($content);
        $this->assertEquals(1, $result['added']);

        $result = $importService->importFromContent($content);
        $this->assertEquals(0, $result['added']);
        $this->assertEquals(1, $result['duplicates']);
    }

    #[Test]
    public function invalid_lines_are_reported(): void
    {
        $content = "Иванов;Иван;Иванович;30;10;0\nНекорректная;Строка;Без;Возраста\n";

        $importService = app(ImportService::class);
        $result = $importService->importFromContent($content);

        $this->assertEquals(1, $result['added']);
        $this->assertGreaterThan(0, count($result['errors']));
    }

    #[Test]
    public function export_returns_valid_file(): void
    {
        $response = $this->get('/workers/export');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/plain; charset=UTF-8');
    }

    #[Test]
    public function windows_1251_file_is_imported_correctly(): void
    {
        $content = mb_convert_encoding("Иванов;Иван;Иванович;30;10;0\n", 'Windows-1251', 'UTF-8');

        $encoding = mb_detect_encoding($content, ['UTF-8', 'Windows-1251'], true);
        if ($encoding !== 'UTF-8') {
            $content = mb_convert_encoding($content, 'UTF-8', $encoding);
        }

        $importService = app(ImportService::class);
        $result = $importService->importFromContent($content);

        $this->assertEquals(1, $result['added']);
    }
}