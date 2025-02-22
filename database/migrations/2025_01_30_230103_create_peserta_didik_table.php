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
            $table->char('nis', 11)->nullable()->unique('pd_nis_unique');
            $table->tinyInteger('anak_keberapa');
            $table->tinyInteger('dari_saudara');
            $table->string('tinggal_bersama', 40);
            $table->string('smartcard');
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
