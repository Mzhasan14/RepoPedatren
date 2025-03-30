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
        Schema::create('pengajar', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('id_pegawai');
            $table->unsignedBigInteger('id_golongan');
            $table->string('jabatan');
            $table->date('tahun_masuk');
            $table->date('tahun_keluar')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->boolean('status');
            $table->timestamps();

            $table->foreign('id_pegawai')->references('id')->on('pegawai')->onDelete('cascade');
            $table->foreign('id_golongan')->references('id')->on('golongan')->onDelete('cascade');
        });
        Schema::create('materi_ajar', function (Blueprint $table) {
            $table->id();
            $table->uuid('id_pengajar');
            $table->string('nama_materi');
            $table->integer('jumlah_menit')->nullable()->default(0); // Simpan dalam satuan menit
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->boolean('status')->default(1);
            $table->timestamps();

            $table->foreign('id_pengajar')->references('id')->on('pengajar')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengajar');
    }
};
