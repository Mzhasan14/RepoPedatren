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
        Schema::create('pendidikan_pelajar', function (Blueprint $table) {
            $table->id();
            $table->uuid('id_peserta_didik');
            $table->unsignedBigInteger('id_lembaga');
            $table->unsignedBigInteger('id_jurusan')->nullable();
            $table->unsignedBigInteger('id_kelas')->nullable();
            $table->unsignedBigInteger('id_rombel')->nullable();
            $table->enum('status', ['aktif', 'keluar']);
            $table->datetime('tanggal_masuk');
            $table->datetime('tanggal_keluar')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('id_lembaga')->references('id')->on('lembaga')->onDelete('cascade');
            $table->foreign('id_jurusan')->references('id')->on('jurusan')->onDelete('cascade');
            $table->foreign('id_kelas')->references('id')->on('kelas')->onDelete('cascade');
            $table->foreign('id_rombel')->references('id')->on('rombel')->onDelete('cascade');
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
        Schema::dropIfExists('pendidikan_pelajar');
    }
};
