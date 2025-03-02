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
  
        Schema::create('jenis_berkas', function (Blueprint $table) {
            $table->id();
            $table->string('type_jenis_berkas');
            $table->string('nama_jenis_berkas');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->boolean('status');
            $table->timestamps();
        });
        Schema::create('berkas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_biodata');
            $table->unsignedBigInteger('id_jenis_berkas');
            $table->string('file_path');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->boolean('status');
            $table->timestamps();

            $table->foreign('id_biodata')->references('id')->on('biodata')->onDelete('cascade');
            $table->foreign('id_jenis_berkas')->references('id')->on('jenis_berkas')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('berkas');
    }
};
