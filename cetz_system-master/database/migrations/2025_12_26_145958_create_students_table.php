<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();

            $table->string('full_name');

            // ✅ التغيير هنا: الجنسية اختيارية (منطقك صحيح)
            $table->string('nationality')->nullable();

            $table->enum('gender', ['ذكر', 'انثى']);

            $table->foreignId('department_id')
                  ->constrained()
                  ->cascadeOnDelete();

            // بعض الطلاب بدون شعبة
            $table->foreignId('section_id')
                  ->nullable()
                  ->constrained()
                  ->nullOnDelete();

            $table->enum('academic_term', ['خريفي', 'ربيعي']);


            // الرقم الجامعي فريد وإجباري
            $table->string('student_number')->unique();

            $table->string('manual_number')->nullable();

            // الرقم الوطني: غير إلزامي لكن فريد إذا وُجد
            $table->string('national_id')
                  ->nullable()
                  ->unique();

            $table->string('passport_number')->nullable();

            $table->date('dob')->nullable();

            $table->string('bank_name')->nullable();
            $table->string('account_number')->nullable();

            $table->string('mother_name')->nullable();
            $table->string('family_record')->nullable(); // قيد الكتيب (غير فريد)

            $table->string('photo')->nullable();

            $table->year('registration_year')->nullable();



            // ✅ إضافة مهمة لدعم النظام الأكاديمي
            $table->enum('current_status', [
                'active',
                'suspended',
                'withdrawn',
                'graduated'
            ])->default('active');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
