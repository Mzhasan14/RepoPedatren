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
        Schema::create('peserta_didik', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_biodata');
            $table->unsignedBigInteger('id_domisili')->nullable();
            $table->unsignedBigInteger('id_lembaga');
            $table->unsignedBigInteger('id_jurusan');
            $table->unsignedBigInteger('id_kelas');
            $table->unsignedBigInteger('id_rombel')->nullable();
            $table->char('nis', 11)->nullable()->unique('pd_nis_unique');
            $table->string('no_induk')->nullable();
            $table->date('tahun_masuk');
            $table->date('tahun_keluar')->nullable();
            $table->timestamps();
            $table->integer('created_by');
            $table->integer('updated_by')->nullable();
            $table->softDeletes();
            $table->integer('deleted_by')->nullable();
            $table->boolean('status');

            $table->foreign('id_biodata')->references('id')->on('biodata')->onDelete('cascade');
            $table->foreign('id_domisili')->references('id')->on('domisili')->onDelete('cascade');
            $table->foreign('id_lembaga')->references('id')->on('lembaga')->onDelete('cascade');
            $table->foreign('id_jurusan')->references('id')->on('jurusan')->onDelete('cascade');
            $table->foreign('id_kelas')->references('id')->on('kelas')->onDelete('cascade');
            $table->foreign('id_rombel')->references('id')->on('rombel')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('peserta_didik');
    }
};
