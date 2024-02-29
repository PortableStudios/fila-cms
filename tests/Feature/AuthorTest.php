<?php

namespace Portable\FilaCms\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Portable\FilaCms\Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;

use Portable\FilaCms\Models\Author;

use Schema;

class AuthorTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    public function test_table_exists(): void
    {
        $this->assertTrue(Schema::hasTable('authors'));
    }

    public function test_can_add_author(): void
    {
        $author = Author::create([
            'first_name'    => 'Jeremy',
            'last_name'     => 'Layson',
            'is_individual' => 1,
        ]);

        $this->assertModelExists($author);
        $this->assertEquals($author->display_name, 'Jeremy Layson');

        // test edit
        $author = Author::orderBy('id', 'DESC')->limit(1)->first();
        $author->update(['first_name' => 'John']);

        // test non-individual
        $author = Author::create([
            'first_name'    => 'Portable',
            'last_name'     => 'SHOULD NOT SHOW',
            'is_individual' => 0,
        ]);

        $this->assertModelExists($author);
        $this->assertEquals($author->display_name, 'Portable');

        $this->assertDatabaseHas('authors', [ 'first_name' => 'John', 'last_name' => 'Layson' ]);
        $this->assertDatabaseMissing('authors', [ 'first_name' => 'Jeremy', 'last_name' => 'Layson' ]);
    }
}
