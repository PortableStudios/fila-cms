<?php

namespace Portable\FilaCms\Commands;

use Illuminate\Console\Command;

class MakeContents extends Command
{
    protected $signature = 'make:filacms-contents {name?}';

    protected $description = 'Creates a new migration, model, resource and permission seeder for an abstract content item';

    public function handle()
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

        $this->call('make:filacms-content-migration', ['name' => $model]);
        $this->call('make:filacms-content-model', ['name' => $model]);
        $this->call('make:filacms-content-permissions', ['name' => $model]);
        $this->call('make:filacms-content-resource', ['name' => $model]);
    }
}
