<?php

namespace Portable\FilaCms\Models;

use CodeInc\HumanReadableFileSize\HumanReadableFileSize;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    protected $fillable = [
        'parent_id',
        'is_folder',
        'filename',
        'filepath',
        'mime_type',
        'size',
        'disk',
        'url',
        'extension',
        'alt_text'
    ];

    public function mediumThumbnail(): Attribute
    {
        return new Attribute(function ($value) {
            return $this->url;
        });
    }

    public function children()
    {
        return $this->hasMany(Media::class, 'parent_id');
    }

    public function displaySize(): Attribute
    {
        return Attribute::make(function ($value) {
            if($this->is_folder) {
                return $this->children->count() > 0 ? $this->children->count() . ' items' : 'Empty';
            } else {
                return HumanReadableFileSize::getHumanSize($this->size);
            }
        });
    }

    public function currentParent()
    {
        return $this->belongsTo(Media::class, 'parent_id');
    }

    public function move($newParent, $newName = null)
    {
        if($newParent->id !== $this->parent_id) {
            if($newParent->disk !== $this->disk) {
                throw new \Exception('Cannot move media to a different disk');
            }
        }

        $currentPath = $this->filepath . '/' . $this->filename;
        $newPath = $newParent->filepath . '/' . ($newName ?: $this->filename);

        Storage::disk($this->disk)->move($currentPath, $newPath);
        $this->update([
            'parent_id' => $newParent->id,
            'filepath' => $newParent->filepath,
            'filename' => $newName ?: $this->filename,
        ]);
    }
}
