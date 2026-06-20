<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            // Adds the class ID to the student and links it to the grade_has_sub_grade table
            $table->foreignId('grade_has_sub_grade_id')->nullable()->constrained('grade_has_sub_grade')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropForeign(['grade_has_sub_grade_id']);
            $table->dropColumn('grade_has_sub_grade_id');
        });
    }
};