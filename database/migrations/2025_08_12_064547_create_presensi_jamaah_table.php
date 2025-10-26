<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // SHOLAT
        Schema::create('sholat', function (Blueprint $table) {
            $table->id();
            $table->string('nama_sholat', 20);
            $table->unsignedTinyInteger('urutan');
            $table->boolean('aktif')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        // JADWAL SHOLAT
        Schema::create('jadwal_sholat', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sholat_id')->constrained('sholat')->cascadeOnDelete();
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->date('berlaku_mulai');
            $table->date('berlaku_sampai')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        // KARTU
        Schema::create('kartu', function (Blueprint $table) {
            $table->id();
            $table->foreignId('santri_id')->constrained('santri')->cascadeOnDelete();
            $table->string('uid_kartu', 50)->unique();
            $table->string('pin');
            $table->boolean('aktif')->default(true);
             $table->decimal('limit_saldo', 15, 2)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        // PRESENSI SHOLAT
        Schema::create('presensi_sholat', function (Blueprint $table) {
            $table->id();
            $table->foreignId('santri_id')->constrained('santri')->cascadeOnDelete();
            $table->foreignId('sholat_id')->constrained('sholat')->cascadeOnDelete();
            $table->date('tanggal');
            $table->time('waktu_presensi')->nullable();
            $table->enum('status', ['Hadir', 'tidak_hadir'])->default('Hadir');
            $table->enum('metode', ['Manual', 'Kartu'])->default('Manual');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['santri_id', 'sholat_id', 'tanggal'], 'unique_presensi_santri_sholat_tanggal');
        });

        // LOG PRESENSI
        Schema::create('log_presensi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('santri_id')->nullable()->constrained('santri')->cascadeOnDelete();
            $table->foreignId('kartu_id')->nullable()->constrained('kartu')->nullOnDelete();
            $table->foreignId('sholat_id')->nullable()->constrained('sholat')->cascadeOnDelete();
            $table->timestamp('waktu_scan');
            $table->enum('hasil', ['Sukses', 'Gagal', 'Duplikat', 'Diluar Jadwal'])->default('Sukses');
            $table->string('pesan')->nullable();
            $table->enum('metode', ['Manual', 'Kartu'])->default('Kartu');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('log_presensi');
        Schema::dropIfExists('presensi_sholat');
        Schema::dropIfExists('kartu');
        Schema::dropIfExists('santri');
        Schema::dropIfExists('jadwal_sholat');
        Schema::dropIfExists('sholat');
        Schema::dropIfExists('users');
    }
};
