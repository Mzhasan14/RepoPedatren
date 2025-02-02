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
        Schema::create('peserta_didik', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('id_biodata');
            $table->string('nis');
            $table->tinyInteger('anak_keberapa');
            $table->tinyInteger('dari_saudara');
            $table->string('tinggal_bersama');
            $table->enum(
                'jenjang_pendidikan_terakhir',
                ['paud', 'sd/mi', 'smp/mts', 'sma/smk/ma', 'd3', 'd4', 's1', 's2']
            );
            $table->string('nama_pendidikan_terakhir');
            $table->string('smartcard');
            $table->date('tahun_masuk');
            $table->date('tahun_keluar')->nullable();
            $table->timestamps();
            $table->integer('created_by');
            $table->integer('update_by')->nullable();
            $table->timestamp('delete_at')->nullable();
            $table->integer('delete_by')->nullable();
            $table->boolean('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('peserta_didik');
    }
};
