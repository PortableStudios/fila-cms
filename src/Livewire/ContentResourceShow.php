<?php

namespace Portable\FilaCms\Livewire;

use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Portable\FilaCms\Facades\FilaCms;

class ContentResourceShow extends Component implements HasForms
{
    use InteractsWithForms;
    use WithPagination;

    public ?array $data = [];

    public $message;

    public $record;

    public $model;

    protected $view = 'fila-cms::livewire.content-resource-show';

    protected $queryString = ['data'];

    public function mount($slug): void
    {
        $modelClass = $this->model;
        $this->record = $modelClass::where('slug', $slug)->firstOrFail();
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form;
        //    return $form->schema($this->getFilters())->statePath('data');
    }

    public function submit()
    {
        $this->message = $this->getResultsQuery()->toSql();
    }

    #[Layout('fila-cms::layouts.app')]
    public function render()
    {
        return view($this->view, $this->getViewData());
    }

    protected function getViewData()
    {
        $resourceClass = FilaCms::getContentModelResource($this->model);

        return [
            'showRoute' => $resourceClass::getFrontendShowRoute()
        ];
    }
}
