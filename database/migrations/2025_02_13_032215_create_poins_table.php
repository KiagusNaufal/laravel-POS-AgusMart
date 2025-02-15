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
        Schema::create('poin', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_member')->nullable();
            $table->foreign('id_member')->references('id')->on('member');
            $table->unsignedBigInteger('id_penjualan')->nullable();
            $table->foreign('id_penjualan')->references('id')->on('penjualan');
            $table->double('poin_didapat')->nullable();
            $table->double('total_poin')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('poin');
    }
};
