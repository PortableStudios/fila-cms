<?php

namespace Portable\FilaCms\Tests\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
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

        return [
            'title'     => fake()->words(15, true),
            'is_draft'  => $draft,
            'publish_at'    => $draft === 1 ? $this->faker->dateTimeBetween('-1 week', '+1 week') : null,
            'expire_at'    => $draft === 1 ? $this->faker->dateTimeBetween('-1 week', '+1 week') : null,
            'contents'  => fake()->words($this->faker->numberBetween(50, 150), true),
        ];
    }
}
