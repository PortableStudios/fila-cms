<?php

namespace Portable\FilaCms\Filament\Resources;

use Portable\FilaCms\Filament\Traits\UserConfigurableResource;
use Filament\Resources\Resource as ResourcesResource;

abstract class AbstractConfigurableResource extends ResourcesResource
{
    use UserConfigurableResource;
}
