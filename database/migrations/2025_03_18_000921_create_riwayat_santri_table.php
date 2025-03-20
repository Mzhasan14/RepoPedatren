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
        Schema::create('riwayat_santri', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_peserta_didik');
            $table->unsignedBigInteger('id_wilayah')->nullable();
            $table->unsignedBigInteger('id_blok')->nullable();
            $table->unsignedBigInteger('id_kamar')->nullable();
            $table->unsignedBigInteger('id_domisili')->nullable();
            $table->string('nis', 11)->unique();
            $table->year('angkatan');
            $table->date('tanggal_masuk');
            $table->date('tanggal_keluar')->nullable();
            $table->enum('status', ['alumni', 'mutasi', 'do', 'berhenti']);
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
           

            $table->foreign('id_wilayah')->references('id')->on('wilayah')->onDelete('cascade');
            $table->foreign('id_blok')->references('id')->on('blok')->onDelete('cascade');
            $table->foreign('id_kamar')->references('id')->on('kamar')->onDelete('cascade');
            $table->foreign('id_domisili')->references('id')->on('domisili')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('deleted_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('id_peserta_didik')->references('id')->on('peserta_didik')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('riwayat_santri');
    }
};
