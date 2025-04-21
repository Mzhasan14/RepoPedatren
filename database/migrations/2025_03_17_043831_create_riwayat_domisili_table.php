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
        Schema::create('riwayat_domisili', function (Blueprint $table) {
            $table->id();
            $table->uuid('santri_id');
            $table->unsignedBigInteger('wilayah_id')->nullable();
            $table->unsignedBigInteger('blok_id')->nullable();
            $table->unsignedBigInteger('kamar_id')->nullable();
            $table->datetime('tanggal_masuk');
            $table->datetime('tanggal_keluar')->nullable();
            $table->enum('status', ['aktif', 'do', 'berhenti', 'alumni', 'pindah'])->default('aktif');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('santri_id')->references('id')->on('santri')->onDelete('cascade');
            $table->foreign('wilayah_id')->references('id')->on('wilayah')->onDelete('cascade');
            $table->foreign('blok_id')->references('id')->on('blok')->onDelete('cascade');
            $table->foreign('kamar_id')->references('id')->on('kamar')->onDelete('cascade');
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
        Schema::dropIfExists('riwayat_domisili');
    }
};
