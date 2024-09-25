<?php

namespace Portable\FilaCms\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Portable\FilaCms\Tests\TestCase;

class MakeContentResourceTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function test_creates_resources(): void
    {
        $this->artisan('make:filacms-content-resource', ['name' => 'MyContent']);
        $this->assertFileExists(app_path('Filament/Resources/MyContentResource.php'));
        $this->assertFileExists(app_path('Filament/Resources/MyContentResource/Pages/ListMyContents.php'));
        $this->assertFileExists(app_path('Filament/Resources/MyContentResource/Pages/EditMyContent.php'));
        $this->assertFileExists(app_path('Filament/Resources/MyContentResource/Pages/CreateMyContent.php'));
        $this->assertFileExists(app_path('Filament/Resources/MyContentResource/Pages/MyContentRevisions.php'));
    }

    public function test_creates_resource_no_argument(): void
    {
        $this->artisan('make:filacms-content-resource')
            ->expectsQuestion('What is the model name?', '')
            ->assertExitCode(1);
    }
}
