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
        Schema::create('grade_has_subject', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grade_has_sub_grade_id')
                ->constrained('grade_has_sub_grade')
                ->onDelete('cascade');
            $table->foreignId('subject_id')
                ->constrained('subjects')
                ->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grade_has_subject');
    }
};
