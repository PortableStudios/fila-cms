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
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->boolean('is_folder');
            $table->string('filename')->nullable();
            $table->string('filepath')->nullable();
            $table->string('mime_type')->nullable();
            $table->string('size')->nullable();
            $table->string('disk')->nullable();
            $table->string('extension')->nullable();
            $table->string('alt_text')->nullable();
            $table->integer('width')->nullable();
            $table->integer('height')->nullable();

            $table->json('meta')->nullable();
            $table->foreignId('user_id')->nullable()->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
