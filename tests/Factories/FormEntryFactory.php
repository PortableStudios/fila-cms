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
            'fields' => [
                [
                    "type" => "Text Field",
                    "data" => [
                        "field_name" => "Your Name",
                        "text_type" => "text",
                        "required" => true,
                        "max_length" => null,
                        "default_value" => null
                    ]
                ],
                [
                    "type" => "Text Field",
                    "data" => [
                        "field_name" => "Telephone",
                        "text_type" => "telephone",
                        "required" => true,
                        "max_length" => null,
                        "default_value" => null
                    ]
                ]
            ]
        ];
    }
}
