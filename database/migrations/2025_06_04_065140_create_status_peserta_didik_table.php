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
        Schema::create('status_peserta_didik', function (Blueprint $table) {
            $table->id();
            $table->uuid('biodata_id');

            $table->boolean('is_santri')->default(false);
            $table->boolean('is_pelajar')->default(false);

            $table->enum('status_santri', ['aktif', 'alumni', 'do', 'berhenti', 'nonaktif'])->nullable();
            $table->enum('status_pelajar', ['aktif', 'do', 'berhenti', 'lulus', 'pindah', 'cuti', 'naik_kelas', 'nonaktif'])->nullable();

            $table->date('tanggal_keluar_santri')->nullable();
            $table->date('tanggal_keluar_pelajar')->nullable();

            $table->timestamps();

            $table->foreign('biodata_id')->references('id')->on('biodata')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('status_peserta_didik');
    }
};
