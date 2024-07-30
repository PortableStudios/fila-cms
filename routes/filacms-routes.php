<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Portable\FilaCms\Facades\FilaCms;
use Portable\FilaCms\Models\Media;

foreach (config('fila-cms.media_library.thumbnails') as $size => $dimensions) {
    Route::get('/media/{media}/thumbnail/' . $size, function ($media) use ($size) {

        $media = Media::findOrFail($media);
        return response(FilaCms::thumbnail($media, $size))->withHeaders(['Content-Type' => 'image/png']);
    })->name('media.thumbnail.' . $size);

    Route::get('media/{media}.{mediaExtension}', function ($media, $mediaExtension) {
        $media = Media::findOrFail($media);
        return response(Storage::disk($media->disk)->get($media->filepath . '/' . $media->filename))->withHeaders(['Content-Type' => $media->mime_type]);
    })->name('media.show');

    Route::get('media/{media}/download', function ($media) {
        $media = Media::findOrFail($media);
        return response(Storage::disk($media->disk)->get($media->filepath . '/' . $media->filename))->withHeaders([
            'Content-Type' => $media->mime_type,
            'Content-Disposition' => 'attachment; filename="' . $media->filename . '"'
        ]);
    })->name('media.download');
}

Route::get('impersonate/leave', function () {
    Auth::user()->leaveImpersonation();
    return redirect(route('filament.admin.pages.dashboard'));
})->name('impersonate.leave')->middleware('web');

Route::post('purify', function () {
    return FilaCms::purifyHtml(request()->input('html'));
})->name('purify');
