<?php

namespace Portable\FilaCms\Tests\Filament;


use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Portable\FilaCms\Facades\FilaCms;
use Portable\FilaCms\Filament\Resources\PageResource as TargetResource;
use Portable\FilaCms\Filament\Resources\PageResource;
use Portable\FilaCms\Filament\Resources\PageResource\Pages\CreatePage;
use Portable\FilaCms\Filament\Resources\PageResource\Pages\EditPage;
use Portable\FilaCms\Filament\Resources\PageResource\Pages\ListPages;
use Portable\FilaCms\Models\Author;
use Portable\FilaCms\Models\Page;
use Portable\FilaCms\Models\Taxonomy;
use Portable\FilaCms\Models\TaxonomyTerm;
use Portable\FilaCms\Tests\TestCase;
use RalphJSmit\Laravel\SEO\Models\SEO;
use Spatie\Permission\Models\Role;

class PageResourceTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected $author = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--class' => '\\Portable\\FilaCms\\Database\\Seeders\\RoleAndPermissionSeeder']);
        $adminRole = Role::where('name', 'Admin')->first();
        $adminUser = $this->createUser();
        $adminUser->assignRole($adminRole);

        $this->author = Author::create([
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'is_individual' => 1,
        ]);

        $this->actingAs($adminUser);
    }

    public function test_render_page(): void
    {
        $this->get(TargetResource::getUrl('index'))->assertSuccessful();
    }

    public function test_forbidden(): void
    {
        $user = $this->createUser();
        $this->be($user);
        $this->get(TargetResource::getUrl('index'))->assertForbidden();
    }

    public function test_can_list_data(): void
    {
        $data = Page::factory()
            ->count(5)
            ->create();

        Livewire::test(ListPages::class)->assertCanSeeTableRecords($data);
    }

    public function test_can_create_record(): void
    {
        Livewire::test(CreatePage::class)
            ->fillForm(Page::factory()->make()->toArray())
            ->call('create')
            ->assertHasNoFormErrors();

        Livewire::test(CreatePage::class)
            ->fillForm([])
            ->call('create')
            ->assertHasFormErrors([
                'title' => 'required',
                'contents' => 'required',
            ]);
    }

    public function test_can_save_seo(): void
    {
        $data = Page::factory()->make();
        $data['seo.override_seo_description'] = true;
        $data['seo.description'] = 'Test Description';
        $data['is_draft'] = 0;
        $data['publish_at'] = now()->subday();
        $data['expire_at'] = now()->addDay();

        Livewire::test(TargetResource\Pages\CreatePage::class)
            ->fillForm($data->toArray())
            ->call('create')
            ->assertHasNoFormErrors();

        // check last record
        $model = Page::orderBy('id', 'desc')->first();

        $this->assertTrue($model->Seo instanceof SEO);
        $this->assertEquals($model->Seo->description, 'Test Description');
    }

    public function test_can_generate_seo(): void
    {
        $data = Page::factory()->make();
        $data['is_draft'] = 0;
        $data['publish_at'] = now()->subday();
        $data['expire_at'] = now()->addDay();

        Livewire::test(TargetResource\Pages\CreatePage::class)
            ->fillForm($data->toArray())
            ->call('create')
            ->assertHasNoFormErrors();

        // check last record
        $model = Page::orderBy('id', 'desc')->first();

        $this->assertTrue($model->Seo instanceof SEO);
        $this->assertEquals($model->Seo->title, Str::limit($model->title, 57));
    }

    public function test_can_update_generated_seo(): void
    {
        $data = Page::factory()->make();
        $data['is_draft'] = 0;
        $data['publish_at'] = now()->subday();
        $data['expire_at'] = now()->addDay();

        Livewire::test(TargetResource\Pages\CreatePage::class)

            ->fillForm($data->toArray())
            ->call('create')
            ->assertHasNoFormErrors();

        // check last record
        $model = Page::orderBy('id', 'desc')->first();

        $data['title'] = 'An updated title';

        Livewire::test(TargetResource\Pages\EditPage::class, [
            'record' => $model->getRoutekey(),
        ])
        ->fillForm($data->toArray())
        ->call('save')
        ->assertHasNoFormErrors();

        // Reload model
        $model = Page::find($model->id);

        $this->assertTrue($model->Seo instanceof SEO);
        $this->assertEquals($model->Seo->title, $model->title);
    }

    public function test_can_render_edit_page(): void
    {
        $data = Page::factory()->create();

        $this->get(TargetResource::getUrl('edit', ['record' => $data]))->assertSuccessful();
    }

    public function test_can_retrieve_edit_data(): void
    {
        $data = Page::factory()->create();

        Livewire::test(
            EditPage::class,
            ['record' => $data->getRouteKey()]
        )
            ->assertFormSet([
                'title' => $data->title,
            ]);
    }

    public function test_can_save_form(): void
    {
        $data = Page::factory()->create();
        $colour = Taxonomy::create([
            'name' => 'Colour',
        ]);
        $rows = [];
        foreach (FilaCms::getContentModels() as $resource => $title) {
            $rows[] = ['resource_class' => $resource, 'taxonomy_id' => $colour->id];
        }
        $colour->resources()->createMany($rows);

        $red = TaxonomyTerm::create([
            'name' => 'Red',
            'taxonomy_id' => $colour->id,
        ]);

        $new = Page::factory()->make();

        Livewire::test(EditPage::class, [
            'record' => $data->getRoutekey(),
        ])
            ->fillForm([
                'title' => $new->title,
                'contents' => $new->contents,
                'is_draft' => $new->is_draft,
                'authors' => [$this->author->id],
                'colours_ids' => [$red->id],
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $data->refresh(['authors']);
        $this->assertEquals($data->title, $new->title);
        $this->assertEquals($data->authors->first()?->id, $this->author->id);
        $this->assertEquals($data->is_draft, $new->is_draft);
    }

    public function test_can_create_page_with_taxonomies(): void
    {
        $colour = Taxonomy::create([
            'name' => 'Colour',
        ]);
        $rows = [];
        foreach (FilaCms::getContentModels() as $resource => $title) {
            $rows[] = ['resource_class' => $resource, 'taxonomy_id' => $colour->id];
        }
        $colour->resources()->createMany($rows);

        $red = TaxonomyTerm::create([
            'name' => 'Red',
            'taxonomy_id' => $colour->id,
        ]);

        Livewire::test(TargetResource\Pages\CreatePage::class)
            ->fillForm([
                'is_draft' => 0,
                'title' => 'Test Page',
                'contents' => $this->createContent(),
                'colours_ids' => [$red->id],
            ])
            ->call('create')
            ->assertHasNoErrors();

        $page = Page::where('title', 'Test Page')->first();

        $this->assertNotNull($page);
        $this->assertEquals($page->colours_ids->toArray(), [$red->id]);
        $this->assertEquals($page->colours->pluck('name')->toArray(), [$red->name]);
    }

    public function test_automatic_slug(): void
    {
        Livewire::test(TargetResource\Pages\CreatePage::class)
            ->fillForm([
                'is_draft' => 0,
                'title' => 'Test Slug Title',
                'contents' => $this->createContent(),
            ])
            ->call('create')
            ->assertHasNoErrors();

        $page = Page::where('title', 'Test Slug Title')->first();

        $this->assertEquals($page->slug, 'test-slug-title');
    }

    public function test_automatic_slug_doesnt_change_on_update(): void
    {
        $data = Page::factory()->create();
        $oldSlug = $data->slug;

        Livewire::test(TargetResource\Pages\EditPage::class, [
            'record' => $data->getRoutekey(),
        ])
            ->fillForm([
                'contents' => $this->faker->words(100, true),
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $data->refresh();
        $this->assertEquals($data->slug, $oldSlug);
    }

    public function test_custom_slug(): void
    {
        Livewire::test(TargetResource\Pages\CreatePage::class)
            ->fillForm([
                'is_draft' => 0,
                'title' => 'Test Slug Title',
                'slug'  => 'custom-slug',
                'contents' => $this->createContent(),
            ])
            ->call('create')
            ->assertHasNoErrors();

        $page = Page::where('title', 'Test Slug Title')->first();

        $this->assertEquals($page->slug, 'custom-slug');
    }

    public function test_autoslug_increment(): void
    {
        Livewire::test(TargetResource\Pages\CreatePage::class)
            ->fillForm([
                'is_draft' => 0,
                'title' => 'Test Slug Title',
                'contents' => $this->createContent(),
            ])
            ->call('create')
            ->assertHasNoErrors();

        Livewire::test(TargetResource\Pages\CreatePage::class)
            ->fillForm([
                'is_draft' => 0,
                'title' => 'Test Slug Title',
                'contents' => $this->createContent(),
            ])
            ->call('create')
            ->assertHasNoErrors();

        $page = Page::where('title', 'Test Slug Title')->orderBy('id', 'desc')->first();

        $this->assertEquals($page->slug, 'test-slug-title-1');
    }

    public function test_duplicate_slug(): void
    {
        Page::create([
            'title' => $this->faker->words(15, true),
            'is_draft' => 1,
            'contents' => $this->createContent(),
            'slug'     => 'first-unique-slug',
        ]);

        Livewire::test(TargetResource\Pages\CreatePage::class)
            ->fillForm([
                'is_draft' => 1,
                'title' => 'Test Slug Title',
                'slug'  => 'first-unique-slug',
                'contents' => $this->createContent(),
            ])
            ->call('create')
            ->assertHasErrors();
    }

    public function test_can_add_vanity_urls(): void
    {
        $page = Livewire::test(TargetResource\Pages\CreatePage::class)
            ->set('data.shortUrls', [])
            ->fillForm([
                'is_draft' => 0,
                'title' => 'Test Page',
                'contents' => $this->createContent()
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $sampleUrl = Str::slug(Str::random(10));
        // check last record
        $model = Page::orderBy('id', 'desc')->first();
        $model->shortUrls()->create([
            'url' => $sampleUrl
        ]);
        $this->assertSame($sampleUrl, $model->shortUrls[0]->url);
    }

    public function test_add_roles(): void
    {
        Livewire::test(TargetResource\Pages\CreatePage::class)
            ->set('data.roleRestrictions.role_id', [\Spatie\Permission\Models\Role::first()->id])
            ->fillForm([
                'is_draft' => 0,
                'title' => 'Test Page',
                'contents' => $this->createContent()
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $model = Page::orderBy('id', 'desc')->first();

        $this->assertGreaterThan(0, $model->Roles->count());
    }

    public function test_check_role_access(): void
    {
        $adminRole = Role::where('name', 'Admin')->first();
        $userRole = Role::where('name', 'User')->first();

        $model = Page::factory()
            ->isPublished()
            ->create();

        $user = $this->createUser();

        $this->actingAs($user)->get(TargetResource::getUrl('edit', [
            'record' => $model
        ]))
        ->assertForbidden();

        $user->assignRole($adminRole);

        $this->get(TargetResource::getUrl('edit', [
            'record' => $model
        ]))->assertSuccessful();

        $user = $this->createUser();

        $user->assignRole($userRole);

        $this->actingAs($user)->get(TargetResource::getUrl('edit', [
            'record' => $model
        ]))->assertForbidden();

        Auth::logout();

        $prefix = Str::start(Str::finish(PageResource::getFrontendRoutePrefix(), '/'), '/');

        $this->get($prefix . $model->slug)->assertForbidden();

        $model->roles()->delete();
        $this->get($prefix . $model->slug)->assertSuccessful();
    }

    protected function createContent()
    {
        return [
            'type'  => 'doc',
            'content' => [
                [
                    'type' => 'paragraph',
                    'attrs' => [
                        'class' => null,
                        'style' => null,
                        'textAlign' => 'start',
                    ],
                    'content' => [
                        [
                            'text' => fake()->words(mt_rand(10, 50), true),
                            'type' => 'text'
                        ]
                    ]
                ]
            ]
        ];
    }
}
