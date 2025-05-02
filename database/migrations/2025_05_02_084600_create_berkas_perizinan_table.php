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
        Schema::create('berkas_perizinan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('perizinan_id');
            $table->string('file_path');
            $table->softDeletes();
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();

            $table->foreign('perizinan_id')->references('id')->on('perizinan')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('berkas_perizinan');
    }
};
