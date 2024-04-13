<?php

namespace Portable\FilaCms\Filament\Resources\AbstractContentResource\Pages;

use FilaCms;
use Str;

trait CanCheckSlug
{
    protected function generateSlug($data)
    {
        $class = get_class($this);
        $parent = new $class();
        $resource = $parent::getResource();
        $model = FilaCms::getModelFromResource($resource);

        if (is_null($data['slug'])) {
            // auto-generate then check
            $data['slug'] = Str::slug($data['title']);
        } else {
            $data['slug'] = Str::slug($data['slug']);
        }

        if ($this->checkIfSlugExists($data['slug'], $model)) {
            $increment = 1;

            while (true) {
                $slug = $data['slug'] . '-' . $increment;
                if ($this->checkIfSlugExists($slug, $model) === false) {
                    $data['slug'] = $slug;
                    break;
                }
                $increment++;
            }
        }

        return $data['slug'];
    }

    protected function checkIfSlugExists($slug, $modelName)
    {
        $data = ($modelName::withoutGlobalScopes())
            ->where('slug', $slug)
            ->first();

        if (is_null($data)) {
            return false;
        }
        return true;
    }
}
