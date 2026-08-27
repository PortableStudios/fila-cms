<?php

namespace Portable\FilaCms\Tests\Filament;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Portable\FilaCms\Database\Seeders\RootMediaFoldersSeeder;
use Portable\FilaCms\Livewire\MediaLibraryTable;
use Portable\FilaCms\Models\Media;
use Portable\FilaCms\Tests\TestCase;

class MediaLibraryTest extends TestCase
{
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_root_folders(): void
    {
        // Without seeding there are no root folders, and assertCanSeeTableRecords() on an empty
        // collection asserts nothing at all.
        Artisan::call('db:seed', ['--class' => RootMediaFoldersSeeder::class]);

        $rootFolders = Media::whereNull('parent_id')->get();
        $this->assertNotEmpty($rootFolders);

        Livewire::test(MediaLibraryTable::class)
            ->assertCanSeeTableRecords($rootFolders);
    }

    public function test_create_folder(): void
    {
        Artisan::call('db:seed', ['--class' => RootMediaFoldersSeeder::class]);
        $imageFolder = Media::whereNull('parent_id')->where('filename', 'Images')->firstOrFail();

        $subfolder = Media::where('parent_id', $imageFolder->id)->where('filename', 'Test Image Subfolder')->first();
        $this->assertNull($subfolder);
        Livewire::test(MediaLibraryTable::class)
            ->call('setParent', $imageFolder->id)
            ->callTableAction('create_folder', null, ['folder_name' => 'Test Image Subfolder']);

        $subfolder = Media::where('parent_id', $imageFolder->id)->where('filename', 'Test Image Subfolder')->first();
        $this->assertNotNull($subfolder);
    }

    public function test_upload_to_subfolder(): void
    {
        Artisan::call('db:seed', ['--class' => RootMediaFoldersSeeder::class]);

        $imageFolder = Media::whereNull('parent_id')->where('filename', 'Images')->firstOrFail();

        $subfolder = Media::where('parent_id', $imageFolder->id)->where('filename', 'Test Image Subfolder')->first();
        $this->assertNull($subfolder);
        Livewire::test(MediaLibraryTable::class)
            ->call('setParent', $imageFolder->id)
            ->callTableAction('create_folder', null, ['folder_name' => 'Test Image Subfolder']);

        $subfolder = Media::where('parent_id', $imageFolder->id)->where('filename', 'Test Image Subfolder')->first();

        // Remove existing image in storage, if any
        $disk = Storage::disk(config('filesystems.default'));
        if ($disk->exists($subfolder->filepath . '/test.png')) {
            $disk->delete($subfolder->filepath . '/test.png');
        }

        Livewire::test(MediaLibraryTable::class)
            ->call('setParent', $subfolder->id)
            ->callTableAction('upload', null, [
                'upload_media' => UploadedFile::fake()->image('test.png'),
                'alt_text' => 'Test Upload'
            ]);

        $newFile = Media::where('filename', 'test.png')->first();
        $this->assertNotNull($newFile);
    }
}
