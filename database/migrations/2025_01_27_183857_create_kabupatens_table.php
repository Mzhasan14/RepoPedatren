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
        Schema::create('kabupaten', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_provinsi');
            $table->string('nama_kabupaten');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->boolean('status')->nullable()->default(true);
            $table->timestamps();

            $table->foreign('id_provinsi')->references('id')->on('provinsi')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kabupaten');
    }
};
