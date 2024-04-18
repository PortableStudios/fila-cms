<?php

namespace Portable\FilaCms\Tests\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Portable\FilaCms\Models\Taxonomy;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class TaxonomyFactory extends Factory
{
    protected $model = Taxonomy::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
        ];
    }

}
