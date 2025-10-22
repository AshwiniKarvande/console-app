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
        Schema::create('nav_histories', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->foreignId('scheme_id')->references('id')->on('schemes')->onDelete('cascade');
            $table->decimal('nav');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nav_histories');
    }
};
