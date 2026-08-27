<?php

namespace Portable\FilaCms\Tests;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Orchestra\Testbench\Attributes\WithMigration;
use Orchestra\Testbench\Concerns\WithWorkbench;
use Portable\FilaCms\Database\Seeders\RoleAndPermissionSeeder;

#[WithMigration]
abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    use RefreshDatabase;
    use WithWorkbench;

    protected static $hasInstalled = false;

    protected function setUp(): void
    {
        parent::setUp();

        // The package's vite build targets a host app: the skeleton's published tailwind config
        // resolves ./vendor relative to itself, which doesn't exist inside our own vendor dir. So
        // assets are never built here and views using @vite would fail on a missing manifest.
        $this->withoutVite();

        // Laravel 11+ flushes factory state after every test, so this must be re-registered each time.
        Factory::guessFactoryNamesUsing(function (string $modelName) {
            return (string) '\\Portable\\FilaCms\\Tests\\Factories\\' . (class_basename($modelName)) . 'Factory';
        });

        if (static::$hasInstalled) {
            return;
        }
        static::$hasInstalled = true;

        $this->withFactories(__DIR__ . '/tests/Factories');

        // remove config files
        File::delete(config_path('fila-cms.php'));
        File::delete(config_path('seo.php'));
        File::delete(config_path('filament-tiptap-editor.php'));

        // remove filament theme
        File::delete(resource_path('css/filament/admin/tailwind.config.js'));
        File::delete(resource_path('css/filament/admin/theme.css'));

        $this->artisan('fila-cms:install')
            ->expectsOutputToContain('Installing Filament Base...')
            ->expectsQuestion('Would you like to publish the FilaCMS config?(Y/n)', 'Y')
            ->expectsQuestion('Would you like to run migrations(Y/n)?', 'Y')
            ->expectsQuestion('Would you like to add the required trait to your App\\Models\\User model?(Y/n)', 'Y')
            ->expectsOutputToContain('Finished')
            ->assertExitCode(0);

        File::copy(getcwd() . '/vite.config.js', resource_path('../vite.config.js'));
        File::ensureDirectoryExists(resource_path('css'));
        File::copy(getcwd() . '/resources/css/filacms.css', resource_path('css/filacms.css'));
        // vite.config.js lists these as entries; without them the build fails and any test
        // rendering a frontend view dies on a missing manifest.
        File::copyDirectory(getcwd() . '/resources/js', resource_path('js'));
        File::copy(getcwd() . '/package.json', resource_path('../package.json'));

        $this->artisan('db:seed', ['--class' => RoleAndPermissionSeeder::class]);
    }

    protected function defineEnvironment($app)
    {
        tap($app['config'], function (Repository $config) {
            $config->set('auth.providers.users.model', User::class);
        });
    }

    protected function getPackageProviders($app)
    {
        $packages = parent::getPackageProviders($app);
        $packages[] = \Filament\Support\SupportServiceProvider::class;
        $packages[] = \RyanChandler\BladeCaptureDirective\BladeCaptureDirectiveServiceProvider::class;
        $packages[] = \Filament\FilamentServiceProvider::class;
        $packages[] = \Filament\Forms\FormsServiceProvider::class;
        $packages[] = \Filament\Actions\ActionsServiceProvider::class;
        $packages[] = \Filament\Infolists\InfolistsServiceProvider::class;
        $packages[] = \Filament\Notifications\NotificationsServiceProvider::class;
        $packages[] = \Filament\Tables\TablesServiceProvider::class;
        $packages[] = \Filament\Widgets\WidgetsServiceProvider::class;
        $packages[] = \Livewire\LivewireServiceProvider::class;
        $packages[] = \FilamentTiptapEditor\FilamentTiptapEditorServiceProvider::class;
        $packages[] = \Mansoor\FilamentVersionable\FilamentVersionableServiceProvider::class;
        $packages[] = \Spatie\Permission\PermissionServiceProvider::class;
        $packages[] = \Laravel\Sanctum\SanctumServiceProvider::class;
        $packages[] = \Portable\FilaCms\Providers\FilaCmsServiceProvider::class;
        $packages[] = \Portable\FilaCms\Providers\FilaCmsServiceProvider::class;
        $packages[] = \RalphJSmit\Laravel\SEO\LaravelSEOServiceProvider::class;
        $packages[] = \Rawilk\FilamentPasswordInput\FilamentPasswordInputServiceProvider::class;
        $packages[] = \Laravel\Scout\ScoutServiceProvider::class;
        /*
*/
        // App\Providers\BroadcastServiceProvider::class,

        return $packages;
    }

    public function createUser()
    {
        return User::create([
            'name' => 'Jeremy Layson',
            'email' => 'jeremy.layson+' . mt_rand(1111, 9999) . '@portable.com.au',
            'password' => 'password',
        ]);
    }
}
