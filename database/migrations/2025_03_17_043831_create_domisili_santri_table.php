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
        Schema::create('domisili_santri', function (Blueprint $table) {
            $table->id();
            $table->uuid('id_peserta_didik');
            $table->unsignedBigInteger('id_wilayah')->nullable();
            $table->unsignedBigInteger('id_blok')->nullable();
            $table->unsignedBigInteger('id_kamar')->nullable();
            $table->datetime('tanggal_masuk');
            $table->datetime('tanggal_keluar')->nullable();
            $table->enum('status', ['aktif', 'keluar']);
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('id_wilayah')->references('id')->on('wilayah')->onDelete('cascade');
            $table->foreign('id_blok')->references('id')->on('blok')->onDelete('cascade');
            $table->foreign('id_kamar')->references('id')->on('kamar')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('deleted_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('id_peserta_didik')->references('id')->on('peserta_didik')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('domisili_santri');
    }
};
