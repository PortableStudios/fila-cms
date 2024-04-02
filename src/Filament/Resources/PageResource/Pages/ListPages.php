<?php

namespace Portable\FilaCms\Filament\Resources\PageResource\Pages;

use Portable\FilaCms\Filament\Resources\AbstractContentResource\Pages\ListAbstractContentResources;
use Portable\FilaCms\Filament\Resources\PageResource;
use Illuminate\Database\Eloquent\Builder;

class ListPages extends ListAbstractContentResources
{
    protected static string $resource = PageResource::class;

    public function isTableSearchable(): bool
    {
        return true;
    } 

    protected function applySearchToTableQuery(Builder $query): Builder
    {
        if (filled($searchQuery = $this->getTableSearch())) {
            $searchQuery = '%' . $searchQuery . '%';
            $query->where('title', 'LIKE', $searchQuery)
                ->orWhere('slug', 'LIKE', $searchQuery)
                ->orWhere('contents', 'LIKE', $searchQuery)
                ->orWhere('slug', 'LIKE', $searchQuery)
                ->orWhere('slug', 'LIKE', $searchQuery);
        }
    
        return $query;
    }
}
