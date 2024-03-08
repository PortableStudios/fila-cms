<?php

namespace Portable\FilaCms\Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Portable\FilaCms\FilaCms;
use Portable\FilaCms\Filament\Resources\PageResource;
use Portable\FilaCms\Models\Page;
use Portable\FilaCms\Tests\TestCase;

class FilaCmsTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected $author = null;

    public function test_gets_content_model()
    {
        $fila = new FilaCms();
        $this->assertEquals(PageResource::class, $fila->getContentModelResource(Page::class));
    }
}
