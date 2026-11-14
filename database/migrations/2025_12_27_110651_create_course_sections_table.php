<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('course_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_offering_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->string('section_name'); // A / B / C
            $table->integer('capacity')->nullable();
            $table->timestamps();

            $table->unique(['course_offering_id', 'section_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_sections');
    }
};
