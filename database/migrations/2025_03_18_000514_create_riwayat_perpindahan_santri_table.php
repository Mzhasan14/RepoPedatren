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
        Schema::create('riwayat_perpindahan_santri', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_peserta_didik');
            $table->unsignedBigInteger('id_wilayah_old');
            $table->unsignedBigInteger('id_wilayah_new');
            $table->unsignedBigInteger('id_kamar_old');
            $table->unsignedBigInteger('id_kamar_new');
            $table->unsignedBigInteger('id_domisili_old');
            $table->unsignedBigInteger('id_domisili_new');
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
        Schema::dropIfExists('riwayat_perpindahan_santri');
    }
};
