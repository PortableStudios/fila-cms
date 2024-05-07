<?php

namespace Portable\FilaCms\Tests\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Portable\FilaCms\Models\Form;

class FormFactory extends Factory
{
    protected $model = Form::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = $this->faker->words(6, true);
        return [
            'title'     => $title,
            'slug'      => Str::slug($title),
            'confirmation_title'  => 'Thank You!',
            'confirmation_text'    => tiptap_converter()->asJSON('<p>Thank you for submitting the form.</p>'),
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
