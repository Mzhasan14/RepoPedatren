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
        Schema::create('riwayat_perpindahan_pelajar', function (Blueprint $table) {
            $table->unsignedBigInteger('id_peserta_didik');
            $table->unsignedBigInteger('id_kelas_old');
            $table->unsignedBigInteger('id_kelas_new');
            $table->unsignedBigInteger('id_jurusan_old');
            $table->unsignedBigInteger('id_jurusan_new');
            $table->unsignedBigInteger('id_lembaga_old');
            $table->unsignedBigInteger('id_lembaga_new');
            $table->unsignedBigInteger('id_rombel_old');
            $table->unsignedBigInteger('id_rombel_new');
            $table->date('tanggal_pindah');
            $table->text('alasan')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('id_peserta_didik')->references('id')->on('peserta_didik')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('riwayat_perpindahan_pelajar');
    }
};
