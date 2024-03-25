<?php
namespace Portable\FilaCms\Filament\Blocks;

use FilamentTiptapEditor\TiptapBlock;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Placeholder;

use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Repeater;

use Filament\Forms\Get;
use Filament\Forms\Set;

use Portable\FilaCms\Models\Page;
use FilaCms;

class RelatedResourceBlock extends TiptapBlock
{
    public string $preview = 'fila-cms::filament.blocks.previews.related-contents';

    public string $rendered = 'fila-cms::filament.blocks.rendered.related-contents';

    public string $width = '5xl';

    public bool $slideOver = FALSE;

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
                                                ->options([
                                                    'pages' => 'Pages'
                                                ])
                                                ->columnSpan(1),
                                            Select::make('content')
                                                ->live()
                                                ->searchable()
                                                ->required()
                                                ->getSearchResultsUsing(fn(string $search, Get $get, Set $set): array => $this->getContents($search, $get, $set))
                                                ->afterStateUpdated(function(?string $state, ?string $old, Get $get, Set $set) {
                                                    $article = ($this->getSourceModel($get('source')))
                                                        ->select('id', 'title')
                                                        ->where('id', $state)
                                                        ->first();
                                                    $set('title', $article->title);
                                                })
                                                ->columnSpan(2),
                                            TextInput::make('title')
                                                ->hidden()
                                        ])
                                        ->columns(3)
                                ])
                        ])
                ])
        ];
    }

    public function getContents(string $search, $get, $set): array
    {
        $data = [];
        $models = NULL;
        $source = $get('source');

        $models = ($this->getSourceModel($source))->select('id', 'title')
            // ->whereNotIn('id', $this->getExcludeIds($get, $set))
            ->where('contents', 'LIKE', '%' . $search . '%')
            ->get();

        foreach ($models as $key => $model) {
            $data[$model->id] = $model->title;
        }

        return $data;
    }

    public function getContent($source, $id)
    {
        return `{$source}: {$id}`;
    }

    protected function getSourceModel($source)
    {
        if ($source === 'pages') {
            return new Page;
        }
    }
}