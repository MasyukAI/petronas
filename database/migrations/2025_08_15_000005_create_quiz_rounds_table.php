<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quiz_rounds', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('status')->default('waiting');
            $table->string('phase_name')->default('lobby');
            $table->unsignedSmallInteger('current_question')->nullable();
            $table->json('questions');
            $table->unsignedTinyInteger('question_count');
            $table->json('timings');
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_rounds');
    }
};
