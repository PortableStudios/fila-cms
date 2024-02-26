<?php

namespace Portable\FilaCms\Livewire;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Portable\FilaCms\Facades\FilaCms;

class ContentResourceList extends Component implements HasForms
{
    use InteractsWithForms;
    use WithPagination;

    public ?array $data = [];

    public $message;

    public $model;

    protected $view = 'fila-cms::livewire.content-resource-list';

    protected $queryString = ['data'];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form->schema($this->getFilters())->statePath('data');
    }

    public function submit()
    {

    }

    protected function getFilters()
    {
        $filters = [TextInput::make('title')->placeholder('Search by title...')->live(debounce: 500)];
        $modelClass = $this->model;
        foreach ($modelClass::taxonomies() as $tax) {
            $filters[] = CheckboxList::make($tax->name)->options($tax->terms->pluck('name', 'id'));
        }

        return $filters;
    }

    protected function getResultsQuery()
    {
        $modelClass = $this->model;
        $query = $modelClass::query();

        if (isset($this->data['title']) && $this->data['title']) {
            $query->where('title', 'like', '%'.$this->data['title'].'%');
        }

        $modelClass = $this->model;
        foreach ($modelClass::taxonomies() as $tax) {
            if (isset($this->data[$tax->name]) && count($this->data[$tax->name])) {
                $query->whereHas('terms', function ($q) use ($tax) {
                    $q->whereIn('id', $this->data[$tax->name]);
                });
            }
        }

        return $query;
    }

    #[Layout('fila-cms::layouts.app')]
    public function render()
    {
        return view($this->view, $this->getViewData());
    }

    protected function getViewData()
    {
        $resourceClass = FilaCms::getContentModelResource($this->model);
        $prefix = method_exists($resourceClass, 'getFrontendRoutePrefix') ? $resourceClass::getFrontendRoutePrefix() : $resourceClass::getRoutePrefix();

        return [
            'results' => $this->getResultsQuery()->paginate(10),
            'title' => Str::title($resourceClass::getPluralModelLabel()),
            'showRoute' => $prefix.'.show',
        ];
    }
}
