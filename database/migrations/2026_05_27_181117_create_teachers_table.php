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
        Schema::create('teachers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 60);
            $table->string('user_name')->unique();   
            $table->string('password');              
            $table->string('mobile_number', 12)->nullable();
            $table->string('email', 50)->unique();
            $table->tinyInteger('access_status')->default(1);
            $table->tinyInteger('role_status')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teachers');
    }
};
