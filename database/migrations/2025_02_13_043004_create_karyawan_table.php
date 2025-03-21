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
            $table->unsignedBigInteger('id_pegawai')->unique();
            $table->unsignedBigInteger('id_golongan');
            $table->text('keterangan');
            $table->string('jabatan');
            $table->unsignedBigInteger('created_by');
            $table->boolean('status');
            $table->timestamps();

            $table->foreign('id_pegawai')->references('id')->on('pegawai')->onDelete('cascade');
            $table->foreign('id_golongan')->references('id')->on('golongan')->onDelete('cascade');
        });
        Schema::create('pengurus', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_pegawai');
            $table->unsignedBigInteger('id_golongan');
            $table->string('satuan_kerja');
            $table->string('jabatan'); // kulturan, tetap, kontrak, pengkaderan 
            $table->string('keterangan_jabatan'); // contohnya : pengasuh, ketua dewan pengasuh
            $table->unsignedBigInteger('created_by');
            $table->boolean('status');
            $table->timestamps();

            $table->foreign('id_pegawai')->references('id')->on('pegawai')->onDelete('cascade');
            $table->foreign('id_golongan')->references('id')->on('golongan')->onDelete('cascade');
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
