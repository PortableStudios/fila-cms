<?php

namespace Portable\FilaCms\Filament\Pages;

use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Portable\FilaCms\Filament\Traits\IsProtectedResource;

class Sitemap extends Page implements HasForms
{
    use IsProtectedResource;
    use InteractsWithForms;

    public ?array $data = [];
    protected static ?string $title = 'Sitemap';
    protected static ?string $navigationIcon = 'heroicon-o-map';
    protected static string $view = 'fila-cms::admin.pages.sitemap';
    protected static ?string $navigationGroup = 'System';

    public function mount(): void
    {

    }

    public function form(Form $form): Form
    {

    }

    public function save(): void
    {

    }
}
