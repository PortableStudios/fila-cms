<?php

namespace Portable\FilaCms\Tests\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Portable\FilaCms\Models\FormEntry;
use Portable\FilaCms\Filament\FormBlocks\FormBuilder;

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
        $fields = FormFactory::getFields();
        $values = [];

        foreach ($fields as $field) {
            $fieldName = data_get($field, 'data.' . FormBuilder::$fieldId);
            if (!$fieldName) {
                continue;
            }

            if ($fieldName === 'Text Area') {
                $values[$fieldName] = $this->faker->sentence;

            } else {
                $values[$fieldName] = $this->faker->word;
            }
        }
        return [
            'status'     => $this->faker->randomElement(['New', 'Open', 'Closed']),
            'values' => $values,
            'fields' => $fields
        ];
    }
}
