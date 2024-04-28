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
        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type');
            $table->json('reference');
            $table->unsignedInteger('order')->default(1);
            $table->foreignId('menu_id')->nullable()->constrained('menus');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::table('menu_items', function (Blueprint $table) {
            $table->foreignId('parent_id')->nullable()->constrained('menu_items');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            $table->dropForeign('menu_items_parent_id_foreign');
        });
        Schema::dropIfExists('menu_items');
    }
};