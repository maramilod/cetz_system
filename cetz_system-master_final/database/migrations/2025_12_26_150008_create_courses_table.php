<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
       Schema::create('courses', function (Blueprint $table) {
    $table->id();

    $table->string('name');

    // هل للمادة عملي
    $table->boolean('has_practical')->default(false);

    // مادة متطلب سابق
    $table->foreignId('prerequisite_course_id')
          ->nullable()
          ->constrained('courses')
          ->nullOnDelete();

    // هل المادة مشروع / تدريب
    $table->enum('course_type', [
        'normal',
        'graduation_project',
        'internship'
    ])->default('normal');

    $table->timestamps();
});

    }

    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
