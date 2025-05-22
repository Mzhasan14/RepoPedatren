<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. jenis_presensi (non-akademik)
        Schema::create('jenis_presensi', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100);
            $table->text('deskripsi')->nullable();
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });

        // 2. presensi_santri (non-akademik)
        Schema::create('presensi_santri', function (Blueprint $table) {
            $table->id();
            $table->foreignId('santri_id')->constrained('santri');
            $table->foreignId('jenis_presensi_id')->constrained('jenis_presensi');
            $table->date('tanggal');
            $table->enum('status', ['hadir', 'izin', 'sakit', 'alfa', 'terlambat']);
            $table->dateTime('waktu')->nullable();
            $table->string('dicatat_oleh', 100)->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->unique(['santri_id', 'jenis_presensi_id', 'tanggal']);
        });

        // 4. presensi_pelajaran (akademik)
        Schema::create('presensi_pelajaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('santri_id')->constrained('santri');
            $table->foreignId('jadwal_pelajaran_id')->constrained('jadwal_pelajaran');
            $table->date('tanggal');
            $table->enum('status', ['hadir', 'izin', 'sakit', 'alfa', 'terlambat']);
            $table->dateTime('waktu')->nullable();
            $table->text('keterangan')->nullable();
            $table->foreignId('dicatat_oleh')->nullable()->constrained('users');
            $table->timestamps();

            $table->unique(['santri_id', 'jadwal_pelajaran_id', 'tanggal']);
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
