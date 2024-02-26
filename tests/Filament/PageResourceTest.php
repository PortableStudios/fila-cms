<?php

namespace Portable\FilaCms\Tests\Feature\Filament;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Portable\FilaCms\Filament\Resources\PageResource as TargetResource;
use App\Models\User;
use Portable\FilaCms\Models\Page as TargetModel;
use Spatie\Permission\Models\Role;
use Portable\FilaCms\Models\Author;
use Auth;
use Livewire\Livewire;
use Illuminate\Foundation\Testing\WithFaker;

class PageResourceTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $author = NULL;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--class' => '\\Portable\\FilaCms\\Database\\Seeders\\RoleAndPermissionSeeder']);
        $adminRole = Role::where('name', 'Admin')->first();
        $adminUser = User::factory()->create();
        $adminUser->assignRole($adminRole);

        $this->author = Author::create([
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'is_individual' => 1
        ]);

        $this->actingAs($adminUser);
    }

    public function test_render_page(): void
    {
        $this->get(TargetResource::getUrl('index'))->assertSuccessful();
    }

    public function test_forbidden(): void
    {
        $user = User::factory()->create();
        $this->be($user);
        $this->get(TargetResource::getUrl('index'))->assertForbidden();
    }

    public function test_can_list_data(): void
    {
        $data = [];
        for ($i=0; $i < 5; $i++) { 
            $data[] = $this->generateModel();
        }

        Livewire::test(TargetResource\Pages\ListPages::class)->assertCanSeeTableRecords($data);
    }

    public function test_can_create_record(): void
    {
        Livewire::test(TargetResource\Pages\CreatePage::class)
            ->fillForm($this->generateModel(TRUE))
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

        \Log::Info($data);

        Livewire::test(
            TargetResource\Pages\EditPage::class,
            ['record' => $data->getRouteKey()]
        )
        ->assertFormSet([
            'title'  => $data->title,
        ]);
    }

    public function test_can_save_form(): void
    {
        $data = $this->generateModel();

        $new = TargetModel::make($this->generateModel(TRUE));

        $updatedTime = now();
        Livewire::test(TargetResource\Pages\EditPage::class, [
            'record' => $data->getRoutekey(),
        ])
        ->fillForm([
            'title'  => $new->title,
            'contents'  => $new->contents,
            'is_draft'  => $new->is_draft,
            'author_id'  => $new->author_id,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

        $data->refresh();
        $this->assertEquals($data->title, $new->title);
        $this->assertEquals($data->author_id, $new->author_id);
        $this->assertEquals($data->is_draft, $new->is_draft);
        $this->assertEquals($data->updated_at->format('Y-m-d H:i'), $updatedTime->format('Y-m-d H:i'));
    }

    public function generateModel($raw = FALSE): TargetModel | Array
    {
        $draft = $this->faker->numberBetween(0, 1);

        $data = [
            'title'     => $this->faker->words(15, TRUE),
            'is_draft'  => $draft,
            'publish_at'    => $draft === 1 ? $this->faker->dateTimeBetween('-1 week', '+1 week') : NULL,
            'expire_at'    => $draft === 1 ? $this->faker->dateTimeBetween('-1 week', '+1 week') : NULL,
            'contents'  => $this->faker->words($this->faker->numberBetween(50, 150), TRUE),
            'author_Id' => $this->author->id,
        ];

        if ($raw) {
            return $data;
        }
        return TargetModel::create($data);
    }
}