<?php

namespace Portable\FilaCms\Commands;

use Filament\Forms\Commands\Concerns\CanGenerateForms;
use Filament\Support\Commands\Concerns\CanIndentStrings;
use Filament\Support\Commands\Concerns\CanManipulateFiles;
use Filament\Support\Commands\Concerns\CanReadModelSchemas;
use Filament\Tables\Commands\Concerns\CanGenerateTables;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

use function Laravel\Prompts\text;

class MakeContentPermissionSeeder extends Command
{
    use CanGenerateForms;
    use CanGenerateTables;
    use CanIndentStrings;
    use CanManipulateFiles;
    use CanReadModelSchemas;

    protected $description = 'Create a new seeder to create permissions for a content resource model';

    protected $signature = 'make:filacms-content-permissions {name?}';

    public function handle(): int
    {
        $model = (string) str($this->argument('name') ?? text(
            label: 'What is the model name?',
            placeholder: 'BlogPost',
            required: true,
        ))
            ->studly()
            ->beforeLast('Resource')
            ->trim('/')
            ->trim('\\')
            ->trim(' ')
            ->studly()
            ->replace('/', '\\');

        $path = database_path('seeders');

        $plural = Str::plural(\Filament\Support\get_model_label($model));

        $this->copyStubToApp('database/seeders/add_permissions_seeder', $path . '/' . $model . 'RoleAndPermissionSeeder.php', [
            'class' => $model,
            'plural' => $plural,
        ]);

        $this->components->info("FilaCms seeder [{$model}.php] created successfully.");

        $answer = $this->ask('Would you like to run the seeder now? (Y/n)');

        if (Str::lower($answer) === 'y') {
            require_once($path . '/' . $model . 'RoleAndPermissionSeeder.php');
            $this->call('db:seed', ['--class' => $model . 'RoleAndPermissionSeeder']);
        }

        return static::SUCCESS;
    }

    public function getDefaultStubPath(): string
    {
        return realpath(__DIR__ . '/../../stubs');
    }
}
