<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('words', function (Blueprint $table) {
            $table->id();
            $table->string('word');
            $table->text('definition');
            $table->string('word_type'); // "wordType" mapped to snake_case
            $table->text('example_sentence')->nullable();
            $table->string('phonetic_spelling')->nullable();
            $table->boolean('is_irregular')->default(false);
            $table->string('past_participle')->nullable();
            $table->json('conjugations')->nullable(); // Store conjugations as JSON
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('words');
    }
};
