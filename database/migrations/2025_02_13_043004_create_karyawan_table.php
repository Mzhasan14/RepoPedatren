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
        Schema::create('karyawan', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('id_pegawai')->unique();
            $table->unsignedBigInteger('id_golongan');
            $table->string('jabatan')->nullable();// kulturan, tetap, kontrak, pengkaderan 
            $table->unsignedBigInteger('created_by');
            $table->boolean('status');
            $table->timestamps();

            $table->foreign('id_pegawai')->references('id')->on('pegawai')->onDelete('cascade');
            $table->foreign('id_golongan')->references('id')->on('golongan')->onDelete('cascade');
        });
        Schema::create('pengurus', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('id_pegawai');
            $table->unsignedBigInteger('id_golongan');
            $table->string('satuan_kerja');
            $table->string('jabatan')->nullable(); // kulturan, tetap, kontrak, pengkaderan 
            $table->string('keterangan_jabatan'); // contohnya : pengasuh, ketua dewan pengasuh
            $table->date('tahun_masuk');
            $table->date('tahun_keluar')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->boolean('status');
            $table->timestamps();

            $table->foreign('id_pegawai')->references('id')->on('pegawai')->onDelete('cascade');
            $table->foreign('id_golongan')->references('id')->on('golongan')->onDelete('cascade');
        });
        Schema::create('riwayat_jabatan_karyawan', function (Blueprint $table) {
            $table->id();
            $table->uuid('id_karyawan'); // Relasi ke tabel karyawan
            $table->string('keterangan_jabatan'); // contohnya : kepala sekolah, wakil kepala bag --- dll
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai')->nullable(); // NULL jika masih menjabat
            $table->boolean('status');
            $table->timestamps();
        
            $table->foreign('id_karyawan')->references('id')->on('karyawan')->onDelete('cascade');
        });        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('karyawan');
        Schema::dropIfExists('pengurus');
    }
};
