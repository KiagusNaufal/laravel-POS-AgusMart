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
        Schema::table('logs', function (Blueprint $table) {
            // Menambahkan kolom baru ke tabel logs
            $table->unsignedBigInteger('user_id')->nullable(); // siapa yang melakukan
            $table->string('action'); // misal: login, update, delete
            $table->text('message')->nullable(); // detail aktivitas
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('logs', function (Blueprint $table) {
            // Menghapus kolom yang ditambahkan pada metode up
            $table->dropColumn(['user_id', 'action', 'message', 'ip_address', 'user_agent']);
        });
    }
};
