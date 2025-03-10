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
        Schema::create('santri', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_peserta_didik');
            $table->unsignedBigInteger('id_wilayah');
            $table->unsignedBigInteger('id_blok');
            $table->unsignedBigInteger('id_kamar');
            $table->unsignedBigInteger('id_domisili');
            $table->string('nis', 11)->unique();
            $table->date('tanggal_masuk');
            $table->date('tanggal_keluar')->nullable();
            $table->integer('created_by');
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_by')->nullable();
            $table->boolean('status');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('id_peserta_didik')->references('id')->on('peserta_didik')->onDelete('cascade');
            $table->foreign('id_wilayah')->references('id')->on('wilayah')->onDelete('cascade');
            $table->foreign('id_blok')->references('id')->on('blok')->onDelete('cascade');
            $table->foreign('id_kamar')->references('id')->on('kamar')->onDelete('cascade');
            $table->foreign('id_domisili')->references('id')->on('domisili')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('santri');
    }
};
