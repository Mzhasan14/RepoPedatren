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
        Schema::create('pengunjung_mahrom', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('santri_id');
            $table->string('nama_pengunjung');
            $table->unsignedBigInteger('hubungan_id');
            $table->tinyInteger('jumlah_rombongan');
            $table->datetime('tanggal_kunjungan');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('deleted_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('hubungan_id')->references('id')->on('hubungan_keluarga')->onDelete('cascade');
            $table->foreign('santri_id')->references('id')->on('santri')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengunjung_mahrom');
    }
};
