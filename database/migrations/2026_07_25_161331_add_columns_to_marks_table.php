<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('marks', function (Blueprint $table) {
            $table->foreignId('term_id')
                  ->nullable()
                  ->after('subject_id')
                  ->constrained('terms')
                  ->onDelete('cascade');

            $table->foreignId('grade_id')
                  ->nullable()
                  ->after('term_id')
                  ->constrained('grades')
                  ->onDelete('cascade');

            $table->decimal('marks_obtained', 8, 2)
                  ->default(0)
                  ->after('grade_id');

            $table->decimal('total_marks', 8, 2)
                  ->default(100)
                  ->after('marks_obtained');

            $table->string('grade_letter', 5)
                  ->nullable()
                  ->after('total_marks');
        });
    }

    public function down(): void
    {
        Schema::table('marks', function (Blueprint $table) {
            $table->dropForeign(['term_id']);
            $table->dropForeign(['grade_id']);
            $table->dropColumn(['term_id', 'grade_id', 'marks_obtained', 'total_marks', 'grade_letter']);
        });
    }
};