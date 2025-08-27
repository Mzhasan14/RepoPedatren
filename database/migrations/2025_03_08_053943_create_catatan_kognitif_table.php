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
        Schema::create('catatan_kognitif', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_santri');
            $table->unsignedBigInteger('id_wali_asuh')->nullable();
            $table->enum('kebahasaan_nilai', ['A', 'B', 'C', 'D', 'E']);
            $table->text('kebahasaan_tindak_lanjut');
            $table->enum('baca_kitab_kuning_nilai', ['A', 'B', 'C', 'D', 'E']);
            $table->text('baca_kitab_kuning_tindak_lanjut');
            $table->enum('hafalan_tahfidz_nilai', ['A', 'B', 'C', 'D', 'E']);
            $table->text('hafalan_tahfidz_tindak_lanjut');
            $table->enum('furudul_ainiyah_nilai', ['A', 'B', 'C', 'D', 'E']);
            $table->text('furudul_ainiyah_tindak_lanjut');
            $table->enum('tulis_alquran_nilai', ['A', 'B', 'C', 'D', 'E']);
            $table->text('tulis_alquran_tindak_lanjut');
            $table->enum('baca_alquran_nilai', ['A', 'B', 'C', 'D', 'E']);
            $table->text('baca_alquran_tindak_lanjut');
            $table->date('tanggal_buat');
            $table->date('tanggal_selesai')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->boolean('status');
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('id_santri')->references('id')->on('santri')->onDelete('cascade');
            $table->foreign('id_wali_asuh')->references('id')->on('wali_asuh')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('catatan_kognitif');
    }
};
