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
        Schema::table('user_sso_links', function (Blueprint $table) {
            $table->text('provider_token')->change();
            $table->text('provider_refresh_token')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_sso_links', function (Blueprint $table) {
            $table->string('provider_token')->change();
            $table->string('provider_refresh_token')->nullable()->change();
        });
    }
};
