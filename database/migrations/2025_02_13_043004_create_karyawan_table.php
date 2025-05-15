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
            $table->id();
            $table->unsignedBigInteger('pegawai_id');
            $table->unsignedBigInteger('golongan_jabatan_id')->nullable();
            $table->unsignedBigInteger('lembaga_id')->nullable();
            $table->string('jabatan')->nullable();// kulturan, tetap, kontrak, pengkaderan 
            $table->string('keterangan_jabatan'); // contohnya : kepala sekolah, wakil kepala bag --- dll
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai')->nullable(); 
            $table->enum('status_aktif', ['aktif', 'tidak aktif'])->default('aktif');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('pegawai_id')->references('id')->on('pegawai')->onDelete('cascade');
            $table->foreign('golongan_jabatan_id')->references('id')->on('golongan_jabatan')->onDelete('cascade');
            $table->foreign('lembaga_id')->references('id')->on('lembaga')->onDelete('cascade');
        });
        Schema::create('pengurus', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pegawai_id');
            $table->unsignedBigInteger('golongan_jabatan_id')->nullable();
            $table->string('jabatan')->nullable(); // kulturan, tetap, kontrak, pengkaderan 
            $table->string('satuan_kerja');
            $table->string('keterangan_jabatan'); // contohnya : pengasuh, ketua dewan pengasuh
            $table->date('tanggal_mulai');
            $table->date('tanggal_akhir')->nullable();
            $table->enum('status_aktif', ['aktif', 'tidak aktif'])->default('aktif');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('pegawai_id')->references('id')->on('pegawai')->onDelete('cascade');
            $table->foreign('golongan_jabatan_id')->references('id')->on('golongan_jabatan')->onDelete('cascade');
        });
        // Schema::create('riwayat_jabatan_karyawan', function (Blueprint $table) {
        //     $table->id();
        //     $table->uuid('karyawan_id'); 
        //     $table->string('keterangan_jabatan'); // contohnya : kepala sekolah, wakil kepala bag --- dll
        //     $table->date('tanggal_mulai');
        //     $table->date('tanggal_selesai')->nullable(); // NULL jika masih menjabat
        //     $table->boolean('status');
        //     $table->unsignedBigInteger('created_by');
        //     $table->unsignedBigInteger('updated_by')->nullable();
        //     $table->timestamps();
        
        //     $table->foreign('karyawan_id')->references('id')->on('karyawan')->onDelete('cascade');
        // });       
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
