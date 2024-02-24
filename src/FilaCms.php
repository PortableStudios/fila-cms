<?php

namespace Portable\FilaCms;

use Filament\Facades\Filament;
use Portable\FilaCms\Filament\Resources\AbstractContentResource;
use ReflectionClass;

class FilaCms
{
    public function getContentModels()
    {
        $options = [];
        foreach (Filament::getPanels() as $panel) {
            foreach ($panel->getResources() as $resourceClass) {
                $reflectionObject = new ReflectionClass($resourceClass);
                if ($reflectionObject->isSubclassOf(AbstractContentResource::class)) {
                    $options[$resourceClass] = $resourceClass::getNavigationLabel();
                }
            }
        }

        return $options;
    }
}
