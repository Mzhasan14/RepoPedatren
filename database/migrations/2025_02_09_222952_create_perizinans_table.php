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
            $table->unsignedBigInteger('santri_id');
            $table->unsignedBigInteger('pengasuh_id')->nullable();
            $table->unsignedBigInteger('biktren_id')->nullable();
            $table->unsignedBigInteger('kamtib_id')->nullable();
            $table->boolean('approved_by_biktren')->default(false);
            $table->boolean('approved_by_kamtib')->default(false);
            $table->boolean('approved_by_pengasuh')->default(false);
            $table->text('alasan_izin');
            $table->text('alamat_tujuan');
            $table->datetime('tanggal_mulai');
            $table->datetime('tanggal_akhir');
            $table->datetime('tanggal_kembali')->nullable();
            $table->enum('jenis_izin', ['Personal', 'Rombongan']);
            $table->enum('status', [
                'sedang proses izin',
                'perizinan diterima',
                'sudah berada diluar pondok',
                'perizinan ditolak',
                'dibatalkan',
                'telat(sudah kembali)',
                'telat(belum kembali)',
                'kembali tepat waktu'
            ]);
            $table->text('keterangan');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('pengantar_id')->references('id')->on('orang_tua_wali')->onDelete('cascade');
            $table->foreign('santri_id')->references('id')->on('santri')->onDelete('cascade');
            $table->foreign('pengasuh_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('biktren_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('kamtib_id')->references('id')->on('users')->onDelete('cascade');
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
