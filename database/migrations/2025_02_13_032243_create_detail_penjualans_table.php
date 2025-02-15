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
        Schema::create('detail_penjualan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_penjualan')->nullable();
            $table->foreign('id_penjualan')->references('id')->on('penjualan');
            $table->unsignedBigInteger('id_barang')->nullable();
            $table->foreign('id_barang')->references('id')->on('barang');
            $table->double('harga_jual')->nullable();
            $table->integer('jumlah')->nullable();
            $table->double('sub_total')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_penjualan');
    }
};
