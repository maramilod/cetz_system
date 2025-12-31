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
     Schema::create('teaching_assignments', function (Blueprint $table) {
    $table->id();

    $table->foreignId('teacher_id')->constrained();
    $table->foreignId('course_offering_id')->constrained();

    $table->enum('role', ['theoretical', 'practical', 'assistant']);
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teaching_assignments');
    }
};
