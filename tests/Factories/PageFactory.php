<?php

namespace Portable\FilaCms\Tests\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Portable\FilaCms\Models\Page;
use Portable\FilaCms\Models\TaxonomyTerm;

class PageFactory extends Factory
{
    protected $model = Page::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $draft = fake()->numberBetween(0, 1);
        $title = fake()->words(15, true);

        TaxonomyFactory::new()
            ->has(TaxonomyTermFactory::new()->count(mt_rand(3, 5)), 'terms')
            ->count(1)
            ->create();

        return [
            'title'     => $title,
            'slug'      => Str::slug($title),
            'is_draft'  => $draft,
            'publish_at'    => $draft === 1 ? $this->faker->dateTimeBetween('-1 week', '+1 week') : null,
            'expire_at'    => $draft === 1 ? $this->faker->dateTimeBetween('+1 week', '+2 weeks') : null,
            'contents'  => $this->createContent(),
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Page $page) {
            $page->terms()->sync(TaxonomyTerm::all()->random(mt_rand(2, 3))->pluck('id'));
        });
    }

    protected function createContent()
    {
        return [
            'type'  => 'doc',
            'content' => [
                [
                    'type' => 'paragraph',
                    'attrs' => [
                        'class' => null,
                        'style' => null,
                        'textAlign' => 'start',
                    ],
                    'content' => [
                        [
                            'text' => fake()->words(mt_rand(10, 50), true),
                            'type' => 'text'
                        ]
                    ]
                ]
            ]
        ];
    }

    public function isPublished(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'is_draft' => false,
                'publish_at' => now()->subDays(3),
                'expire_at' => null,
            ];
        });
    }
}
