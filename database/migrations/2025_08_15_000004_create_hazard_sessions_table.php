<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hazard_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('participant_id')->nullable()->constrained()->nullOnDelete();
            $table->string('scorecard_id');
            $table->string('status')->default('active');
            $table->unsignedTinyInteger('correct_count')->default(0);
            $table->unsignedSmallInteger('elapsed_seconds')->nullable();
            $table->unsignedSmallInteger('score')->nullable();
            $table->json('answers')->nullable();
            $table->json('questions')->nullable();
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hazard_sessions');
    }
};
