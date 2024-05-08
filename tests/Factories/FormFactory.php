<?php

namespace Portable\FilaCms\Tests\Factories;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Portable\FilaCms\Filament\FormBlocks\CheckboxBlock;
use Portable\FilaCms\Filament\FormBlocks\CheckboxListBlock;
use Portable\FilaCms\Filament\FormBlocks\DateTimeInputBlock;
use Portable\FilaCms\Filament\FormBlocks\InformationBlock;
use Portable\FilaCms\Filament\FormBlocks\RadioBlock;
use Portable\FilaCms\Filament\FormBlocks\RelationshipBlock;
use Portable\FilaCms\Filament\FormBlocks\RichTextBlock;
use Portable\FilaCms\Filament\FormBlocks\SelectBlock;
use Portable\FilaCms\Filament\FormBlocks\TextAreaBlock;
use Portable\FilaCms\Filament\FormBlocks\TextInputBlock;
use Portable\FilaCms\Filament\Resources\PageResource;
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
            'fields' => static::getFields()
        ];
    }

    public static function getFields()
    {
        return [
            [
                "type" => CheckboxBlock::getBlockName(),
                "data" => [
                    "field_name" => "Checkbox",
                    "required" => true,
                    "default_value" => null
                ]
            ],
            [
                "type" => CheckboxListBlock::getBlockName(),
                "data" => [
                    "field_name" => "Checkbox List",
                    "required" => true,
                    "default_value" => null,
                    "options" => [
                        ["option_name" => "Option 1", "option_value" => "Option 1"],
                        ["option_name" => "Option 2", "option_value" => "Option 2"],
                        ["option_name" => "Option 3", "option_value" => "Option 3"],
                    ]
                ]
            ],
            [
                "type" => DateTimeInputBlock::getBlockName(),
                "data" => [
                    "field_name" => "Date Picker",
                    "required" => true,
                    'date_type' => DatePicker::class,
                    "default_value" => null
                ]
            ],
            [
                "type" => InformationBlock::getBlockName(),
                "data" => [
                    "field_name" => "Information",
                    "contents" => [
                        "type" => "paragraph",
                        "attrs" => [
                            "class" => null,
                            "style" => null,
                            "textAlign" => "start"
                        ],
                        "content" => [
                            [
                                "type" => "text",
                                "text" => "This is an information block."
                            ]
                        ]
                    ]
                ]
            ],
            [
                "type" => RadioBlock::getBlockName(),
                "data" => [
                    "field_name" => "Radio",
                    "required" => true,
                    "default_value" => null,
                    "options" => [
                        ["option_name" => "Option 1", "option_value" => "Option 1"],
                        ["option_name" => "Option 2", "option_value" => "Option 2"],
                        ["option_name" => "Option 3", "option_value" => "Option 3"],
                    ]
                ]
            ],
            [
                "type" => RelationshipBlock::getBlockName(),
                "data" => [
                    "field_name" => "Relationship",
                    "required" => true,
                    "component_class" => CheckboxList::class,
                    'relationship' => PageResource::class,
                ]
            ],
            [
                "type" => RichTextBlock::getBlockName(),
                "data" => [
                    "max_length" => 100,
                    "field_name" => "Rich Text",
                    "required" => true,
                    "default_value" => null
                ]
            ],
            [
                "type" => SelectBlock::getBlockName(),
                "data" => [
                    "field_name" => "Select",
                    "required" => true,
                    "default_value" => null,
                    "options" => [
                        ["option_name" => "Option 1", "option_value" => "Option 1"],
                        ["option_name" => "Option 2", "option_value" => "Option 2"],
                        ["option_name" => "Option 3", "option_value" => "Option 3"],
                    ]
                ]
            ],
            [
                "type" => TextAreaBlock::getBlockName(),
                "data" => [
                    "field_name" => "Text Area",
                    "required" => true,
                    "max_length" => 100,
                    "default_value" => null
                ]
            ],
            [
                "type" => TextInputBlock::getBlockName(),
                "data" => [
                    "field_name" => "Text Input",
                    "text_type" => "text",
                    "required" => true,
                    "max_length" => 100,
                    "default_value" => null
                ]
            ],
        ];
    }
}
