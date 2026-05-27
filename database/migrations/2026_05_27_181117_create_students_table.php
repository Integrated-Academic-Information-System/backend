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
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('reg_no', 15)->unique();
            $table->string('password', 512); // VARCHAR(512) for hashed passwords
            $table->string('name', 60);
            $table->string('address', 45)->nullable();
            $table->date('reg_date');
            $table->date('leave_date')->nullable();
            $table->string('mobile_number', 11)->nullable();
            $table->string('email', 45)->unique();
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
