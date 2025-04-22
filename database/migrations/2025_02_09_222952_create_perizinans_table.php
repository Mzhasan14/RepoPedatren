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
        Schema::create('perizinan', function (Blueprint $table) {
            $table->id();
            $table->uuid('santri_id');
            $table->unsignedBigInteger('pengasuh');
            $table->unsignedBigInteger('biktren');
            $table->unsignedBigInteger('kamtib');
            $table->text('alasan_izin');
            $table->text('alamat_tujuan');
            $table->datetime('tanggal_mulai');
            $table->datetime('tanggal_akhir');
            $table->integer('lama_izin');
            $table->datetime('tanggal_kembali')->nullable();
            $table->enum('jenis_izin', ['Personal', 'Rombongan']);
            $table->enum('status_izin', ['sedang proses izin', 'perizinan diterima', 'sudah berada diluar pondok', 'perizinan ditolak', 'dibatalkan']);
            $table->enum('status_kembali', ['telat', 'telat(sudah kembali)', 'telat(belum kembali)', 'kembali tepat waktu'])->nullable();
            $table->text('keterangan');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('santri_id')->references('id')->on('santri')->onDelete('cascade');
            $table->foreign('pengasuh')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('biktren')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('kamtib')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('perizinan');
    }
};
