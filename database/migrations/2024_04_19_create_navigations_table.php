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
        Schema::create('navigations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type');
            $table->json('reference');
            $table->unsignedInteger('order')->default(1);
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::table('navigations', function (Blueprint $table) {
            $table->foreignId('parent_id')->nullable()->constrained('navigations');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('navigations', function (Blueprint $table) {
            $table->dropForeign('navigations_parent_id_foreign');
        });
        Schema::dropIfExists('navigations');
    }
};
