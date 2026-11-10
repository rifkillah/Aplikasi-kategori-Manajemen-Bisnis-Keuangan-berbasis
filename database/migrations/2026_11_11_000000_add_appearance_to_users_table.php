<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('theme_mode', 16)->default('system')->after('is_active');
            $table->string('background_preset', 32)->default('aurora')->after('theme_mode');
            $table->string('background_image')->nullable()->after('background_preset');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['theme_mode', 'background_preset', 'background_image']);
        });
    }
};
