<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('course_offerings', function (Blueprint $table) {
            $table->id();

            // المادة
            $table->foreignId('course_id')
                  ->constrained()
                  ->cascadeOnDelete();

            // القسم
            $table->foreignId('department_id')
                  ->constrained()
                  ->cascadeOnDelete();

            // الشعبة (اختياري)
            $table->foreignId('section_id')
                  ->nullable()
                  ->constrained()
                  ->nullOnDelete();

            // رقم السيمستر
            $table->unsignedTinyInteger('semester_number');

            // السنة الأكاديمية
            $table->year('academic_year');


            // هل الطرح نشط
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            // منع تكرار نفس الطرح
            $table->unique([
    'course_id',
    'department_id',
    'section_id',
    'semester_number',
    'academic_year'
], 'unique_course_offering');

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_offerings');
    }
};
