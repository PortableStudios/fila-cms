<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Portable\FilaCms\Models\Taxonomy;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('taxonomies', function (Blueprint $table) {
            $table->string('code')->nullable()->after('name');
        });

        // Populate codes
        Taxonomy::all()->each(function ($taxonomy) {
            $taxonomy->update([
                'code' => Str::slug($taxonomy->name),
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('taxonomies', function (Blueprint $table) {
            $table->dropColumn('code');
        });
    }
};
