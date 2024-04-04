<?php

namespace Portable\FilaCms\Tests\Filament;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Livewire\Livewire;
use Portable\FilaCms\Facades\FilaCms;
use Portable\FilaCms\Filament\Resources\PageResource as TargetResource;
use Portable\FilaCms\Models\Author;
use Portable\FilaCms\Models\Page as TargetModel;
use Portable\FilaCms\Models\Taxonomy;
use Portable\FilaCms\Models\TaxonomyTerm;
use Portable\FilaCms\Tests\TestCase;
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
        $updatedTime = now();

        $data->refresh();
        $this->assertEquals($data->title, $new->title);
        $this->assertEquals($data->author_id, $new->author_id);
        $this->assertEquals($data->is_draft, $new->is_draft);
        $this->assertEquals($data->updated_at->format('Y-m-d H:i'), $updatedTime->format('Y-m-d H:i'));
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
                'contents' => 'Test Page Contents',
                'colours_ids' => [$red->id],
            ])
            ->call('create')
            ->assertHasNoErrors();

        $page = TargetModel::where('title', 'Test Page')->first();

        $this->assertNotNull($page);
        $this->assertEquals($page->colours_ids->toArray(), [$red->id]);
        $this->assertEquals($page->colours->pluck('name')->toArray(), [$red->name]);
    }

    public function generateModel($raw = false): TargetModel|array
    {
        $draft = $this->faker->numberBetween(0, 1);

        $data = [
            'title' => $this->faker->words(15, true),
            'is_draft' => $draft,
            'publish_at' => $draft === 1 ? $this->faker->dateTimeBetween('-1 week', '+1 week') : null,
            'expire_at' => $draft === 1 ? $this->faker->dateTimeBetween('-1 week', '+1 week') : null,
            'contents' => json_decode('{"type": "doc", "content": [{"type": "paragraph", "attrs": {"class": null, "style": null, "textAlign": "start"}, "content": [{"text": "Lorem ipsum keme keme keme 48 years chaka biway chapter ano kemerloo at nang ng shontis at klapeypey-klapeypey katagalugan katagalugan nang sa at na ang jowabella buya daki nang makyonget biway chaka shongaers lorem ipsum keme keme chipipay intonses shontis at nang at bakit bella kasi lulu shonga-shonga lorem ipsum keme keme buya ano jutay ma-kyonget wasok otoko at bakit juts kirara at nang na ang shogal bella na ang kabog majubis jowabella at kabog bakit otoko at ang na ang 48 years at ang nakakalurky pranella shonga chopopo ng at chuckie na at nang bella kasi chopopo nang valaj ng shokot chipipay kasi tungril guash sangkatuts lulu ano majonders ganda lang kabog sa warla sa jutay biway at bakit matod majubis sa bonggakea jowabella oblation wiz shonga-shonga.", "type": "text"}]}]}', true),
            'author_Id' => $this->author->id,
        ];

        if ($raw) {
            return $data;
        }

        return TargetModel::create($data);
    }
}
