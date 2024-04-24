<?php

namespace Portable\FilaCms\Tests\Filament;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Portable\FilaCms\Facades\FilaCms;
use Portable\FilaCms\Filament\Resources\PageResource as TargetResource;
use Portable\FilaCms\Models\Author;
use Portable\FilaCms\Models\Page as TargetModel;
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
        $data = [];
        for ($i = 0; $i < 5; $i++) {
            $data[] = $this->generateModel();
        }

        Livewire::test(TargetResource\Pages\ListPages::class)->assertCanSeeTableRecords($data);
    }

    public function test_can_create_record(): void
    {
        Livewire::test(TargetResource\Pages\CreatePage::class)
            ->fillForm($this->generateModel(true))
            ->call('create')
            ->assertHasNoFormErrors();

        Livewire::test(TargetResource\Pages\CreatePage::class)
            ->fillForm([])
            ->call('create')
            ->assertHasFormErrors([
                'title' => 'required',
                'contents' => 'required',
            ]);
    }

    public function test_can_save_seo(): void
    {
        $data = $this->generateModel(true);
        $data['seo.override_seo_description'] = true;
        $data['seo.description'] = 'Test Description';
        $data['is_draft'] = 0;
        $data['publish_at'] = now()->subday();
        $data['expire_at'] = now()->addDay();

        Livewire::test(TargetResource\Pages\CreatePage::class)
            ->fillForm($data)
            ->call('create')
            ->assertHasNoFormErrors();

        // check last record
        $model = TargetModel::orderBy('id', 'desc')->first();

        $this->assertTrue($model->Seo instanceof SEO);
        $this->assertEquals($model->Seo->description, 'Test Description');
    }

    public function test_can_generate_seo(): void
    {
        $data = $this->generateModel(true);
        $data['is_draft'] = 0;
        $data['publish_at'] = now()->subday();
        $data['expire_at'] = now()->addDay();

        Livewire::test(TargetResource\Pages\CreatePage::class)
            ->fillForm($data)
            ->call('create')
            ->assertHasNoFormErrors();

        // check last record
        $model = TargetModel::orderBy('id', 'desc')->first();

        $this->assertTrue($model->Seo instanceof SEO);
        $this->assertEquals($model->Seo->title, Str::limit($model->title, 57));
    }

    public function test_can_update_generated_seo(): void
    {
        $data = $this->generateModel(true);
        $data['is_draft'] = 0;
        $data['publish_at'] = now()->subday();
        $data['expire_at'] = now()->addDay();

        Livewire::test(TargetResource\Pages\CreatePage::class)

            ->fillForm($data)
            ->call('create')
            ->assertHasNoFormErrors();

        // check last record
        $model = TargetModel::orderBy('id', 'desc')->first();

        $data['title'] = 'An updated title';

        Livewire::test(TargetResource\Pages\EditPage::class, [
            'record' => $data->getRoutekey(),
        ])
        ->fillForm($data)
        ->call('save')
        ->assertHasNoFormErrors();

        // Reload model
        $model = TargetModel::find($model->id);

        $this->assertTrue($model->Seo instanceof SEO);
        $this->assertEquals($model->Seo->title, $model->title);
    }

    public function test_can_render_edit_page(): void
    {
        $data = $this->generateModel();

        $this->get(TargetResource::getUrl('edit', ['record' => $data]))->assertSuccessful();
    }

    public function test_can_retrieve_edit_data(): void
    {
        $data = $this->generateModel();

        Livewire::test(
            TargetResource\Pages\EditPage::class,
            ['record' => $data->getRouteKey()]
        )
            ->assertFormSet([
                'title' => $data->title,
            ]);
    }

    public function test_can_save_form(): void
    {
        $data = $this->generateModel();
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

        $new = TargetModel::make($this->generateModel(true));

        Livewire::test(TargetResource\Pages\EditPage::class, [
            'record' => $data->getRoutekey(),
        ])
            ->fillForm([
                'title' => $new->title,
                'contents' => $new->contents,
                'is_draft' => $new->is_draft,
                'author_id' => $new->author_id,
                'colours_ids' => [$red->id],
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $data->refresh();
        $this->assertEquals($data->title, $new->title);
        $this->assertEquals($data->author_id, $new->author_id);
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

        $page = TargetModel::where('title', 'Test Page')->first();

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

        $page = TargetModel::where('title', 'Test Slug Title')->first();

        $this->assertEquals($page->slug, 'test-slug-title');
    }

    public function test_automatic_slug_doesnt_change_on_update(): void
    {
        $data = TargetModel::factory()->create();
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

        $page = TargetModel::where('title', 'Test Slug Title')->first();

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

        $page = TargetModel::where('title', 'Test Slug Title')->orderBy('id', 'desc')->first();

        $this->assertEquals($page->slug, 'test-slug-title-1');
    }

    public function test_duplicate_slug(): void
    {
        TargetModel::create([
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

    public function generateModel($raw = false): TargetModel|array
    {
        $draft = $this->faker->numberBetween(0, 1);

        $data = [
            'title' => $this->faker->words(15, true),
            'is_draft' => $draft,
            'publish_at' => $draft === 1 ? $this->faker->dateTimeBetween('-1 week', '+1 week') : null,
            'expire_at' => $draft === 1 ? $this->faker->dateTimeBetween('-1 week', '+1 week') : null,
            'contents' => $this->createContent(),
            'author_Id' => $this->author->id,
        ];

        if ($raw) {
            return $data;
        }

        return TargetModel::create($data);
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
