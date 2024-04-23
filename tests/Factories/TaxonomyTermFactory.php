<?php

namespace Portable\FilaCms\Tests\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Portable\FilaCms\Models\TaxonomyTerm;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class TaxonomyTermFactory extends Factory
{
    protected $model = TaxonomyTerm::class;

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
