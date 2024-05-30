<?php

namespace Portable\FilaCms\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Portable\FilaCms\Tests\TestCase;

class MakeContentMigrationTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function test_creates_migration(): void
    {
        $timestamp = date('Y_m_d_His');
        $this->artisan('make:filacms-content-migration', ['name' => 'MyContent']);
        $this->assertFileExists(database_path('migrations/' . $timestamp . '_create_my_contents_table.php'));
        unlink(database_path('migrations/' . $timestamp . '_create_my_contents_table.php'));
    }

    public function test_creates_migration_no_argument(): void
    {
        $this->artisan('make:filacms-content-migration')
            ->expectsQuestion('What is the model name?', '')
            ->assertExitCode(1);
    }
}
