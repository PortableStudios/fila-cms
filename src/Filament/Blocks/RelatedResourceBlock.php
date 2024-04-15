<?php

namespace Portable\FilaCms\Filament\Blocks;

use FilaCms;

use Filament\Forms\Components\Group;
use Filament\Forms\Components\Repeater;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;

use Filament\Forms\Get;
use Filament\Forms\Set;

use FilamentTiptapEditor\TiptapBlock;
use Str;

class RelatedResourceBlock extends TiptapBlock
{
    public string $preview = 'fila-cms::filament.blocks.previews.related-contents';

    public string $rendered = 'fila-cms::filament.blocks.rendered.related-contents';

    public string $width = '5xl';

    public bool $slideOver = false;

    public ?string $icon = 'heroicon-o-film';

    public function getFormSchema(): array
    {
        $articles = [];

        return [
            Group::make()
                ->schema([
                    Group::make()
                        ->schema([
                            TextInput::make('heading')
                                ->required(),
                        ]),
                    Group::make()
                        ->schema([
                            Repeater::make('selectedContents')
                                ->schema([
                                    Group::make()
                                        ->schema([
                                            Select::make('source')
                                                ->live()
                                                ->required()
                                                ->options($this->getSources())
                                                ->columnSpan(1),
                                            Select::make('content')
                                                ->live()
                                                ->searchable()
                                                ->hidden(fn (Get $get): bool => empty($get('source')))
                                                ->required()
                                                ->getSearchResultsUsing(fn (string $search, Get $get): array => $this->getContents($search, $get))
                                                ->getOptionLabelUsing(fn (string $value, Get $get): ?string => ($this->getSourceModel($get('source')))->select('id', 'title')->where('id', $value)->first()?->title)
                                                ->afterStateUpdated(function (?string $state, ?string $old, Get $get, Set $set) {
                                                    $article = ($this->getSourceModel($get('source')))
                                                        ->select('id', 'title')
                                                        ->where('id', $state)
                                                        ->first();
                                                    if($article) {
                                                        $set('title', $article->title);
                                                    }
                                                })
                                                ->columnSpan(2),
                                            TextInput::make('title')
                                                ->hidden()
                                        ])
                                        ->columns(3)
                                ])
                                ->addActionLabel('Add Another')
                        ])
                ])
        ];
    }

    public function getContents(string $search, $get): array
    {
        $data = [];
        $models = null;
        $source = $get('source');

        $models = ($this->getSourceModel($source))
            ->select('id', 'title')
            ->whereNotIn('id', $this->getExcludeIds($get))
            ->where(function ($q) use ($search) {
                $q->where('contents', 'LIKE', '%' . $search . '%')
                ->orWhere('title', 'LIKE', '%' . $search . '%');

            })
            ->get();

        foreach ($models as $key => $model) {
            $data[$model->id] = $model->title;
        }

        return $data;
    }

    /**
     * Get all current arrays of selected IDs
     * So that it can be excluded in the search result
     */
    protected function getExcludeIds($get)
    {
        $selectedContents = $get('../../selectedContents');

        $ids = [];

        foreach ($selectedContents as $key => $content) {
            if (is_null($content['content']) === false) {
                $ids[] = $content['content'];
            }
        }

        return $ids;
    }

    /**
     * Take all models that extends the abstract content resource
     */
    protected function getSources()
    {
        $sources = FilaCms::getContentModels();
        $list = [];

        foreach ($sources as $key => $source) {
            $list[Str::lower($source)] = $source;
        }

        return $list;
    }

    /**
     * Gets the origin model of the selected source
     * The model could reside on the FilaCMS package
     * or on the project that implements it
     */
    protected function getSourceModel($source)
    {
        $source = Str::studly($source);
        $models = FilaCms::getContentModels();
        $resource = array_search($source, $models);
        $className = FilaCms::getModelFromResource($resource);

        return new $className();
    }
}
