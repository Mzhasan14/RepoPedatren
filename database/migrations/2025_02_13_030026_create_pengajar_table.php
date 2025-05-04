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
            $table->uuid('pegawai_id');
            $table->unsignedBigInteger('lembaga_id')->nullable();
            $table->unsignedBigInteger('golongan_id')->nullable();  
            $table->string('jabatan');
            $table->date('tahun_masuk')->nullable();
            $table->date('tahun_akhir')->nullable();
            $table->enum('status_aktif', ['aktif', 'tidak aktif'])->default('aktif');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('lembaga_id')->references('id')->on('lembaga')->onDelete('cascade');
            $table->foreign('pegawai_id')->references('id')->on('pegawai')->onDelete('cascade');
            $table->foreign('golongan_id')->references('id')->on('golongan')->onDelete('cascade');
        });
        Schema::create('materi_ajar', function (Blueprint $table) {
            $table->id();
            $table->uuid('pengajar_id');
            $table->string('nama_materi');
            $table->integer('jumlah_menit')->nullable()->default(0); // Simpan dalam satuan menit
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->boolean('status')->default(1);
            $table->timestamps();

            $table->foreign('pengajar_id')->references('id')->on('pengajar')->onDelete('cascade');
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
