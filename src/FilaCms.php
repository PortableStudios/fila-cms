<?php

namespace Portable\FilaCms;

use Closure;
use Filament\Facades\Filament;
use FilamentTiptapEditor\Enums\TiptapOutput;
use FilamentTiptapEditor\TiptapEditor;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver as GDDriver;
use Intervention\Image\ImageManager;
use Portable\FilaCms\Data\SettingData;
use Portable\FilaCms\Filament\Resources\AbstractContentResource;
use Portable\FilaCms\Filament\Resources\FormResource;
use Portable\FilaCms\Models\Form;
use Portable\FilaCms\Models\Media;
use Portable\FilaCms\Models\ShortUrl;
use ReflectionClass;

class FilaCms
{
    protected static $contentResources = null;
    protected static $contentModels = null;
    protected static $settings = null;

    public function systemUser()
    {
        $userModel = config('auth.providers.users.model');
        $system = $userModel::query()->where('email', 'system@filacms')->first();
        if ($system) {
            return $system;
        }

        $userFieldsRaw = Schema::getColumnListing((new $userModel())->getTable());

        $excludeFields = [ 'id', 'created_at', 'updated_at', 'deleted_at', 'remember_token', 'subscribed', 'email_verified_at', 'password','email','two_factor_confirmed_at'];

        $data = [
            'name' => 'System',
            'email' => 'system@filacms',
            'password' => Hash::make(Str::random(24))
        ];
        $userFields = array_diff($userFieldsRaw, $excludeFields);
        foreach ($userFields as $key => $field) {
            $data[$field] = 'SYSTEM';
        }
        $data['email_verified_at'] = new Carbon();

        $systemUser = $userModel::create($data);

        return $systemUser;
    }

