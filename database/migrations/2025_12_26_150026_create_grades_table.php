<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grades', function (Blueprint $table) {
            $table->id();
            
            // ربط الدرجة بالطالب
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');

            // ربط الدرجة بالمادة والتسجيل
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->foreignId('enrollment_id')->nullable()->constrained('enrollments')->onDelete('cascade');

            // درجات المادة
            $table->decimal('midterm', 5, 2)->nullable();       // امتحان نصفي
            $table->decimal('final', 5, 2)->nullable();         // امتحان نهائي
            $table->decimal('assignment', 5, 2)->nullable();    // اعمال
            $table->decimal('practical', 5, 2)->nullable();     // عملي إذا موجود
            $table->decimal('total', 5, 2)->nullable();         // المجموع النهائي

            // نوع الطالب (عادي، تكميلي) - اختياري
            $table->string('student_type')->nullable();

            // الدور الثاني
            $table->boolean('is_second_chance')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grades');
    }
};
