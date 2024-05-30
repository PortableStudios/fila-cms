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

class MakeContentModel extends Command
{
    use CanGenerateForms;
    use CanGenerateTables;
    use CanIndentStrings;
    use CanManipulateFiles;
    use CanReadModelSchemas;

    protected $description = 'Create a new model  for a content resource model';

    protected $signature = 'make:filacms-content-model {name?}';

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

        $path = app_path('Models');

        $this->copyStubToApp('Models/ContentModel', $path . '/' . $model . '.php', [
            'model' => $model,
            'resourceName' => $model . 'Resource',
            'table' => Str::plural(Str::snake($model)),
        ]);

        $this->components->info("FilaCms content model [{$model}.php] created successfully.");

        return static::SUCCESS;
    }

    public function getDefaultStubPath(): string
    {
        return realpath(__DIR__ . '/../../stubs');
    }
}
