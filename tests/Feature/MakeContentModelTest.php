<?php

namespace Portable\FilaCms\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Portable\FilaCms\Tests\TestCase;

class MakeContentModelTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function test_creates_model(): void
    {
        $timestamp = date('Y_m_d_His');
        $this->artisan('make:filacms-content-model', ['name' => 'MyContent']);
        $this->assertFileExists(app_path('Models/MyContent.php'));
    }

    public function test_creates_model_no_argument(): void
    {
        $this->artisan('make:filacms-content-model')
             ->expectsQuestion('What is the model name?', '')
             ->assertExitCode(1);
    }
}
