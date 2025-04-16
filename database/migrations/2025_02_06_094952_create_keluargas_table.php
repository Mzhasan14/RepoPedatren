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
            // Tabel hubungan_keluarga (Jenis Hubungan Keluarga)
            Schema::create('hubungan_keluarga', function (Blueprint $table) {
                $table->id();
                $table->enum('nama_status', ['ayah', 'ibu', 'wali']);
                $table->softDeletes();
                $table->unsignedBigInteger('created_by');
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->unsignedBigInteger('deleted_by')->nullable();
                $table->timestamps();

                $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('updated_by')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('deleted_by')->references('id')->on('users')->onDelete('cascade');
            });

            // Tabel orang_tua_wali (Informasi Orang Tua atau Wali)
            Schema::create('orang_tua_wali', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('id_biodata');
                $table->unsignedBigInteger('id_hubungan_keluarga');
                $table->boolean('wali')->default(false);
                $table->string('pekerjaan')->nullable();
                $table->integer('penghasilan')->nullable();
                $table->boolean('wafat')->default(false);
                $table->boolean('status');
                $table->softDeletes();
                $table->unsignedBigInteger('created_by');
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->unsignedBigInteger('deleted_by')->nullable();
                $table->timestamps();

                $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('updated_by')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('deleted_by')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('id_biodata')->references('id')->on('biodata')->onDelete('cascade');
                $table->foreign('id_hubungan_keluarga')->references('id')->on('hubungan_keluarga')->onDelete('cascade');
            });

            // Tabel keluarga (Data Keluarga dalam Satu KK)
            Schema::create('keluarga', function (Blueprint $table) {
                $table->id();
                $table->char('no_kk', 16)->nullable();
                $table->unsignedBigInteger('id_biodata');
                $table->boolean('status');
                $table->softDeletes();
                $table->unsignedBigInteger('created_by');
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->unsignedBigInteger('deleted_by')->nullable();
                $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('deleted_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('id_biodata')->references('id')->on('biodata')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('status_keluarga');
        Schema::dropIfExists('orang_tua');
        Schema::dropIfExists('anggota_keluarga');
    }
};
