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
        Schema::table('peserta_didik', function (Blueprint $table) {
            $table->dropColumn('jenjang_pendidikan_terakhir', 'nama_pendidikan_terakhir');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('peserta_didik', function (Blueprint $table) {
            $table->enum(
                'jenjang_pendidikan_terakhir',
                ['paud', 'sd/mi', 'smp/mts', 'sma/smk/ma', 'd3', 'd4', 's1', 's2']
            );
            $table->string('nama_pendidikan_terakhir');
        });
    }
};
