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
        // No-op: admins table already defines its primary key in the create migration.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
