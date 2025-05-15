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

        Schema::create('wali_kelas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pegawai_id');
            $table->unsignedBigInteger('lembaga_id')->nullable();
            $table->unsignedBigInteger('jurusan_id')->nullable();
            $table->unsignedBigInteger('kelas_id')->nullable();
            $table->unsignedBigInteger('rombel_id')->nullable();
            $table->string('jumlah_murid');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->enum('status_aktif', ['aktif', 'tidak aktif'])->default('aktif');
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('pegawai_id')->references('id')->on('pegawai')->onDelete('cascade');
            $table->foreign('lembaga_id')->references('id')->on('lembaga')->onDelete('cascade');
            $table->foreign('jurusan_id')->references('id')->on('jurusan')->onDelete('cascade');
            $table->foreign('kelas_id')->references('id')->on('kelas')->onDelete('cascade');
            $table->foreign('rombel_id')->references('id')->on('rombel')->onDelete('cascade');
        });
        // Schema::create('wali_kelas', function (Blueprint $table) {
        //     $table->uuid('id')->primary();
        //     $table->uuid('pengajar_id')->unique();
        //     $table->uuid('kelas_id');
        //     $table->string('jumlah_murid');
        //     $table->string('tahun_ajaran');
        //     $table->string('semester')->nullable();
        //     $table->text('keterangan')->nullable();
        //     $table->unsignedBigInteger('created_by');
        //     $table->unsignedBigInteger('updated_by')->nullable();
        //     $table->boolean('status');
        //     $table->timestamps();
        
        //     $table->foreign('pengajar_id')->references('id')->on('pengajar')->onDelete('cascade');
        //     $table->foreign('kelas_id')->references('id')->on('kelas')->onDelete('cascade');
        // });
        


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wali_kelas');
    }
};
