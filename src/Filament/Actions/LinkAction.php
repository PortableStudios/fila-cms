<?php

namespace Portable\FilaCms\Filament\Actions;

use Filament\Forms\ComponentContainer;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use FilamentTiptapEditor\TiptapEditor;
use Illuminate\Support\HtmlString;
use Portable\FilaCms\Facades\FilaCms;
use Portable\FilaCms\Filament\Forms\Components\MediaPicker;
use Portable\FilaCms\Filament\Resources\FormResource;
use Portable\FilaCms\Models\Form;
use Portable\FilaCms\Models\Media;

class LinkAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'filament_tiptap_link';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->modalWidth('lg')
            ->arguments([
                'href' => '',
                'id' => '',
                'reference_media' => '',
                'reference_page' => '',
                'reference_content' => '',
                'reference_text' => '',
                'target' => '',
                'link_type' => '',
                'referrerpolicy' => '',
                'as_button' => false,
                'button_theme' => '',
            ])->mountUsing(function (ComponentContainer $form, array $arguments) {
                $arguments = $this->parseArguments($arguments);
                $form->fill($arguments);
            })->modalHeading(function (array $arguments) {
                $context = blank($arguments['href']) ? 'insert' : 'update';

                return trans('filament-tiptap-editor::link-modal.heading.' . $context);
            })->form([
                Grid::make(['md' => 3])
                    ->schema([
                        Select::make('link_type')
                            ->options([
                                'index-page' => 'Content Listing Page',
                                'content' => 'Content Detail Page',
                                'media' => 'Media',
                                'download' => 'Download',
                                'url'  => 'URL',
                            ])
                            ->default('content')
                            ->selectablePlaceholder(false)
                            ->columnSpanFull()
                            ->live()
                            ->required(),

                            Group::make()
                            ->schema([
                                MediaPicker::make('reference_media')
                                    ->label('Media')
                                    ->visible(function (Get $get) {
                                        $type = $get('link_type');
                                        return ($type === 'media' || $type == 'download');
                                    })
                                    ->columnSpanFull(),
                                Select::make('reference_page')
                                    ->label('Content Type')
                                    ->visible(function (Get $get) {
                                        $type = $get('link_type');
                                        return ($type !== 'url' && $type !== 'media' && $type !== 'download');
                                    })
                                    ->options(static::getContentSources())
                                    ->required()
                                    ->live()
                                    ->columnSpan(function (Get $get) {
                                        return $get('link_type') === 'index-page' ? 4 : 2;
                                    }),
                                Select::make('reference_content')
                                    ->label('Content Item')
                                    ->visible(function (Get $get) {
                                        $type = $get('link_type');
                                        return ($type === 'content');
                                    })
                                    ->getSearchResultsUsing(function (string $search, Get $get) {
                                        return static::modelSearch($get('reference_page'), $search);
                                    })
                                    ->getOptionLabelUsing(function (string $value, Get $get) {
                                        return static::modelDisplay($get('reference_page'), $value);
                                    })
                                    ->live()
                                    ->required()
                                    ->searchable()
                                    ->columnSpan(2),
                                TextInput::make('reference_text')
                                    ->visible(fn (Get $get) => $get('link_type') === 'url' ? true : false)
                                    ->label('URL')
                                    ->columnSpanFull()
                                    ->required(),
                            ])
                            ->columnSpanFull()
                            ->columns(4),

                        Select::make('target')
                            ->selectablePlaceholder(false)
                            ->options([
                                '' => trans('filament-tiptap-editor::link-modal.labels.target.default'),
                                '_blank' => trans('filament-tiptap-editor::link-modal.labels.target.new_window'),
                                '_parent' => trans('filament-tiptap-editor::link-modal.labels.target.parent'),
                                '_top' => trans('filament-tiptap-editor::link-modal.labels.target.top'),
                            ])->columnSpanFull(),
                    ]),
            ])->action(function (TiptapEditor $component, $data) {
                $component->getLivewire()->dispatch(
                    event: 'insertFromAction',
                    type: 'link',
                    statePath: $component->getStatePath(),
                    href: $this->getHref($data),
                    target: $data['target'],
                );

                $component->state($component->getState());
            })->extraModalFooterActions(function (Action $action): array {
                if ($action->getArguments()['href'] !== '') {
                    return [
                        $action->makeModalSubmitAction('remove_link', [])
                            ->color('danger')
                            ->extraAttributes(function () use ($action) {
                                return [
                                    'x-on:click' => new HtmlString("\$dispatch('unset-link', {'statePath': '{$action->getComponent()->getStatePath()}'}); close()"),
                                    'style' => 'margin-inline-start: auto;',
                                ];
                            }),
                    ];
                }

                return [];
            });
    }

    protected function getHref($data)
    {
        switch($data['link_type']) {
            case 'index-page':
                $resourceClass = $data['reference_page'];
                return route($resourceClass::getFrontendIndexRoute());
            case 'content':
                $resourceClass = $data['reference_page'];
                $model = ($resourceClass::getModel())::find($data['reference_content']);
                $prefix = $resourceClass::getFrontendRoutePrefix();
                if ($prefix == '') {
                    return '/' . $model?->slug;
                } else {
                    return route($resourceClass::getFrontendShowRoute(), ['slug' => $model?->slug]);
                }
                // no break
            case 'media':
                return Media::find($data['reference_media'])?->url;
            case 'download':
                return route('media.download', ['media' => $data['reference_media']]);
            default:
                return $data['reference_text'];
        }
    }

    public static function getContentSources()
    {
        $sources = FilaCms::getContentModels();
        $sources[FormResource::class] = 'Forms';

        return $sources;
    }

    protected static function modelSearch($source, $search)
    {
        $query = static::modelQuery($source)->where('title', 'like', '%' . $search . '%')->get();

        return $query->pluck('title', 'id');
    }

    protected static function modelDisplay($source, $value)
    {
        static::modelQuery($source)->find($value)->title;
        $titleField = $source == Media::class ? 'filename' : 'title';
        return static::modelQuery($source)->find($value)->$titleField;
    }

    protected static function modelQuery($source)
    {
        $className = FilaCms::getModelFromResource($source);

        if (!$className) {
            if($source === FormResource::class) {
                $query = Form::query();
            } else {
                $query = Media::query()->where('is_folder', 0);
            }
        } else {
            $query = $className::query();
        }

        return $query;
    }

    protected function parseArguments($args)
    {
        $url = $args['href'];
        // If the URL is a route, we need to parse it to get the correct arguments
        try {
            $route = app('router')->getRoutes()->match(app('request')->create($url), 'GET');
            if(count($route->parameters) == 0) {
                $args['link_type'] = 'url';
                $args['reference_text'] = $url;
                return $args;
            }
            if(isset($route->parameters['model'])) {
                $model = $route->parameters['model'];
                $resource = FilaCms::getContentModelResource($model);
            } else {
                $model = Form::class;
                $resource = FormResource::class;
            }
            $args['reference_page'] = $resource;

            if(isset($route->parameters['slug'])) {
                $args['link_type'] = 'content';
                $args['reference_content'] = $model::query()->where('slug', $route->parameters['slug'])->first()?->id;
            } elseif(isset($route->parameters['media'])) {
                $args['reference_media'] = $route->parameters['media'];
                if(isset($route->parameters['mediaExtension'])) {
                    $args['link_type'] = 'media';
                } else {
                    $args['link_type'] = 'download';
                }
            } else {
                $args['link_type'] = 'index-page';
            }
        } catch(\Exception $e) {
            $args['link_type'] = 'url';
            $args['reference_text'] = $url;
        }

        return $args;
    }
}
