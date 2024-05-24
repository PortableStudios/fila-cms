<?php

namespace Portable\FilaCms\Filament\Pages;

use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Portable\FilaCms\Filament\Traits\IsProtectedResource;

class UserSettings extends Page implements HasForms
{
    use IsProtectedResource;
    use InteractsWithForms;

    public ?array $twoFactor = [];
    public $qrCode = '';

    protected static bool $shouldRegisterNavigation = false;
    protected static ?string $title = 'User Settings';
    protected static ?string $navigationIcon = 'heroicon-o-cog-8-tooth';
    protected static string $view = 'fila-cms::admin.pages.user-settings';
}
