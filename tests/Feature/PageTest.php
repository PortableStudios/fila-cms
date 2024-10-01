<?php

namespace Portable\FilaCms\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Portable\FilaCms\Filament\Actions\CloneAction;
use Portable\FilaCms\Filament\Resources\PageResource\Pages\ListPages;
use Portable\FilaCms\Models\Author;
use Portable\FilaCms\Models\Page;
use Portable\FilaCms\Tests\TestCase;
use Spatie\Permission\Models\Role;

class PageTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    protected $userModel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userModel = config('auth.providers.users.model');
        $user = $this->userModel::create([
            'name'  => 'Jeremy Layson',
            'email' => 'jeremy.layson@portable.com.au',
            'password'  => 'password'
        ]);
        $adminRole = Role::where('name', 'Admin')->first();
        $user->assignRole($adminRole);

        $this->actingAs($user);

        Author::create([
            'first_name'    => 'Portable',
            'is_individual' => 0
        ]);
    }
    public function test_table_exists(): void
    {
        $this->assertTrue(Schema::hasTable('pages'));
    }

    public function test_can_add_page(): void
    {
        $user = $this->userModel::first();

        $secondUser = $this->userModel::create([
            'name'  => 'John Santos',
            'email' => 'johnsantos@portable.com.au',
            'password'  => 'password'
        ]);

        $page = Page::factory()->create();

        $this->assertModelExists($page);
        $this->assertDatabaseHas('pages', [ 'slug' => Str::slug($page->title) ]);
        $this->assertEquals($page->created_user_id, $user->id);

        $this->be($secondUser);
        $page->title = 'Edit';
        $page->save();
        $page = $page->fresh();

        $this->assertEquals($page->updated_user_id, $secondUser->id);
    }

    public function test_custom_slug(): void
    {
        $author = Author::first();
        $user = $this->userModel::first();

        $page = Page::factory()->create([
            'slug' => 'test-slug',
        ]);

        $this->assertDatabaseHas('pages', [ 'slug' => 'test-slug' ]);
        $this->assertDatabaseMissing('pages', [ 'slug' => Str::slug($page->title) ]);
    }

    public function test_draft_status(): void
    {
        $author = Author::first();
        $user = $this->userModel::first();

        $page = Page::factory()->create([
            'is_draft' => 1,
        ]);

        $this->assertEquals($page->status, 'Draft');
    }

    public function test_published_status(): void
    {
        $author = Author::first();
        $user = $this->userModel::first();

        $page = Page::factory()->create([
            'is_draft' => 0,
            'publish_at' => $this->faker->dateTimeBetween('-1 week', '-1 day'),
            'expire_at' => $this->faker->dateTimeBetween('+1 day', '+1 week'),
        ]);

        $this->assertEquals($page->status, 'Published');
    }

    public function test_clone(): void
    {
        $user = $this->userModel::first();
        $user->assignRole('Admin');
        $this->actingAs($user);
        $page = Page::factory()->create([
            'is_draft' => 0,
            'publish_at' => $this->faker->dateTimeBetween('-1 week', '-1 day'),
            'expire_at' => $this->faker->dateTimeBetween('+1 day', '+1 week'),
        ]);
        Livewire::test(ListPages::class)->callTableAction(CloneAction::class, $page->id);

        $this->assertDatabaseHas('pages', [ 'title' => '[CLONE] ' . $page->title ]);
        $this->assertDatabaseHas('pages', [ 'slug' => $page->slug . '-1' ]);
    }

    public function test_expired_status(): void
    {
        $author = Author::first();
        $user = $this->userModel::first();

        $page = Page::factory()->create([
            'is_draft' => 0,
            'publish_at' => $this->faker->dateTimeBetween('-1 week', '-1 day'),
            'expire_at' => $this->faker->dateTimeBetween('-1 week', '-1 day'),
        ]);

        $this->assertEquals($page->status, 'Expired');
    }

    public function test_pending_status(): void
    {
        $author = Author::first();
        $user = $this->userModel::first();

        $page = Page::factory()->create([
            'is_draft' => 0,
            'publish_at' => $this->faker->dateTimeBetween('+1 day', '+1 week'),
            'expire_at' => $this->faker->dateTimeBetween('+1 day', '+1 week'),
        ]);

        $this->assertEquals($page->status, 'Pending');
    }
}
