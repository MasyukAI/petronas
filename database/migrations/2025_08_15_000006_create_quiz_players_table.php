<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quiz_players', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_round_id')->constrained()->cascadeOnDelete();
            $table->foreignId('participant_id')->nullable()->constrained()->nullOnDelete();
            $table->string('scorecard_id');
            $table->string('name');
            $table->boolean('ready')->default(false);
            $table->json('answers')->nullable();
            $table->unsignedTinyInteger('score')->default(0);
            $table->unsignedTinyInteger('correct_count')->default(0);
            $table->unsignedTinyInteger('answered_count')->default(0);
            $table->string('player_token')->nullable()->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_players');
    }
};
