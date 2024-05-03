<?php

namespace Portable\FilaCms\Livewire;

use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Portable\FilaCms\Filament\FormBlocks\FormBuilder;
use Portable\FilaCms\Models\Form as ModelsForm;
use Portable\FilaCms\Models\FormEntry;

class FormShow extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];
    public $record;
    public $submitted = false;
    protected $view = 'fila-cms::livewire.form-show';

    public function mount(string $slug): void
    {
        $this->record = ModelsForm::where('slug', $slug)->first();
        if(!$this->record || ($this->record->only_for_logged_in && !auth()->user())) {
            abort(404);
        }
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form->schema(FormBuilder::getFields($this->record->fields))->statePath('data');
    }

    public function submitForm()
    {
        FormEntry::create([
            'form_id' => $this->record->id,
            'user_id' => auth()->user()?->id,
            'fields' => $this->record->fields,
            'values' => $this->data,
        ]);

        $this->submitted = true;
    }

    #[Layout('fila-cms::layouts.app')]
    public function render()
    {
        return view($this->view);
    }

}
