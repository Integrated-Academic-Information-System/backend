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
        Schema::create('student_has_marks', function (Blueprint $table) {
        $table->id();
        $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
        $table->string('student_reg_no', 45);
        $table->foreignId('marks_id')->constrained('marks')->onDelete('cascade');
        
        // Linking to grade, subject, term, and exam year for better context
        $table->foreignId('grade_id')->constrained('grades')->onDelete('cascade');
        
        $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
        $table->foreignId('term_id')->constrained('terms')->onDelete('cascade');
        $table->foreignId('exam_year_id')->constrained('exam_years')->onDelete('cascade');
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_has_marks');
    }
};
