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
        // Tabel status_keluarga (Jenis Hubungan Wali)
        Schema::create('status_keluarga', function (Blueprint $table) {
            $table->id();
            $table->enum('nama_status', ['ayah', 'ibu', 'anak', 'wali']); // Ayah, Ibu, Kakak, dll.
            $table->softDeletes();
            $table->boolean('status')->default(true);
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('deleted_by')->references('id')->on('users')->onDelete('cascade');
        });

        // Tabel anggota_keluarga (Data Keluarga dalam Satu KK)
        Schema::create('keluarga', function (Blueprint $table) {
            $table->id();
            $table->char('no_kk', 16);
            $table->unsignedBigInteger('id_biodata');
            $table->unsignedBigInteger('id_status_keluarga');
            $table->boolean('wali')->default(false);
            $table->softDeletes();
            $table->boolean('status')->default(true);
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('deleted_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('id_biodata')->references('id')->on('biodata')->onDelete('cascade');
        });

        // Tabel orang_tua (Informasi Orang Tua)
        Schema::create('orang_tua', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_biodata'); // Anak yang bersangkutan
            $table->string('pekerjaan');
            $table->string('penghasilan')->nullable();
            $table->boolean('wafat')->default(false);
            $table->boolean('status')->default(true);
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
