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
        Schema::create('absensi_kerja', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable(); // siapa yang melakukan
            $table->date('tanggal_masuk')->nullable(); // tanggal masuk
            $table->time('waktu_masuk')->nullable(); // jam masuk
            $table->enum('status', ['Masuk', 'Cuti', 'Sakit'])->default('Masuk');
            $table->time('waktu_akhir_kerja')->nullable(); // jam keluar
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('absensi_kerjas');
    }
};
