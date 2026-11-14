<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::create('course_grading_rules', function (Blueprint $table) {

            $table->id();

            $table->foreignId('course_id')
                  ->constrained()
                  ->cascadeOnDelete();

            // ===== الجزء النظري (إجباري) =====
            $table->unsignedTinyInteger('theory_work_ratio');
            $table->unsignedTinyInteger('theory_midterm_ratio');
            $table->unsignedTinyInteger('theory_final_ratio');

            // ===== الجزء العملي (اختياري) =====
            $table->unsignedTinyInteger('practical_work_ratio')->nullable();
            $table->unsignedTinyInteger('practical_midterm_ratio')->nullable();
            $table->unsignedTinyInteger('practical_final_ratio')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_grading_rules');
    }
};