    public function getModelFromResource($resource)
    {
        if (!is_array(self::$contentModels)) {
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

    public function formRoutes()
    {
        Route::group(
            ['prefix' => FormResource::getFrontendRoutePrefix(), 'middleware' => 'web'],
            function () {
                Route::get('/{slug}', \Portable\FilaCms\Livewire\FormShow::class)->name(FormResource::getFrontendShowRoute());
                Route::post('/{slug}', \Portable\FilaCms\Livewire\FormShow::class)->name(FormResource::getFrontendShowRoute() . '.submit');
            }
        );
    }

    public function profileRoutes()
    {
        Route::group(
            ['prefix' => 'user', 'middleware' => [config('fortify.auth_middleware', 'auth'), config('fortify.guard')]],
            function () {
                Route::get('profile-information', function () {
                    return view('fila-cms::auth.user-profile');
                })->name('user-profile-information.show');
            }
        );
    }

    public function contentRoutes()
    {
        $this->getContentModels();
        foreach (static::$contentModels as $modelClass => $resourceClass) {
            $prefix = $resourceClass::getFrontendRoutePrefix();
            $registerIndex = $resourceClass::registerIndexRoute();
            $registerShow = $resourceClass::registerShowRoute();
            $feIndexComponent = $resourceClass::getFrontendIndexComponent();
            $feShowComponent = $resourceClass::getFrontendShowComponent();

            if ($prefix !== '') {
                Route::group(
                    ['prefix' => $prefix, 'middleware' => ['web', \Portable\FilaCms\Http\Middleware\ContentRoleMiddleware::class]],
                    function () use ($feShowComponent, $resourceClass, $registerIndex, $registerShow, $feIndexComponent, $modelClass) {
                        if ($registerIndex) {
                            Route::get('/', $feIndexComponent)
                                ->name($resourceClass::getFrontendIndexRoute())
                                ->defaults('model', $modelClass);
                        }
                        if ($registerShow) {
                            Route::get('/{slug}', $feShowComponent)->name($resourceClass::getFrontendShowRoute())->defaults('model', $modelClass);
                        }
                    }
                );
            } else {
                // If there's no prefix, manually register all the routes, we don't create a catch-all hole
                Route::group(
                    ['middleware' => ['web', \Portable\FilaCms\Http\Middleware\ContentRoleMiddleware::class]],
                    function () use ($feShowComponent, $resourceClass, $registerIndex, $registerShow, $feIndexComponent, $modelClass) {
                        if ($registerIndex) {
                            Route::get('/', $feIndexComponent)
                                ->name($resourceClass::getFrontendIndexRoute())
                                ->defaults('model', $modelClass);
                        }
                        if ($registerShow) {
                            try {
                                foreach ($modelClass::all() as $model) {
                                    Route::get('/' . $model->slug, $feShowComponent)
                                        ->defaults('slug', $model->slug)
                                        ->defaults('model', $modelClass);
                                }
                            } catch (\Exception $e) {
                                // Models may not exist yet, we might be running migrations, etc.
                            }
                        }
                    }
                );
            }
        }
    }

    public function getRawContentModels()
    {
        if (is_null(static::$contentModels)) {
            static::getContentModels();
        }
        return static::$contentModels;
    }

    public function shortUrlRoutes()
    {
        Route::get(config('fila-cms.short_url_prefix') . '/{slug}', function ($slug) {

            $shortUrl = ShortUrl::where('url', $slug)->first();
            if (empty($shortUrl)) {
                abort(404);
            }

            $modelClass = $shortUrl->shortable_type;

            $target = new $modelClass();
            $page = $target->find($shortUrl->shortable_id);

            if ($shortUrl->enable === false || empty($page)) {
                abort(404);
            }

            $shortUrl->increment('hits');

            // TODO: make it more dynamic instead of direct using "pages"
            return redirect(route('pages.show', $page->slug ?? $page->id), $shortUrl->redirect_status ?? 302);
        });
    }

    public function thumbnail(Media $media, $size = 'small')
    {
        $thumbnailSizes = config('fila-cms.media_library.thumbnails');
        if (!isset($thumbnailSizes[$size])) {
            throw new \Exception("Invalid thumbnail size");
        }
        $width = $thumbnailSizes[$size]['width'];
        $height = $thumbnailSizes[$size]['height'];

        switch ($media->mime_type) {
            case 'application/pdf':
                $filePath = dirname(__FILE__) . '/../resources/images/pdf-icon.png';
                $manager = new ImageManager(GDDriver::class);
                $image = $manager->read($filePath);
                return $image->scaleDown($width, $height)->encodeByMediaType('image/png', 75);
            default:
                return $this->imageThumbnail($media, $width, $height);
        }
    }

    protected function imageThumbnail(Media $media, $width, $height)
    {
        $disk = Storage::disk($media->disk);
        $manager = new ImageManager(GDDriver::class);
        $imageBinary = $disk->get($media->filepath . '/' . $media->filename);
        try {
            $image = $manager->read($imageBinary);
        } catch (\Exception $e) {
            $filePath = dirname(__FILE__) . '/../resources/images/image-icon.png';
            $image = $manager->read($filePath);
        }

        return $image->scaleDown($width, $height)->encodeByMediaType('image/png', 75);
    }

    public function registerSetting(string $tab, string $group, int $order, Closure $fieldsCallback)
    {
        if (static::$settings === null) {
            static::$settings = collect();
        }

        $settingData = new SettingData($tab, $group, $order, $fieldsCallback);
        static::$settings->push($settingData);
    }

    public function getSettings()
    {
        return static::$settings;
    }

    public function getSettingsFields()
    {
        $settings = static::$settings;
        $tabs = [];
        foreach ($settings->groupBy(['tabName','groupName'])->sortBy('order') as $tabName => $groups) {
            $tabs[$tabName] = [];
            foreach ($groups as $groupName => $settingsData) {
                $groupFields = [];
                foreach ($settingsData as $settingsDatum) {
                    $groupFields = array_merge($groupFields, call_user_func($settingsDatum->fields));
                }
                $tabs[$tabName][$groupName] = $groupFields;
            }
        }

        return $tabs;
    }

    public function getFormBlock(string $name)
    {
        $blocks = config('fila-cms.forms.blocks');
        foreach ($blocks as $block) {
            if ($block::getBlockName() === $name) {
                return $block;
            }
        }
        return null;
    }

    public function tipTapEditor($name): TiptapEditor
    {
        return TiptapEditor::make($name)
            ->tools([
                    'heading', 'bullet-list', 'ordered-list', 'checked-list', 'blockquote', 'hr', '|',
                    'bold', 'italic', 'strike', 'underline', 'superscript', 'subscript', 'lead', 'small', 'color', 'highlight', 'align-left', 'align-center', 'align-right', '|',
                    'link', 'media', 'oembed', 'table', 'grid-builder', '|', 'code', 'code-block', 'source', 'blocks',
                ])
            ->extraInputAttributes(['style' => 'min-height: 24rem;'])
            ->required()
            ->columnSpanFull()
            ->collapseBlocksPanel(true)
            ->output(TiptapOutput::Json);
    }

    public function ssoRoutes()
    {
        $providers = config('fila-cms.sso.providers', ['google','facebook','linkedin']);
        foreach ($providers as $provider) {
            if (config('settings.sso.' . $provider . '.client_id') && config('settings.sso.' . $provider . '.client_secret')) {
                Route::get('/login/' . $provider, [\Portable\FilaCms\Http\Controllers\SSOController::class, 'redirectToProvider'])
                    ->middleware('web')->name('login.' . $provider);
                Route::get('/login/' . $provider . '/callback', [\Portable\FilaCms\Http\Controllers\SSOController::class, 'handleProviderCallback'])
                ->middleware('web');
            }
        }
    }

    public function search($term)
    {
        $results = [];
        foreach (static::$contentModels as $modelClass => $resourceClass) {
            $results = array_merge($results, $modelClass::search($term)->get()->toArray());
        }

        $results = array_merge($results, Form::search($term)->get()->toArray());

        return $results;
    }
}
