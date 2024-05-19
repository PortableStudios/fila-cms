<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('taxonomy_terms', function (Blueprint $table) {
            $table->foreignId('parent_id')->nullable()->constrained('taxonomy_terms');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('taxonomy_terms', function (Blueprint $table) {
            if(config('database.default') !== 'sqlite') {
                $table->dropForeign('taxonomy_terms_parent_id_foreign');
            }
        });
    }
};
