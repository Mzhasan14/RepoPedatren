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
        Schema::create('pesan_santri', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('santri_id');
            $table->unsignedBigInteger('orangtua_id');
            $table->text('pesan');
            $table->dateTime('tanggal_baca')->nullable();
            $table->enum('status', ['dibaca', 'belum dibaca'])->default('belum dibaca');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('santri_id')->references('id')->on('santri')->onDelete('cascade');
            $table->foreign('orangtua_id')->references('id')->on('orang_tua_wali')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pesan_santri');
    }
};
