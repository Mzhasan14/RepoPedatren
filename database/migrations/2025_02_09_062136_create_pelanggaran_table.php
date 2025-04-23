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
        Schema::create('pelanggaran', function (Blueprint $table) {
            $table->id();
            $table->uuid('santri_id');
            $table->enum('status_pelanggaran', ['Belum diproses', 'Sedang diproses', 'Sudah diproses'])->default('Belum diproses');
            $table->enum('jenis_putusan', ['Belum ada putusan', 'Disanksi', 'Dibebaskan'])->default('Belum ada putusan');
            $table->enum('jenis_pelanggaran', ['Ringan', 'Sedang', 'Berat'])->default('Ringan');
            $table->boolean('diproses_mahkamah')->default(false);
            $table->text('keterangan');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('santri_id')->references('id')->on('santri')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pelanggaran');
    }
};
