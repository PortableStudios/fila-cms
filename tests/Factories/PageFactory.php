<?php

namespace Portable\FilaCms\Tests\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Portable\FilaCms\Models\Page;

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

        return [
            'title'     => $title,
            'slug'      => Str::slug($title),
            'is_draft'  => $draft,
            'publish_at'    => $draft === 1 ? $this->faker->dateTimeBetween('-1 week', '+1 week') : null,
            'expire_at'    => $draft === 1 ? $this->faker->dateTimeBetween('+1 week', '+2 weeks') : null,
            'contents'  => $this->createContent(),
        ];
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
}
