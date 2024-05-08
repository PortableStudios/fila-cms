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
        Schema::create('user_sso_links', function (Blueprint $table) {
            $table->id();
            $table->string('driver');
            $table->foreignId('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->string('provider_id');
            $table->string('provider_token');
            $table->string('provider_refresh_token');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_sso_links');
    }
};
