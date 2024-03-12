<?php

namespace Portable\FilaCms\Tests\Feature;

use Filament\Models\Contracts\FilamentUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Portable\FilaCms\Tests\TestCase;
use Portable\FilaCms\Tests\UserNoImplements;
use Spatie\Permission\Traits\HasRoles;

class UserConcernsTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    public function test_dry_run()
    {
        $oldModel = config('auth.providers.users.model');
        config([
            'auth.providers.users.model' => UserNoImplements::class,
        ]);

        $userModel = config('auth.providers.users.model');
        $userReflection = new \ReflectionClass($userModel);
        $traitsAndInterfaces = [
            HasRoles::class,
            FilamentUser::class,
        ];

        $fileContents = file_get_contents($userReflection->getFileName());
        foreach($traitsAndInterfaces as $traitOrInterface) {
            $this->assertStringNotContainsString($traitOrInterface, $fileContents, "User model does not have $traitOrInterface");
        }

        $this->artisan('fila-cms:add-user-concerns', ['--dry-run' => true])
            ->expectsOutput('Adding traits and interfaces to User Model')
            ->expectsOutput('Adding trait Spatie\Permission\Traits\HasRoles')
            ->expectsOutput('Adding interface Filament\Models\Contracts\FilamentUser')
            ->assertExitCode(0);

        $fileContentsAfter = file_get_contents($userReflection->getFileName());
        $this->assertEquals($fileContents, $fileContentsAfter);
        config([
            'auth.providers.users.model' => $oldModel,
        ]);
    }
}
