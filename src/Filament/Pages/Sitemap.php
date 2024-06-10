<?php

namespace Portable\FilaCms\Filament\Pages;

use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Artisan;
use Portable\FilaCms\Facades\FilaCms;
use Portable\FilaCms\Filament\Traits\IsProtectedResource;
use Portable\FilaCms\Models\Setting;

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
