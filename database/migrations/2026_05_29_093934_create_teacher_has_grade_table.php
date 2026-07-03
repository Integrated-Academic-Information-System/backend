<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Assign teachers to grades.
     *
     * A teacher can teach multiple grades.
     */
    public function up(): void
    {
        Schema::create('teacher_has_grade', function (Blueprint $table) {

            $table->id();

            $table->foreignId('teacher_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('grade_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->timestamps();

            $table->unique([
                'teacher_id',
                'grade_id'
            ]);

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_has_grade');
    }
};
