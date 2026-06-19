<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('score_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('participant_id')->constrained()->cascadeOnDelete();
            $table->string('game_code');
            $table->string('raw_result');
            $table->unsignedSmallInteger('calculated_score');
            $table->string('source'); // manual, hazard_hunt, quickfire
            $table->string('status')->default('approved');
            $table->json('breakdown')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('score_attempts');
    }
};
