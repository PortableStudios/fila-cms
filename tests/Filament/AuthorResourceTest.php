<?php

namespace Portable\FilaCms\Tests\Feature\Filament;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Portable\FilaCms\Filament\Resources\AuthorResource as TargetResource;
use App\Models\User;
use Portable\FilaCms\Models\Author as TargetModel;
use Spatie\Permission\Models\Role;
use Auth;
use Livewire\Livewire;
use Illuminate\Foundation\Testing\WithFaker;

class AuthorResourceTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--class' => '\\Portable\\FilaCms\\Database\\Seeders\\RoleAndPermissionSeeder']);
        $adminRole = Role::where('name', 'Admin')->first();
        $adminUser = User::factory()->create();
        $adminUser->assignRole($adminRole);

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

        Livewire::test(TargetResource\Pages\ListAuthors::class)->assertCanSeeTableRecords($data);
    }

    public function test_can_create_record(): void
    {
        Livewire::test(TargetResource\Pages\CreateAuthor::class)
            ->fillForm([
                'first_name' => $this->faker->firstName,
                'last_name' => $this->faker->lastName,
                'is_individual' => $this->faker->numberBetween(0, 1)
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        Livewire::test(TargetResource\Pages\CreateAuthor::class)
            ->fillForm([
                'first_name' => '',
                'last_name' => '',
            ])
            ->call('create')
            ->assertHasFormErrors([
                'first_name' => 'required',
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
                TargetResource\Pages\EditAuthor::class,
                ['record' => $data->getRouteKey()]
            )
            ->assertFormSet([
                'first_name'  => $data->first_name,
                'last_name'  => $data->last_name,
                'is_individual'  => $data->is_individual,
            ]);
    }

    public function test_can_save_form(): void
    {
        $data = $this->generateModel();

        $new = TargetModel::make([
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'is_individual' => $this->faker->numberBetween(0, 1)
        ]);

        $updatedTime = now();
        Livewire::test(TargetResource\Pages\EditAuthor::class, [
            'record' => $data->getRoutekey(),
        ])
        ->fillForm([
            'first_name'  => $new->first_name,
            'last_name'  => $new->last_name,
            'is_individual'  => $new->is_individual,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

        $data->refresh();
        $this->assertEquals($data->first_name, $new->first_name);
        $this->assertEquals($data->is_individual, $new->is_individual);
        $this->assertEquals($data->updated_at->format('Y-m-d H:i'), $updatedTime->format('Y-m-d H:i'));
    }

    public function test_display_name_individual_to_company(): void
    {
        $data = TargetModel::create([
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'is_individual' => 1
        ]);

        $new = TargetModel::make([
            'first_name' => $this->faker->firstName,
            'last_name' => '',
            'is_individual' => 0
        ]);

        $updatedTime = now();
        Livewire::test(TargetResource\Pages\EditAuthor::class, [
            'record' => $data->getRoutekey(),
        ])
        ->fillForm([
            'first_name'  => $new->first_name,
            'last_name'  => $new->last_name,
            'is_individual'  => $new->is_individual,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

        $data->refresh();
        $this->assertEquals($data->display_name, $new->first_name);
        $this->assertEquals($data->is_individual, $new->is_individual);
        $this->assertEquals($data->updated_at->format('Y-m-d H:i'), $updatedTime->format('Y-m-d H:i'));
    }

    public function test_display_name_company_to_individual(): void
    {
        $data = TargetModel::create([
            'first_name' => $this->faker->firstName,
            'last_name' => '',
            'is_individual' => 0,
        ]);

        $new = TargetModel::make([
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'is_individual' => 1
        ]);

        $updatedTime = now();
        Livewire::test(TargetResource\Pages\EditAuthor::class, [
            'record' => $data->getRoutekey(),
        ])
        ->fillForm([
            'first_name'  => $new->first_name,
            'last_name'  => $new->last_name,
            'is_individual'  => $new->is_individual,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

        $data->refresh();
        $this->assertEquals($data->display_name, $new->first_name . ' ' . $new->last_name);
        $this->assertEquals($data->is_individual, $new->is_individual);
        $this->assertEquals($data->updated_at->format('Y-m-d H:i'), $updatedTime->format('Y-m-d H:i'));
    }

    public function generateModel(): TargetModel
    {
        return TargetModel::create([
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'is_individual' => $this->faker->numberBetween(0, 1)
        ]);
    }
}