<?php

namespace Portable\FilaCms\Tests\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Portable\FilaCms\Models\Taxonomy;
use Portable\FilaCms\Models\TaxonomyTerm;

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
        $name = fake()->words(2, true);
        return [
            'name' => $name,
            'code' => Str::slug($name)
        ];
    }

    public function forResources(array $resources): self
    {
        return $this->afterCreating(function (Taxonomy $taxonomy) use ($resources) {
            $taxonomy->resources()->createMany(array_map(function ($model) {
                return ['resource_class' => $model];
            }, $resources));
        });
    }

    public function withTerms(int $count = 3): self
    {
        return $this->afterCreating(function (Taxonomy $taxonomy) use ($count) {
            $taxonomy->terms()->saveMany(TaxonomyTerm::factory($count)->create(['taxonomy_id' => $taxonomy->id]));
        });
    }
}
