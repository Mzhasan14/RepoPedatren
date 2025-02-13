<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use League\CommonMark\Reference\Reference;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('kategori_golongan', function (Blueprint $table) {
            $table->id();
            $table->string('nama_kategori_golongan');
            $table->unsignedBigInteger('created_by');
            $table->boolean('status');
            $table->timestamps();
        });
        Schema::create('golongan', function (Blueprint $table) {
            $table->id();
            $table->string('nama_golongan');
            $table->unsignedBigInteger('id_kategori_golongan');
            $table->unsignedBigInteger('created_by');
            $table->boolean('status');
            $table->timestamps();

            $table->foreign('id_kategori_golongan')->references('id')->on('kategori_golongan')->onDelete('cascade');
        });
        Schema::create('entitas_pegawai', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_pegawai');
            $table->unsignedBigInteger('id_golongan');
            $table->date('tanggal_masuk');
            $table->date('tanggal_keluar')->nullable();
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
        Schema::dropIfExists('entitas_pegawai');
        Schema::dropIfExists('golongan');
        Schema::dropIfExists('kategori_golongan');
    }
};
