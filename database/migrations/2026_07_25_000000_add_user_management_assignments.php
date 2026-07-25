<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            $table->boolean('is_class_teacher')->default(false)->after('role_status');
            $table->boolean('is_subject_teacher')->default(false)->after('is_class_teacher');
        });

        Schema::create('teacher_subject_grade', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('grade_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['teacher_id', 'subject_id', 'grade_id']);
        });

        Schema::create('student_has_subject', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['student_id', 'subject_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_has_subject');
        Schema::dropIfExists('teacher_subject_grade');
        Schema::table('teachers', function (Blueprint $table) {
            $table->dropColumn(['is_class_teacher', 'is_subject_teacher']);
        });
    }
};
