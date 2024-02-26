<?php

namespace Portable\FilaCms;

use Filament\Facades\Filament;
use Illuminate\Support\Facades\Route;
use Portable\FilaCms\Filament\Resources\AbstractContentResource;
use Portable\FilaCms\Livewire\ContentResourceList;
use Portable\FilaCms\Livewire\ContentResourceShow;
use ReflectionClass;

class FilaCms
{
    protected static $contentResources = null;

    protected static $contentModels = null;

    public function getContentModelResource($modelClass)
    {
        if (is_null(self::$contentModels)) {
            $this->getContentModels();
        }

        return isset(self::$contentModels[$modelClass]) ? self::$contentModels[$modelClass] : null;
    }

    public function getContentModels()
    {
        if (! is_null(self::$contentResources) && ! is_null(self::$contentModels)) {
            return self::$contentResources;
        }

        $options = [];
        static::$contentModels = [];
        foreach (Filament::getPanels() as $panel) {
            foreach ($panel->getResources() as $resourceClass) {
                $reflectionObject = new ReflectionClass($resourceClass);
                if ($reflectionObject->isSubclassOf(AbstractContentResource::class)) {
                    $options[$resourceClass] = $resourceClass::getNavigationLabel();
                    static::$contentModels[$resourceClass::getModel()] = $resourceClass;
                }
            }
        }
        static::$contentResources = $options;

        return $options;
    }

    public function contentRoutes()
    {
        $this->getContentModels();
        foreach (static::$contentModels as $modelClass => $resourceClass) {
            $prefix = method_exists($resourceClass, 'getFrontendRoutePrefix') ? $resourceClass::getFrontendRoutePrefix() : $resourceClass::getRoutePrefix();
            $registerIndex = method_exists($resourceClass, 'registerIndexRoute') ? $resourceClass::registerIndexRoute() : true;
            $registerShow = method_exists($resourceClass, 'registerShowRoute') ? $resourceClass::registerShowRoute() : true;
            $feIndexComponent = method_exists($resourceClass, 'getFrontendIndexComponent') ? $resourceClass::getFrontendIndexComponent() : ContentResourceList::class;
            $feShowComponent = method_exists($resourceClass, 'getFrontendShowComponent') ? $resourceClass::getFrontendShowComponent() : ContentResourceShow::class;

            Route::group(
                ['prefix' => $prefix, 'middleware' => 'web'],
                function () use ($feShowComponent, $prefix, $registerIndex, $registerShow, $feIndexComponent, $modelClass) {
                    if ($registerIndex) {
                        Route::get('/', $feIndexComponent)
                            ->name($prefix.'.index')
                            ->defaults('model', $modelClass);
                    }
                    if ($registerShow) {
                        Route::get('/{slug}', $feShowComponent)->name($prefix.'.show')->defaults('model', $modelClass);
                    }
                }
            );
        }
    }
}
