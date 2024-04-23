<?php

namespace Portable\FilaCms\Tests\Filament;

use Illuminate\Foundation\Testing\WithFaker;
use Livewire\Livewire;
use Portable\FilaCms\Filament\Resources\NavigationResource as TargetResource;
use Portable\FilaCms\Models\Navigation as TargetModel;
use Portable\FilaCms\Models\Page;
use Portable\FilaCms\Tests\TestCase;
use Spatie\Permission\Models\Role;

class NavigationResourceTest extends TestCase
{
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--class' => '\\Portable\\FilaCms\\Database\\Seeders\\RoleAndPermissionSeeder']);
        $adminRole = Role::where('name', 'Admin')->first();

        $userModel = config('auth.providers.users.model');

        $adminUser = (new $userModel())->create([
            'name' => 'Test',
            'email' => 'test@example.com',
            'password' => 'password'
        ]);
        $adminUser->assignRole($adminRole);

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

        Livewire::test(TargetResource\Pages\ListNavigations::class)->assertCanSeeTableRecords($data);
    }

    public function test_can_create_record(): void
    {
        Livewire::test(TargetResource\Pages\CreateNavigation::class)
            ->fillForm([
                'name'      => fake()->words(mt_rand(2, 10), true),
                'type' => 'url',
                'reference_text' => fake()->url()
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        Livewire::test(TargetResource\Pages\CreateNavigation::class)
            ->fillForm([
                'name' => '',
                'type' => 'url',
                'reference_text' => 'asdada',
            ])
            ->call('create')
            ->assertHasFormErrors([
                'name' => 'required',
            ]);
    }

    public function test_can_render_edit_page(): void
    {
        $this->generateModel();

        $data = TargetModel::first();

        $this->get(TargetResource::getUrl('edit', ['record' => $data]))->assertSuccessful();
    }

    public function test_can_retrieve_edit_data(): void
    {
        $this->generateModel();
        $data = TargetModel::first();

        Livewire::test(
            TargetResource\Pages\EditNavigation::class,
            ['record' => $data->getRouteKey()]
        )
            ->assertFormSet([
                'name'  => $data->name,
                'type'  => $data->type,
            ]);
    }

    public function test_can_save_form(): void
    {
        $data = $this->generateModel();

        $updatedTime = now();
        Livewire::test(TargetResource\Pages\EditNavigation::class, [
            'record' => $data->getRoutekey(),
        ])
        ->fillForm([
            'name'  => fake()->words(3, true),
        ])
        ->call('save')
        ->assertHasNoFormErrors();
    }

    public function generateModel(): TargetModel
    {
        $type = fake()->randomElement(['page', 'content', 'url']);
        Page::factory()->count(5)->create();

        $reference = [
            'page' => [
                'source' => 'http://localhost:8000//pages',
                'resource' => 'Portable\\FilaCms\\Filament\\Resources\\PageResource',
            ],
            'content' => [
                'resource'  => 'Portable\\FilaCms\\Filament\\Resources\\PageResource',
                'model'     => 'Portable\\FilaCms\\Models\\Page',
                'id'        => Page::inRandomOrder()->first()->id,
            ],
            'url' => [
                'url' => fake()->url(),
            ]
        ];

        return TargetModel::create([
            'name'      => fake()->words(mt_rand(2, 10), true),
            'type' => $type,
            'reference' => $reference[$type],
        ]);
    }
}
