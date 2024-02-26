<?php

namespace Portable\FilaCms\Filament\Resources;

use Filament\Resources\Resource as ResourcesResource;
use Portable\FilaCms\Filament\Traits\UserConfigurableResource;

abstract class AbstractConfigurableResource extends ResourcesResource
{
    use UserConfigurableResource;
}