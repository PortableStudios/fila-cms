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
        Schema::create('short_urls', function (Blueprint $table) {
            $table->id();
            $table->string('url')->unique()->index();
            $table->unsignedBigInteger('shortable_id');
            $table->string('shortable_type');
            $table->string('redirect_status')->default('301');
            $table->boolean('enable')->default(true);
            $table->unsignedBigInteger('hits')->nullable()->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('short_urls');
    }
};
