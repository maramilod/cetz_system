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
        Schema::create('project_teachers', function (Blueprint $table) {
    $table->id();
    $table->foreignId('graduation_project_id')->constrained();
    $table->foreignId('teacher_id')->constrained();
    $table->enum('role', ['supervisor', 'examiner']);
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_teachers');
    }
};
