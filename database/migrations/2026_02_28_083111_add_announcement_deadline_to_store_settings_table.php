<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('store_settings', function (Blueprint $table) {
            $table->date('announcement_deadline')->nullable()->after('announcement_link');
        });
    }

    public function down(): void
    {
        Schema::table('store_settings', function (Blueprint $table) {
            $table->dropColumn('announcement_deadline');
        });
    }
};
