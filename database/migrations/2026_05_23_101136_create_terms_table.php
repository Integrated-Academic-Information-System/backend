<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('terms', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g. "Mid-Term Assessment"
            $table->integer('order'); // 1, 2, 3
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('terms');
    }
};