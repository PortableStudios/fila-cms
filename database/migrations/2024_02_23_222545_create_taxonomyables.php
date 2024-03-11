<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('taxonomyables', function (Blueprint $table) {
            $table->foreignId('taxonomy_term_id')->references('id')->on('taxonomy_terms')->cascadeOnDelete();
            $table->foreignId('taxonomyable_id');
            $table->string('taxonomyable_type');
            $table->timestamps();

            $table->index(['taxonomy_term_id', 'taxonomyable_id', 'taxonomyable_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('taxonomyables');
    }
};
