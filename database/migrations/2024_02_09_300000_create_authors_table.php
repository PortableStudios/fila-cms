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
        Schema::create('authors', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->boolean('is_individual')->default(true);
            $table->timestamps();
            $table->index('first_name');
            $table->index('last_name');
            $table->index('is_individual');
            $table->string('display_name')->virtualAs('if(is_individual, concat(first_name, " ", last_name), first_name)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('authors');
    }
};
