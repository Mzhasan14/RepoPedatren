<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. kitab
        Schema::create('kitab', function (Blueprint $table) {
            $table->id();
            $table->string('nama_kitab', 100);
            $table->unsignedSmallInteger('total_bait')->default(0);
            $table->boolean('status');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();

            $table->foreign('created_by')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('deleted_by')->references('id')->on('users')->nullOnDelete();

            $table->softDeletes();
            $table->timestamps();
        });

        // 2. tahfidz
        Schema::create('tahfidz', function (Blueprint $table) {
            $table->id();
            $table->foreignId('santri_id')->constrained('santri')->cascadeOnDelete();
            $table->foreignId('tahun_ajaran_id')->constrained('tahun_ajaran')->cascadeOnDelete();
            // $table->foreignId('ustadz_id')->constrained('ustadz')->cascadeOnDelete();
            $table->date('tanggal');
            $table->enum('jenis_setoran', ['baru', 'murojaah']);
            $table->string('surat', 20);
            $table->unsignedSmallInteger('ayat_mulai');
            $table->unsignedSmallInteger('ayat_selesai');
            $table->enum('nilai', ['lancar', 'cukup', 'kurang']);
            $table->text('catatan')->nullable();
            $table->enum('status', ['proses', 'tuntas']);

            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();

            $table->foreign('created_by')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('deleted_by')->references('id')->on('users')->nullOnDelete();

            $table->softDeletes();
            $table->timestamps();
        });

        // 3. rekap_tahfidz
        // Schema::create('rekap_tahfidz', function (Blueprint $table) {
        //     $table->id();
        //     $table->foreignId('santri_id')->constrained('santri')->cascadeOnDelete();
        //     $table->foreignId('tahun_ajaran_id')->constrained('tahun_ajaran')->cascadeOnDelete();

        //     $table->unique(['santri_id', 'tahun_ajaran_id']);

        //     $table->unsignedSmallInteger('total_surat')->default(0);
        //     $table->decimal('persentase_khatam', 5, 2)->default(0.00);

        //     $table->unsignedBigInteger('created_by');
        //     $table->unsignedBigInteger('updated_by')->nullable();
        //     $table->unsignedBigInteger('deleted_by')->nullable();

        //     $table->foreign('created_by')->references('id')->on('users')->cascadeOnDelete();
        //     $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
        //     $table->foreign('deleted_by')->references('id')->on('users')->nullOnDelete();

        //     $table->softDeletes();
        //     $table->timestamps();
        // });

        Schema::create('rekap_tahfidz', function (Blueprint $table) {
            $table->id();
            $table->foreignId('santri_id')->constrained('santri');
            $table->foreignId('tahun_ajaran_id')->constrained('tahun_ajaran');
            $table->unsignedTinyInteger('total_surat')->default(0);
            $table->decimal('persentase_khatam', 5, 2)->default(0.00);
            $table->unsignedTinyInteger('surat_tersisa')->default(114);
            $table->decimal('sisa_persentase', 5, 2)->default(100.00);
            $table->unsignedInteger('jumlah_setoran')->default(0);
            $table->decimal('rata_rata_nilai', 5, 2)->default(0.00);
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->softDeletes();

            $table->foreign('created_by')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('deleted_by')->references('id')->on('users')->nullOnDelete();
            $table->timestamps();
        });


        // 4. nadhoman
        Schema::create('nadhoman', function (Blueprint $table) {
            $table->id();
            $table->foreignId('santri_id')->constrained('santri')->cascadeOnDelete();
            $table->foreignId('kitab_id')->constrained('kitab')->cascadeOnDelete();
            $table->foreignId('tahun_ajaran_id')->constrained('tahun_ajaran')->cascadeOnDelete();
            // $table->foreignId('ustadz_id')->constrained('ustadz')->cascadeOnDelete();
            $table->date('tanggal');
            $table->enum('jenis_setoran', ['baru', 'murojaah']);
            $table->unsignedSmallInteger('bait_mulai');
            $table->unsignedSmallInteger('bait_selesai');
            $table->enum('nilai', ['lancar', 'cukup', 'kurang']);
            $table->text('catatan')->nullable();
            $table->enum('status', ['proses', 'tuntas']);

            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();

            $table->foreign('created_by')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('deleted_by')->references('id')->on('users')->nullOnDelete();

            $table->softDeletes();
            $table->timestamps();
        });

        // 5. rekap_nadhoman
        Schema::create('rekap_nadhoman', function (Blueprint $table) {
            $table->id();
            $table->foreignId('santri_id')->constrained('santri')->cascadeOnDelete();
            $table->foreignId('kitab_id')->constrained('kitab')->cascadeOnDelete();
            $table->foreignId('tahun_ajaran_id')->constrained('tahun_ajaran')->cascadeOnDelete();

            $table->unique(['santri_id', 'kitab_id', 'tahun_ajaran_id']);

            $table->unsignedSmallInteger('total_bait')->default(0);
            $table->decimal('persentase_selesai', 5, 2)->default(0.00);

            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();

            $table->foreign('created_by')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('deleted_by')->references('id')->on('users')->nullOnDelete();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rekap_nadhoman');
        Schema::dropIfExists('nadhoman');
        Schema::dropIfExists('rekap_tahfidz');
        Schema::dropIfExists('tahfidz');
        Schema::dropIfExists('kitab');
    }
};
