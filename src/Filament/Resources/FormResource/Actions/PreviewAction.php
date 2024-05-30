<?php

namespace Portable\FilaCms\Filament\Resources\FormResource\Actions;

use Filament\Actions\Action;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Portable\FilaCms\Filament\FormBlocks\FormBuilder;

class PreviewAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'preview';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->form(function (PreviewAction $action, Form $form, Get $get) {
            $livewire = $action->getLivewire();
            $formData = $livewire->data;
            $fields = FormBuilder::getFields($formData['fields']);
            return $fields;
        })->modalHeading(function (PreviewAction $action) {
            return $action->getLivewire()->data['title'];
        });
    }
}
