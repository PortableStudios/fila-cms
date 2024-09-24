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

class MakeContentResource extends Command
{
    use CanGenerateForms;
    use CanGenerateTables;
    use CanIndentStrings;
    use CanManipulateFiles;
    use CanReadModelSchemas;

    protected $description = 'Create a new Filament resource class and default page classes';

    protected $signature = 'make:filacms-content-resource {name?}';

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

        $path = app_path('Filament/Resources/');

        $baseResourcePath = (string) str($model . 'Resource')
            ->prepend('/')
            ->prepend($path)
            ->replace('\\', '/')
            ->replace('//', '/');

        $listResourcePageClass = 'List' . Str::plural($model);
        $createResourcePageClass = 'Create' . $model;
        $editResourcePageClass = 'Edit' . $model;
        $reviseResourcePageClass = $model . 'Revisions';

        $resourcePath = "{$baseResourcePath}.php";
        $resourcePagesDirectory = "{$baseResourcePath}/Pages";
        $listResourcePagePath = "{$resourcePagesDirectory}/{$listResourcePageClass}.php";
        $createResourcePagePath = "{$resourcePagesDirectory}/{$createResourcePageClass}.php";
        $editResourcePagePath = "{$resourcePagesDirectory}/{$editResourcePageClass}.php";
        $reviseResourcePagePath = "{$resourcePagesDirectory}/{$reviseResourcePageClass}.php";

        $this->copyStubToApp('Resources/ContentResource', $resourcePath, [
            'class' => $model,
            'pluralClass' => Str::plural($model),
            'model' => $model,
        ]);

        $this->copyStubToApp('Resources/ContentResource/Pages/CreateResource', $createResourcePagePath, [
            'class' => $model,
            'pluralClass' => Str::plural($model),
            'model' => $model,
        ]);

        $this->copyStubToApp('Resources/ContentResource/Pages/EditResource', $editResourcePagePath, [
            'class' => $model,
            'pluralClass' => Str::plural($model),
            'model' => $model,
        ]);

        $this->copyStubToApp('Resources/ContentResource/Pages/ListResources', $listResourcePagePath, [
            'class' => $model,
            'pluralClass' => Str::plural($model),
            'model' => $model,
        ]);

        $this->copyStubToApp('Resources/ContentResource/Pages/ResourceRevisions', $reviseResourcePagePath, [
            'class' => $model,
            'pluralClass' => Str::plural($model),
            'model' => $model,
        ]);

        $this->components->info("FilaCms content resource [{$resourcePath}] created successfully.");

        return static::SUCCESS;
    }

    public function getDefaultStubPath(): string
    {
        return realpath(__DIR__ . '/../../stubs');
    }
}
