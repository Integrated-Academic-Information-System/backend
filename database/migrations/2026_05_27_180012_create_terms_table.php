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
        Schema::create('terms', function (Blueprint $table) {
            $table->id(); // from this create auto-incrementing 'id' INT PRIMARY KEY
            $table->string('name'); // name VARCHAR(255 by default)
            $table->timestamps(); // Laravel default  for create_time, update_time TIMESTAMP  
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('terms');
    }
};
