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
        Schema::create('catatan_afektif', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_santri');
            $table->unsignedBigInteger('id_wali_asuh');
            $table->enum('kepedulian_nilai', ['A', 'B', 'C', 'D', 'E']);
            $table->text('kepedulian_tindak_lanjut');
            $table->enum('kebersihan_nilai', ['A', 'B', 'C', 'D', 'E']);
            $table->text('kebersihan_tindak_lanjut');
            $table->enum('akhlak_nilai', ['A', 'B', 'C', 'D', 'E']);
            $table->text('akhlak_tindak_lanjut');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->boolean('status');
            $table->timestamps();

            $table->foreign('id_santri')->references('id')->on('santri')->onDelete('cascade');
            $table->foreign('id_wali_asuh')->references('id')->on('wali_asuh')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('catatan_afektif');
    }
};
