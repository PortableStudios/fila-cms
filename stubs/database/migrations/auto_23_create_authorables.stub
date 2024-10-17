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
        Schema::create('authorables', function (Blueprint $table) {
            $table->foreignId('author_id')->references('id')->on('authors')->cascadeOnDelete();
            $table->foreignId('authorable_id');
            $table->string('authorable_type');
            $table->timestamps();

            $table->index(['author_id', 'authorable_id', 'authorable_type'], 'authorable_search_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('authorables');
    }
};
