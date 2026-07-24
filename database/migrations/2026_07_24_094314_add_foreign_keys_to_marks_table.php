<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('marks', function (Blueprint $table) {
            $table->unsignedBigInteger('student_id')->after('id');
            $table->unsignedBigInteger('subject_id')->after('student_id');

            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('subject_id')->references('id')->on('subjects')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('marks', function (Blueprint $table) {
            $table->dropForeign(['student_id']);
            $table->dropForeign(['subject_id']);
            $table->dropColumn(['student_id', 'subject_id']);
        });
    }
};
