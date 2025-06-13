<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('jenis_presensi', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100);
            $table->text('deskripsi')->nullable();
            $table->boolean('aktif')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('presensi_santri', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('santri_id');
            $table->unsignedBigInteger('jenis_presensi_id');
            $table->date('tanggal')->index();
            $table->time('waktu_presensi')->nullable();
            $table->enum('status', ['hadir', 'izin', 'sakit', 'alfa']);
            $table->string('keterangan', 255)->nullable();
            $table->string('lokasi', 50)->nullable();
            $table->enum('metode', ['qr', 'manual', 'rfid', 'fingerprint'])->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->unique(['santri_id', 'jenis_presensi_id', 'tanggal']);

            $table->foreign('santri_id')->references('id')->on('santri')->cascadeOnDelete();
            $table->foreign('jenis_presensi_id')->references('id')->on('jenis_presensi')->cascadeOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('deleted_by')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::create('presensi_pelajaran', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('santri_id');
            $table->unsignedBigInteger('jadwal_pelajaran_id');
            $table->date('tanggal')->index();
            $table->enum('status', ['hadir', 'izin', 'sakit', 'alfa', 'terlambat']);
            $table->dateTime('waktu')->nullable();
            $table->text('keterangan')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->unique(['santri_id', 'jadwal_pelajaran_id', 'tanggal']);

            $table->foreign('santri_id')->references('id')->on('santri')->cascadeOnDelete();
            $table->foreign('jadwal_pelajaran_id')->references('id')->on('jadwal_pelajaran')->cascadeOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('deleted_by')->references('id')->on('users')->cascadeOnDelete();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('presensi_pelajaran');
        Schema::dropIfExists('jadwal_pelajaran');
        Schema::dropIfExists('presensi_santri');
        Schema::dropIfExists('jenis_presensi');
    }
};
