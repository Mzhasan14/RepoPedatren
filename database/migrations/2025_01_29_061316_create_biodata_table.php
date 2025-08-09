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
        Schema::create('biodata', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('negara_id')->nullable();
            $table->unsignedBigInteger('provinsi_id')->nullable();
            $table->unsignedBigInteger('kabupaten_id')->nullable();
            $table->unsignedBigInteger('kecamatan_id')->nullable();
            $table->string('jalan')->nullable()->nullable();
            $table->string('kode_pos')->nullable();
            $table->string('nama', 100);
            $table->string('no_passport')->nullable();
            $table->enum('jenis_kelamin', ['l', 'p']);
            $table->date('tanggal_lahir')->nullable();
            $table->string('tempat_lahir', 50)->nullable();
            $table->char('nik', 16)->nullable();
            $table->string('no_telepon', 20)->nullable();
            $table->string('no_telepon_2', 20)->nullable();
            $table->string('email', 100)->nullable();
            $table->enum(
                'jenjang_pendidikan_terakhir',
                ['paud', 'sd/mi', 'smp/mts', 'sma/smk/ma', 'd3', 'd4', 's1', 's2']
            )->nullable();
            $table->string('nama_pendidikan_terakhir')->nullable();
            $table->tinyInteger('anak_keberapa')->nullable();
            $table->tinyInteger('dari_saudara')->nullable();
            $table->string('tinggal_bersama', 40)->nullable();
            $table->string('smartcard')->nullable();
            $table->boolean('status')->default(true);
            $table->boolean('wafat')->default(false);
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('deleted_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('negara_id')->references('id')->on('negara')->onDelete('cascade');
            $table->foreign('provinsi_id')->references('id')->on('provinsi')->onDelete('cascade');
            $table->foreign('kabupaten_id')->references('id')->on('kabupaten')->onDelete('cascade');
            $table->foreign('kecamatan_id')->references('id')->on('kecamatan')->onDelete('cascade');
            $table->fullText('nama');

            $table->index('negara_id', 'idx_biodata_negara_id');
            $table->index('provinsi_id', 'idx_biodata_provinsi_id');
            $table->index('kabupaten_id', 'idx_biodata_kabupaten_id');
            $table->index('kecamatan_id', 'idx_biodata_kecamatan_id');
        });
    }

    /** 
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('biodata');
    }
};
