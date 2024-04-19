<?php

namespace Portable\FilaCms\Database\Seeders;

use Illuminate\Database\Seeder;

class RootMediaFoldersSeeder extends Seeder
{
    public function run()
    {
        foreach(config('fila-cms.media_library.root_folders') as $folderData) {
            \Portable\FilaCms\Models\Media::firstOrCreate([
                'filename' => $folderData['name'],
                'disk' => isset($folderData['disk']) ? $folderData['disk'] : config('filesystems.default'),
                'is_folder' => true,
            ]);
        }
    }
}
