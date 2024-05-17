<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Portable\FilaCms\Models\Page;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Page::withoutGlobalScopes()->get()->each(function (Page $page) {
            if($page->author_id) {
                $page->authors()->sync($page->author_id);
            }
        });

        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn('author_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->foreignId('author_id')->nullable();
        });

        Page::withoutGlobalScopes()->get()->each(function (Page $page) {
            if($page->authors()->count() > 0) {
                $page->author_id = $page->authors->first()->id;
            }
        });
    }
};
