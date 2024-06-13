<?php

namespace Portable\FilaCms\Models;

use CodeInc\HumanReadableFileSize\HumanReadableFileSize;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
        'width',
        'height',
        'extension',
        'alt_text'
    ];

    public static function boot()
    {
        parent::boot();

        static::deleting(function ($media) {
            if ($media->is_folder) {
                $media->children->each->delete();
            } else {
                Storage::disk($media->disk)->delete($media->filepath . '/' . $media->filename);
            }
        });
    }

    public function isImage(): Attribute
    {
        return new Attribute(function () {
            return Str::startsWith($this->mime_type, 'image');
        });
    }

    public function url(): Attribute
    {
        return new Attribute(function ($value) {
            return route('media.show', ['media' => $this, 'mediaExtension' => $this->extension]);
        });
    }

    public function smallThumbnail(): Attribute
    {
        return new Attribute(function ($value) {
            return $this->is_folder ? static::folderImage() : route('media.thumbnail.small', $this);
        });
    }

    public function mediumThumbnail(): Attribute
    {
        return new Attribute(function ($value) {
            return $this->is_folder ? static::folderImage() : $this->url;
        });
    }

    public function children()
    {
        return $this->hasMany(Media::class, 'parent_id');
    }

    public function displaySize(): Attribute
    {
        return Attribute::make(function ($value) {
            if ($this->is_folder) {
                return $this->children->count() > 0 ? $this->children->count() . ' items' : 'Empty';
            } else {
                try {
                    $readableSize = new HumanReadableFileSize();
                    $readableSize->setSpaceBeforeUnit(true);
                    $readableSize->useNumberFormatter('en-AU');
                    return preg_replace('/\.\d{1,2}(K?B)/', '$1', $readableSize->compute($this->size));
                } catch(\Exception $e) {
                    // Dealing with nulls
                    return '?';
                }
            }
        });
    }

    /**
     * Computes a human readable size.
     *
     * @param int $bytes
     * @param int $decimals
     * @return string
     */
    public function readableSizeCompute(HumanReadableFileSize $readableSize, int $bytes, int $decimals = 2): string
    {
        $factor = floor((strlen((string)$bytes) - 1) / 3);
        $number = floatval(sprintf("%.{$decimals}f", $bytes / pow($readableSize->getBytesPeyKilo(), $factor)));
        if($readableSize->getNumberFormatter()) {
            $number = $readableSize->getNumberFormatter()->format($number);
        }
        return $number.($readableSize->hasSpaceBeforeUnit() ? ' ' : '').@$readableSize->getUnits()[$factor];
    }

    public function currentParent()
    {
        return $this->belongsTo(Media::class, 'parent_id');
    }

    public function move($newParent, $newName = null)
    {
        if ($newParent?->id !== $this->parent_id) {
            if ($newParent->disk !== $this->disk) {
                throw new \Exception('Cannot move media to a different disk');
            }
        }

        $currentPath = $this->filepath . '/' . $this->filename;
        $newPath = $newParent?->filepath . '/' . ($newName ?: $this->filename);

        Storage::disk($this->disk)->move($currentPath, $newPath);
        $this->update([
            'parent_id' => $newParent?->id,
            'filepath' => $newParent?->filepath,
            'filename' => $newName ?: $this->filename,
        ]);
    }

    public static function folderImage()
    {
        $data = Blade::render('@svg("heroicon-o-folder")');

        $encodedSVG = \rawurlencode(\str_replace(["\r", "\n"], ' ', $data));
        return 'data:image/svg+xml,' . $encodedSVG;
    }

    public static function uploadImage()
    {
        $data = Blade::render('@svg("heroicon-m-arrow-up-tray")');

        $encodedSVG = \rawurlencode(\str_replace(["\r", "\n"], ' ', $data));
        return 'data:image/svg+xml,' . $encodedSVG;
    }
}
