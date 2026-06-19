<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hazard_questions', function (Blueprint $table) {
            $table->id();
            $table->string('scene_id')->unique();
            $table->string('title');
            $table->string('image');
            $table->string('source');
            $table->text('question');
            $table->json('options');
            $table->unsignedTinyInteger('answer');
            $table->text('explanation');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hazard_questions');
    }
};
