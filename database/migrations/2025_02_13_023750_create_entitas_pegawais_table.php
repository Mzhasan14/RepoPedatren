<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use League\CommonMark\Reference\Reference;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('kategori_golongan', function (Blueprint $table) {
            $table->id();
            $table->string('nama_kategori_golongan');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->boolean('status');
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::create('golongan', function (Blueprint $table) {
            $table->id();
            $table->string('nama_golongan');
            $table->unsignedBigInteger('kategori_golongan_id')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->boolean('status');
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('kategori_golongan_id')->references('id')->on('kategori_golongan')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('golongan');
        Schema::dropIfExists('kategori_golongan');
    }
};
