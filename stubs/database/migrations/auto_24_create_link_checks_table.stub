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
        Schema::create('link_checks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('origin_resource');
            $table->string('edit_url');
            $table->string('url');
            $table->unsignedInteger('status_code');
            $table->string('status_text')->nullable();
            $table->decimal('timeout', total:12, places: 8)->default(0);
            $table->string('batch_id');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('link_checks');
    }
};
