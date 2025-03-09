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
            $table->unsignedBigInteger('id_desa');
            $table->string('nama', 100);
            $table->char('niup', 11);
            $table->enum('jenis_kelamin', ['l', 'p']);
            $table->date('tanggal_lahir');
            $table->string('tempat_lahir', 50);
            $table->char('nik', 16);
            $table->char('no_kk', 16);
            $table->string('no_telepon', 20);
            $table->string('email', 100)->unique('bd_email_unique');
            $table->enum(
                'jenjang_pendidikan_terakhir',
                ['paud', 'sd/mi', 'smp/mts', 'sma/smk/ma', 'd3', 'd4', 's1', 's2']
            );
            $table->string('nama_pendidikan_terakhir');
            $table->string('image_url');
            $table->string('smartcard');
            $table->boolean('status');
            $table->integer('created_by');
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_by')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('id_desa')->references('id')->on('desa')->onDelete('cascade');
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
