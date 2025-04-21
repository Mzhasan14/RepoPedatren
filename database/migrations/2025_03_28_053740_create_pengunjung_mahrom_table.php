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
            $table->uuid('santri_id');
            $table->string('nama_pengunjung');
            $table->tinyInteger('jumlah_rombongan');
            $table->datetime('tanggal');
            $table->timestamps();

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
