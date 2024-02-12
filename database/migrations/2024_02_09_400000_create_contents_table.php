<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('contents', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug');
            $table->boolean('is_draft')->default(TRUE);
            $table->timestamp('publish_at')->nullable();
            $table->timestamp('expire_at')->nullable();
            $table->longText('contents');

            $table->foreignId('created_user_id')->constrained('users');
            $table->foreignId('updated_user_id')->constrained('users');
            $table->foreignId('author_id')->nullable()->constrained('authors');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contents');
    }
};
