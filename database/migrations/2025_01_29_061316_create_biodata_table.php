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
            $table->id();
            $table->unsignedBigInteger('id_negara');
            $table->unsignedBigInteger('id_provinsi')->nullable();
            $table->unsignedBigInteger('id_kabupaten')->nullable();
            $table->unsignedBigInteger('id_kecamatan')->nullable();
            $table->unsignedBigInteger('id_desa')->nullable();
            $table->string('nama', 100);
            $table->char('niup', 11);
            $table->string('no_passport')->nullable();
            $table->enum('jenis_kelamin', ['l', 'p']);
            $table->date('tanggal_lahir');
            $table->string('tempat_lahir', 50);
            $table->char('nik', 16)->nullable();
            $table->char('no_kk', 16)->nullable();
            $table->string('no_telepon', 20);
            $table->string('email', 100)->unique('bd_email_unique');
            $table->enum(
                'jenjang_pendidikan_terakhir',
                ['paud', 'sd/mi', 'smp/mts', 'sma/smk/ma', 'd3', 'd4', 's1', 's2']
            );
            $table->string('nama_pendidikan_terakhir');
            $table->tinyInteger('anak_keberapa');
            $table->tinyInteger('dari_saudara');
            $table->string('tinggal_bersama', 40);
            $table->string('smartcard')->nullable();
            $table->boolean('status');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->softDeletes();
            $table->timestamps();


            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('deleted_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('id_negara')->references('id')->on('negara')->onDelete('cascade');
            $table->foreign('id_provinsi')->references('id')->on('provinsi')->onDelete('cascade');
            $table->foreign('id_kabupaten')->references('id')->on('kabupaten')->onDelete('cascade');
            $table->foreign('id_kecamatan')->references('id')->on('kecamatan')->onDelete('cascade');
            $table->foreign('id_desa')->references('id')->on('desa')->onDelete('cascade');
            $table->fullText('nama');
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
