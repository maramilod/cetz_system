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
    Schema::create('teacher_ranks', function (Blueprint $table) {
    $table->id();
    $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
    $table->foreignId('academic_rank_id')->constrained();
    $table->date('from_date');
    $table->date('to_date')->nullable();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_ranks');
    }
};
