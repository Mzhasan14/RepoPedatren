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
        Schema::create('berkas_pelanggaran', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pelanggaran_id');
            $table->string('file_path');
            $table->softDeletes();
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();

            $table->foreign('pelanggaran_id')->references('id')->on('pelanggaran')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('berkas_pelanggaran');
    }
};
