<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // public function up(): void
    // {
    //     Schema::create('tingkatan', function (Blueprint $table) {
    //         $table->id();
    //         $table->unsignedBigInteger('jurusan_id');
    //         $table->string('nama_tingkatan'); // contoh: Tingkat 1, Tingkat 2
    //         $table->boolean('aktif')->default(true);
    //         $table->timestamps();

    //         $table->foreign('jurusan_id')->references('id')->on('jurusan')->onDelete('cascade');
    //         $table->index('jurusan_id', 'idx_tingkatan_jurusan_id');
    //     });

    //     Schema::create('pendidikan_pesantren', function (Blueprint $table) {
    //         $table->id();
    //         $table->uuid('biodata_id');
    //         $table->unsignedBigInteger('lembaga_id');
    //         $table->date('tanggal_masuk')->nullable();
    //         $table->date('tanggal_keluar')->nullable();
    //         $table->enum('status', ['aktif', 'nonaktif', 'lulus', 'berhenti'])->default('aktif');
    //         $table->text('keterangan')->nullable();
    //         $table->unsignedBigInteger('created_by');
    //         $table->unsignedBigInteger('updated_by')->nullable();
    //         $table->unsignedBigInteger('deleted_by')->nullable();
    //         $table->softDeletes();
    //         $table->timestamps();

    //         $table->foreign('biodata_id')->references('id')->on('biodata')->onDelete('cascade');
    //         $table->foreign('lembaga_id')->references('id')->on('lembaga')->onDelete('cascade');
    //         $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
    //         $table->foreign('updated_by')->references('id')->on('users')->onDelete('cascade');
    //         $table->foreign('deleted_by')->references('id')->on('users')->onDelete('cascade');
    //     });

    //     Schema::create('pendidikan_pesantren_jurusan', function (Blueprint $table) {
    //         $table->id();
    //         $table->unsignedBigInteger('pendidikan_pesantren_id');
    //         $table->unsignedBigInteger('jurusan_id');
    //         $table->enum('status', ['aktif', 'selesai'])->default('aktif');
    //         $table->year('tahun_mulai')->nullable();
    //         $table->year('tahun_selesai')->nullable();
    //         $table->text('keterangan')->nullable();
    //         $table->unsignedBigInteger('created_by');
    //         $table->unsignedBigInteger('updated_by')->nullable();
    //         $table->unsignedBigInteger('deleted_by')->nullable();
    //         $table->softDeletes();
    //         $table->timestamps();

    //         $table->foreign('pendidikan_pesantren_id')->references('id')->on('pendidikan_pesantren')->onDelete('cascade');
    //         $table->foreign('jurusan_id')->references('id')->on('jurusan')->onDelete('cascade');
    //         $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
    //         $table->foreign('updated_by')->references('id')->on('users')->onDelete('cascade');
    //         $table->foreign('deleted_by')->references('id')->on('users')->onDelete('cascade');
    //     });

    //     Schema::create('pendidikan_pesantren_tingkatan', function (Blueprint $table) {
    //         $table->id();
    //         $table->unsignedBigInteger('pendidikan_pesantren_jurusan_id');
    //         $table->unsignedBigInteger('tingkatan_id');
    //         $table->date('tanggal_mulai')->nullable();
    //         $table->date('tanggal_selesai')->nullable();
    //         $table->enum('status', ['aktif', 'selesai'])->default('aktif');
    //         $table->text('keterangan')->nullable();
    //         $table->timestamps();

    //         $table->foreign('pendidikan_pesantren_jurusan_id')->references('id')->on('pendidikan_pesantren_jurusan')->onDelete('cascade');
    //         $table->foreign('tingkatan_id')->references('id')->on('tingkatan')->onDelete('cascade');

    //         $table->index('pendidikan_pesantren_jurusan_id', 'idx_ppt_pendidikan_pesantren_jurusan_id');
    //         $table->index('tingkatan_id', 'idx_ppt_tingkatan_id');
    //     });

    //     Schema::create('riwayat_pendidikan_pesantren', function (Blueprint $table) {
    //         $table->id();
    //         $table->uuid('biodata_id');
    //         $table->unsignedBigInteger('lembaga_id')->nullable();
    //         $table->unsignedBigInteger('jurusan_id')->nullable();
    //         $table->unsignedBigInteger('tingkatan_id')->nullable();
    //         $table->year('tahun_mulai')->nullable();
    //         $table->year('tahun_selesai')->nullable();
    //         $table->enum('status', ['aktif', 'selesai', 'pindah', 'naik_tingkat', 'berhenti'])->default('aktif');
    //         $table->text('keterangan')->nullable();
    //         $table->unsignedBigInteger('created_by');
    //         $table->softDeletes();
    //         $table->timestamps();

    //         $table->foreign('biodata_id')->references('id')->on('biodata')->onDelete('cascade');
    //         $table->foreign('lembaga_id')->references('id')->on('lembaga')->onDelete('set null');
    //         $table->foreign('jurusan_id')->references('id')->on('jurusan')->onDelete('set null');
    //         $table->foreign('tingkatan_id')->references('id')->on('tingkatan')->onDelete('set null');
    //         $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');

    //         $table->index('biodata_id', 'idx_rpp_biodata_id');
    //         $table->index('lembaga_id', 'idx_rpp_lembaga_id');
    //         $table->index('jurusan_id', 'idx_rpp_jurusan_id');
    //         $table->index('tingkatan_id', 'idx_rpp_tingkatan_id');
    //     });
    // }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pendidikan_pesantren');
    }
};
