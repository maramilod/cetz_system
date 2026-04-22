<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('enrollments', function (Blueprint $table) {
    $table->id();

    $table->foreignId('student_id')
          ->constrained()
          ->cascadeOnDelete();

    $table->foreignId('course_offering_id')->constrained()->onDelete('cascade');
// الآن لا حاجة لـ semester أو academic_year أو department_id، كلها موجودة في course_offerings


    // محاولة رقم كم (1 أول مرة، 2 إعادة، 3…)
    $table->unsignedTinyInteger('attempt')->default(1);

    // حالة المادة
    $table->enum('status', [
        'in_progress',
        'passed',
        'failed',
        'withdrawn'
    ])->default('in_progress');

    // تاريخ النتيجة (نجاح / رسوب)
    $table->date('result_date')->nullable();

    $table->timestamps();

    // منع تكرار نفس المحاولة
$table->unique([
    'student_id',
    'course_offering_id',
    'attempt'
]);

});

    }

    public function down(): void
    {
        Schema::dropIfExists('enrollments');
    }
};
