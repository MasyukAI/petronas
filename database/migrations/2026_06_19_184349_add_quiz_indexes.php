<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('score_attempts', function (Blueprint $table) {
            $table->index('game_code');
        });

        Schema::table('hazard_sessions', function (Blueprint $table) {
            $table->index('scorecard_id');
            $table->index('status');
        });

        Schema::table('quiz_rounds', function (Blueprint $table) {
            $table->index('status');
        });

        Schema::table('quiz_players', function (Blueprint $table) {
            $table->index('scorecard_id');
        });
    }

    public function down(): void
    {
        Schema::table('score_attempts', function (Blueprint $table) {
            $table->dropIndex(['game_code']);
        });

        Schema::table('hazard_sessions', function (Blueprint $table) {
            $table->dropIndex(['scorecard_id']);
            $table->dropIndex(['status']);
        });

        Schema::table('quiz_rounds', function (Blueprint $table) {
            $table->dropIndex(['status']);
        });

        Schema::table('quiz_players', function (Blueprint $table) {
            $table->dropIndex(['scorecard_id']);
        });
    }
};
