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
        Schema::create('mata_pelajaran', function (Blueprint $table) {
            $table->id();
            $table->string('kode_mapel');
            $table->string('nama_mapel');
            $table->unsignedBigInteger('pengajar_id');
            $table->boolean('status');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();

            $table->foreign('pengajar_id')->references('id')->on('pengajar')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('deleted_by')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('jam_pelajaran', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('jam_ke'); // urutan jam pelajaran
            $table->string('label')->nullable();   // contoh: "Jam 1", "Istirahat", "Sesi Sore 1"
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();

            $table->unique('jam_ke');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('deleted_by')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('jadwal_pelajaran', function (Blueprint $table) {
            $table->id();
            $table->enum('hari', ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu']);
            $table->unsignedBigInteger('semester_id');
            $table->unsignedBigInteger('lembaga_id');
            $table->unsignedBigInteger('jurusan_id');
            $table->unsignedBigInteger('kelas_id');
            $table->unsignedBigInteger('rombel_id')->nullable();
            $table->unsignedBigInteger('mata_pelajaran_id');
            $table->unsignedBigInteger('jam_pelajaran_id');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();

            // Relasi foreign key
            $table->unique(['hari', 'kelas_id', 'jam_pelajaran_id'], 'jadwal_unik_kelas_jam');
            $table->foreign('semester_id')->references('id')->on('semester')->onDelete('cascade');
            $table->foreign('lembaga_id')->references('id')->on('lembaga')->onDelete('cascade');
            $table->foreign('jurusan_id')->references('id')->on('jurusan')->onDelete('cascade');
            $table->foreign('kelas_id')->references('id')->on('kelas')->onDelete('cascade');
            $table->foreign('rombel_id')->references('id')->on('rombel')->onDelete('cascade');
            $table->foreign('mata_pelajaran_id')->references('id')->on('mata_pelajaran')->onDelete('cascade');
            $table->foreign('jam_pelajaran_id')->references('id')->on('jam_pelajaran')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('deleted_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pelajaran');
    }
};
