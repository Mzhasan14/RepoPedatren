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
        Schema::create('domisili', function (Blueprint $table) {
            $table->id();
            $table->char('nis');
            $table->unsignedBigInteger('id_kamar');
            $table->string('nama_domisili');
            $table->integer('created_by');
            $table->integer('updated_by');
            $table->boolean('status');
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('id_kamar')->references('id')->on('kamar')->onDelete('cascade');
            $table->foreign('nis')->references('nis')->on('peserta_didik')->onDelete('cascade');
        });
    } 

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('domisili');
    }
};
