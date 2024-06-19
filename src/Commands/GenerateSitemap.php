<?php

namespace Portable\FilaCms\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;
use Portable\FilaCms\Models\Form;
use Portable\FilaCms\Facades\FilaCms;
use Str;
use Storage;

class GenerateSitemap extends Command
{
    protected $signature = 'fila-cms:generate-sitemap';

    protected $description = 'Generate sitemap';

    protected $exemptedWords = [
        '_',
        'storybook',
        'horizon',
        'livewire',
        'two-factor-challenge',
        'sanctum',
        'filament',
        'reset-password',
        'media/{media}',
        's/{slug}',
        'link/{type}/{slug}',
    ];

    protected $exemptedMiddleware = [
        'filament-actions',
        'Illuminate\Session\Middleware\AuthenticateSession',
        'Laravel\Pulse\Http\Middleware\Authorize',
        'auth',
        'auth:sanctum',
        'auth:web'
    ];

    public function handle()
    {
        $routes = Route::getRoutes();

        $filteredRoutes = [];
        $this->generateSlugs('');

        foreach ($routes as $key => $route) {
            $shouldInclude = true;

            // only GET routes
            if (collect($route->methods())->search('GET') === false) {
                continue;
            }

            foreach ($this->exemptedWords as $word) {
                if (Str::of($route->uri)->startsWith($word)) {
                    $shouldInclude = false;
                    break;
                }
            }

            foreach ($this->exemptedMiddleware as $middleware) {
                if (collect($route->gatherMiddleware())->search($middleware) !== false) {
                    $shouldInclude = false;
                    break;
                }
            }

            if ($shouldInclude === false) {
                continue;
            }

            if (Str::of($route->uri)->contains('{slug}') == false) {
                $filteredRoutes[] = [url($route->uri), now()->subDays(7)];
            }
        }

        // add other slugs
        $slugs = $this->generateSlugs();
        $filteredRoutes = array_merge($filteredRoutes, $slugs);

        $sitemap = $this->generateSitemap($filteredRoutes);

        Storage::disk('public')->put('/sitemap.xml', $sitemap);
        // file_put_contents(public_path('/sitemap.xml'), $sitemap);
    }

    protected function generateSlugs()
    {
        $models = FilaCms::getRawContentModels();
        unset($models["Portable\FilaCms\Models\Page"]);

        $records = [];

        foreach ($models as $model => $resource) {
            $data = $model::whereDoesntHave('seo', function ($query) {
                $query->where('robots', 'noindex, nofollow')
                    ->orWhere('robots', 'noindex, follow');
            })
                ->get();

            foreach ($data as $key => $record) {
                $records[] = [$record->url, $record->updated_at];
            }
        }

        $forms = Form::where('only_for_logged_in', 0)->get();

        foreach ($forms as $key => $form) {
            $records[] = [$form->url, $form->updated_at];
        }

        return $records;
    }

    protected function generateSitemap($routes)
    {
        $xw = xmlwriter_open_memory();
        xmlwriter_set_indent($xw, 1);
        $res = xmlwriter_set_indent_string($xw, ' ');
        xmlwriter_start_document($xw, '1.0', 'UTF-8');

        xmlwriter_start_element($xw, 'urlset');
        xmlwriter_start_attribute($xw, 'xmlns');
        xmlwriter_text($xw, "http://www.sitemaps.org/schemas/sitemap/0.9");
        xmlwriter_end_attribute($xw);

        foreach ($routes as $key => $route) {
            xmlwriter_start_element($xw, 'url');
            xmlwriter_start_element($xw, 'loc');
            xmlwriter_text($xw, $route[0]);
            xmlwriter_end_element($xw);
            xmlwriter_start_element($xw, 'changefreq');
            xmlwriter_text($xw, 'monthly');
            xmlwriter_end_element($xw);
            xmlwriter_start_element($xw, 'lastmod');
            xmlwriter_text($xw, $route[1]->format('Y-m-d H:i:s'));
            xmlwriter_end_element($xw);
            xmlwriter_end_element($xw);
        }

        xmlwriter_end_element($xw);

        return xmlwriter_output_memory($xw);
    }
}
