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
        Schema::create('pendidikan', function (Blueprint $table) {
            $table->id();
            $table->uuid('biodata_id');
            $table->string('no_induk')->nullable();
            $table->unsignedBigInteger('lembaga_id');
            $table->unsignedBigInteger('jurusan_id')->nullable();
            $table->unsignedBigInteger('kelas_id')->nullable();
            $table->unsignedBigInteger('rombel_id')->nullable();
            $table->unsignedBigInteger('angkatan_id')->nullable();
            $table->date('tanggal_masuk');
            $table->enum('status', ['aktif', 'cuti', 'tunda'])->default('aktif');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('angkatan_id')->references('id')->on('angkatan')->onDelete('set null');
            $table->foreign('biodata_id')->references('id')->on('biodata')->onDelete('cascade');
            $table->foreign('lembaga_id')->references('id')->on('lembaga')->onDelete('cascade');
            $table->foreign('jurusan_id')->references('id')->on('jurusan')->onDelete('cascade');
            $table->foreign('kelas_id')->references('id')->on('kelas')->onDelete('cascade');
            $table->foreign('rombel_id')->references('id')->on('rombel')->onDelete('cascade');
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
        Schema::dropIfExists('pendidikan');
    }
};
