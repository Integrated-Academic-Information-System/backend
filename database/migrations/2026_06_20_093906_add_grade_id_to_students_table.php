<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            // The grade_id is newly added and linked to the grades table.
            $table->foreignId('grade_id')->nullable()->constrained('grades')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropForeign(['grade_id']);
            $table->dropColumn('grade_id');
        });
    }
};