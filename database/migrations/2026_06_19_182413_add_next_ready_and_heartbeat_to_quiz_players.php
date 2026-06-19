<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quiz_players', function (Blueprint $table) {
            $table->boolean('next_ready')->default(false)->after('ready');
            $table->timestamp('last_heartbeat')->nullable()->after('next_ready');
        });
    }

    public function down(): void
    {
        Schema::table('quiz_players', function (Blueprint $table) {
            $table->dropColumn(['next_ready', 'last_heartbeat']);
        });
    }
};
