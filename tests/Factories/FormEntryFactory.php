<?php

namespace Portable\FilaCms\Tests\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Portable\FilaCms\Models\FormEntry;

class FormEntryFactory extends Factory
{
    protected $model = FormEntry::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'status'     => $this->faker->randomElement(['New', 'Open', 'Closed']),
            'values' => [
                'Your Name' => $this->faker->name,
                'Telephone' => $this->faker->phoneNumber
            ],
            'fields' => FormFactory::getFields()
        ];
    }
}
