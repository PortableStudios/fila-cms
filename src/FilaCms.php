<?php

namespace Portable\FilaCms;

use Filament\Facades\Filament;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver as GDDriver;
use Intervention\Image\ImageManager;
use Portable\FilaCms\Filament\Resources\AbstractContentResource;
use Portable\FilaCms\Livewire\ContentResourceList;
use Portable\FilaCms\Livewire\ContentResourceShow;
use Portable\FilaCms\Models\Media;
use Portable\FilaCms\Models\ShortUrl;
use ReflectionClass;

class FilaCms
{
    protected static $contentResources = null;

    protected static $contentModels = null;

    public function systemUser()
    {
        $userModel = config('auth.providers.users.model');
        $system = $userModel::query()->where('email', 'system@filacms')->first();
        if($system) {
            return $system;
        }

        $userFieldsRaw = Schema::getColumnListing((new $userModel())->getTable());

        $excludeFields = [ 'id', 'created_at', 'updated_at', 'deleted_at', 'remember_token', 'email_verified_at', 'password','email'];
        $data = [
            'name' => 'System',
            'email' => 'system@filacms',
            'password' => Hash::make(Str::random(24))
        ];
        $userFields = array_diff($userFieldsRaw, $excludeFields);
        foreach ($userFields as $key => $field) {
            $data[$field] = 'SYSTEM';
        }

        $systemUser = $userModel::create($data);

        return $systemUser;
    }

    public function getModelFromResource($resource)
    {
        if(!is_array(self::$contentModels)) {
            $this->getContentModels();
        }
        return array_search($resource, self::$contentModels);
    }

    public function getContentModelResource($modelClass)
    {
        // @codeCoverageIgnoreStart
        if (is_null(self::$contentModels)) {
            $this->getContentModels();
        }
        // @codeCoverageIgnoreEnd

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

    public function shortUrlRoutes()
    {
        Route::get(config('fila-cms.short_url_prefix') . '/{slug}', function ($slug) {

            $shortUrl = ShortUrl::where('url', $slug)->first();
            if(empty($shortUrl)) {
                abort(404);
            }

            $modelClass = $shortUrl->shortable_type;

            $target = new $modelClass();
            $page = $target->find($shortUrl->shortable_id);

            if(empty($page)) {
                abort(404);
            }

            // SUGGESTION: add hit counter?
            // $shortUrl->increment('hits');

            // TODO: make it more dynamic instead of direct using "pages"
            return redirect()->route('pages.show', $page->slug ?? $page->id);
        });
    }

    public function thumbnail(Media $media, $size = 'small')
    {
        $thumbnailSizes = config('fila-cms.media_library.thumbnails');
        if(!isset($thumbnailSizes[$size])) {
            throw new \Exception("Invalid thumbnail size");
        }
        $disk = Storage::disk($media->disk);

        $manager = new ImageManager(GDDriver::class);

        $imageBinary = $disk->get($media->filepath . '/' . $media->filename);

        $image = $manager->read($imageBinary);

        return $image->scaleDown($thumbnailSizes[$size]['width'], $thumbnailSizes[$size]['height'])->encodeByMediaType('image/png', 75);
    }
}
