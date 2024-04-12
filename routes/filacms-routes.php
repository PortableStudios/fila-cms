<?php

use Illuminate\Support\Facades\Route;
use Portable\FilaCms\Facades\FilaCms;
use Portable\FilaCms\Models\Media;

foreach(config('fila-cms.media_library.thumbnails') as $size => $dimensions) {
    Route::get('/media/{media}/thumbnail/'.$size, function ($media) use ($size) {

        $media = Media::findOrFail($media);
        return response(FilaCms::thumbnail($media, $size))->withHeaders(['Content-Type' => 'image/png']);

    })->name('media.thumbnail.'.$size);
}
