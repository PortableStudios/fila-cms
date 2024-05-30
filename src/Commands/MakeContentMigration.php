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

class MakeContentMigration extends Command
{
    use CanGenerateForms;
    use CanGenerateTables;
    use CanIndentStrings;
    use CanManipulateFiles;
    use CanReadModelSchemas;

    protected $description = 'Create a new database migration for a content resource model';

    protected $signature = 'make:filacms-content-migration {name?}';

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

        $migrationFile = now()->format('Y_m_d_His') . '_create_' . Str::plural(Str::snake($model)) . '_table.php';
        $path = database_path('migrations');

        $this->copyStubToApp('database/migrations/create_content_model', $path . '/' . $migrationFile, [
            'table' => Str::plural(Str::snake($model)),
        ]);

        $this->components->info("FilaCms migration [{$migrationFile}] created successfully.");

        return static::SUCCESS;
    }

    public function getDefaultStubPath(): string
    {
        return realpath(__DIR__ . '/../../stubs');
    }
}
