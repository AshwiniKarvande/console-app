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
        Schema::create('fund_aum_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fund_id')->references('id')->on('funds')->onDelete('cascade');
            $table->date('start_date');
            $table->decimal('total_aum', 12,2);

            $table->unique(['fund_id', 'start_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fund_aum_histories');
    }
};
