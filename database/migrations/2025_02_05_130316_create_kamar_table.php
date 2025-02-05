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
        Schema::create('kamar', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_blok');
            $table->string('nama_kamar');
            $table->integer('created_by');
            $table->integer('updated_by');
            $table->boolean('status');
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('id_blok')->references('id')->on('blok')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kamar');
    }
};
