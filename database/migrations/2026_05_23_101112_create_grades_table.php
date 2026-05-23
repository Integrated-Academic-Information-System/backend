<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('grades', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g. "Grade 7"
            $table->integer('level'); // e.g. 7
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('grades');
    }
};