<?php

namespace Portable\FilaCms\Tests\Filament;

use Illuminate\Foundation\Testing\WithFaker;
use Livewire\Livewire;
use Portable\FilaCms\Filament\Resources\FormResource as TargetResource;
use Portable\FilaCms\Livewire\FormShow;
use Portable\FilaCms\Models\Form as TargetModel;
use Portable\FilaCms\Tests\TestCase;
use Spatie\Permission\Models\Role;

class FormResourceTest extends TestCase
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
        $records = TargetModel::factory()->count(5)->create();
        Livewire::test(TargetResource\Pages\ListForms::class)->assertCanSeeTableRecords($records);
    }

    public function test_can_create_record(): void
    {
        Livewire::test(TargetResource\Pages\CreateForm::class)
            ->fillForm([
                'title' => $this->faker->words(3, true),
                'confirmation_title' => $this->faker->words(3, true),
                'confirmation_text' => tiptap_converter()->asJSON($this->faker->words(3, true)),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        Livewire::test(TargetResource\Pages\CreateForm::class)
            ->fillForm([
                'title' => '',
            ])
            ->call('create')
            ->assertHasFormErrors([
                'title' => 'required',
            ]);
    }

    public function test_can_render_edit_page(): void
    {
        $data = $this->generateModel();

        $this->get(TargetResource::getUrl('edit', ['record' => $data]))->assertSuccessful();
    }

    public function test_can_retrieve_edit_data(): void
    {
        $this->generateModel();
        $data = TargetModel::first();

        Livewire::test(
            TargetResource\Pages\EditForm::class,
            ['record' => $data->getRouteKey()]
        )
            ->assertFormSet([
                'title'  => $data->title,
            ]);
    }

    public function test_can_save_form(): void
    {
        $data = $this->generateModel();

        $new = TargetModel::make([
            'title' => $this->faker->words(3, true),
        ]);

        Livewire::test(TargetResource\Pages\EditForm::class, [
            'record' => $data->getRoutekey(),
        ])
        ->fillForm([
            'title'  => $new->title,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

        $data->refresh();
        $this->assertEquals($data->title, $new->title);

    }

    public function test_can_render_form()
    {
        $form = TargetModel::factory()->create();
        $this->get(route('form.show', $form->slug))->assertSuccessful();
    }

    public function test_can_submit_form()
    {
        $form = TargetModel::factory()->create();

        Livewire::test(FormShow::class, ['slug' => $form->slug])
            ->fillForm([
                'Checkbox' => true,
                'Checkbox List' => ['Option 1', 'Option 2'],
                'Date Picker' => now()->format('Y-m-d'),
                'Radio' => 'Option 1',
                'Relationship' => [1,2],
                'Rich Text' => tiptap_converter()->asJSON('<p>Rich Text</p>'),
                'Select' => 'Option 1',
                'Text Area' => 'Text Area',
                'Text Input' => 'Text Input',
            ])
            ->call('submitForm')
            ->assertHasNoFormErrors();
    }

    public function generateModel(): TargetModel
    {
        return TargetModel::factory()->create();
    }
}
