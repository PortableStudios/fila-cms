<?php

namespace Portable\FilaCms\Tests\Filament;

use Portable\FilaCms\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Portable\FilaCms\Filament\Resources\TaxonomyResource as TargetResource;
use Portable\FilaCms\Tests\User;
use Portable\FilaCms\Models\Taxonomy as TargetModel;
use Spatie\Permission\Models\Role;
use Livewire\Livewire;
use Illuminate\Foundation\Testing\WithFaker;

class TaxonomyResourceTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--class' => '\\Portable\\FilaCms\\Database\\Seeders\\RoleAndPermissionSeeder']);
        $adminRole = Role::where('name', 'Admin')->first();
        $adminUser = $this->createUser();
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

        Livewire::test(TargetResource\Pages\ListTaxonomies::class)->assertCanSeeTableRecords($data);
    }

    public function test_can_create_record(): void
    {
        Livewire::test(TargetResource\Pages\CreateTaxonomy::class)
            ->fillForm([
                'name' => $this->faker->firstName,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        Livewire::test(TargetResource\Pages\CreateTaxonomy::class)
            ->fillForm([
                'name' => '',
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
            TargetResource\Pages\EditTaxonomy::class,
            ['record' => $data->getRouteKey()]
        )
            ->assertFormSet([
                'name'  => $data->name,
            ]);
    }

    public function test_can_save_form(): void
    {
        $data = $this->generateModel();

        $new = TargetModel::make([
            'name' => $this->faker->firstName,
        ]);

        Livewire::test(TargetResource\Pages\EditTaxonomy::class, [
            'record' => $data->getRoutekey(),
        ])
        ->fillForm([
            'name'  => $new->name,
        ])
        ->call('save')
        ->assertHasNoFormErrors();
        $updatedTime = now();

        $data->refresh();
        $this->assertEquals($data->name, $new->name);
        $this->assertEquals($data->updated_at->format('Y-m-d H:i'), $updatedTime->format('Y-m-d H:i'));
    }

    public function generateModel(): TargetModel
    {
        return TargetModel::create([
            'name' => $this->faker->firstName,
        ]);
    }
}
