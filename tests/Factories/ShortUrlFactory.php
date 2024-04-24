<?php

namespace Portable\FilaCms\Tests\Factories;

use Illuminate\Support\Str;
use Portable\FilaCms\Models\ShortUrl;
use Portable\FilaCms\Models\Page;
use Illuminate\Database\Eloquent\Factories\Factory;

class ShortUrlFactory extends Factory
{
    protected $model = ShortUrl::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $page = Page::create();

        return [
            'url' => Str::slug(fake()->name()),
            // 'shortable_id' => $page->id,
            // 'shortable_type' => Page::class,
        ];
    }
}
