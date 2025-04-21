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
            $table->unsignedBigInteger('wali_asuh_id');
            $table->string('pembuat');
            $table->string('biktren');
            $table->unsignedBigInteger('kamtib');
            $table->text('alasan_izin');
            $table->text('alamat_tujuan');
            $table->datetime('tanggal_mulai');
            $table->datetime('tanggal_akhir');
            $table->enum('jenis_izin', ['Personal', 'Rombongan']);
            $table->enum('status_izin', ['sedang proses izin', 'perizinan diterima', 'sudah berada diluar pondok', 'perizinan ditolak', 'dibatalkan']);
            $table->enum('status_kembali', ['telat', 'telat(sudah kembali)', 'telat(belum kembali)', 'kembali tepat waktu'])->nullable();
            $table->text('keterangan');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->boolean('status');
            $table->timestamps();

            $table->foreign('santri_id')->references('id')->on('santri')->onDelete('cascade');
            $table->foreign('wali_asuh_id')->references('id')->on('wali_asuh')->onDelete('cascade');
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
