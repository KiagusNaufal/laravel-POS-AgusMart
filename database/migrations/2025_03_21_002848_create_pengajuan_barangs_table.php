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
        Schema::create('pengajuan_barang', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_member')->nullable();
            $table->foreign('id_member')->references('id')->on('member')->onDelete('set null');
            $table->string('nama_barang', 100)->nullable();
            $table->date('tanggal_pengajuan')->nullable();
            $table->integer('jumlah')->nullable();
            $table->integer('terpenuhi')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengajuan_barang');
    }
};
