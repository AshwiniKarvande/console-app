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
        Schema::create('scheme_aum_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scheme_id')->references('id')->on('schemes')->onDelete('cascade');
            $table->date('start_date');//strdtAUM
            $table->decimal('aum', 12, 2);

            $table->unique(['scheme_id', 'start_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scheme_aum_histories');
    }
};
