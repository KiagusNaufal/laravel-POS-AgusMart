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
        Schema::create('detail_pembelian', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_pembelian')->nullable();
            $table->foreign('id_pembelian')->references('id')->on('pembelian');
            $table->unsignedBigInteger('id_barang')->nullable();
            $table->foreign('id_barang')->references('id')->on('barang');
            $table->double('harga_beli')->nullable();
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
        Schema::dropIfExists('detail_pembelian');
    }
};
