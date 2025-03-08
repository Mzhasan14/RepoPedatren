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
        Schema::create('desa', function (Blueprint $table) {
            $table->id();
            $table->string('nama_desa');
            $table->unsignedBigInteger('id_kecamatan');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->softDeletes();
            $table->boolean('status');
            $table->timestamps();

            $table->foreign('id_kecamatan')->references('id')->on('kecamatan')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('desa');
    }
};
