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
        Schema::create('keluarga', function (Blueprint $table) {
            $table->id();
            $table->char('no_kk', 16);
            $table->boolean('status_wali')->nullable()->default(false);
            $table->unsignedBigInteger('id_status_keluarga');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->boolean('status')->default(true);
            $table->timestamps(); 


            // $table->foreign('no_kk')->references('no_kk')->on('biodata')->onDelete('cascade');
            $table->foreign('id_status_keluarga')->references('id')->on('status_keluarga')->onDelete('cascade');

            // $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            // $table->foreign('updated_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('keluarga', function (Blueprint $table) {
            // $table->dropForeign(['no_kk']);
            $table->dropForeign(['id_status_keluarga']);
            // $table->dropForeign(['created_by']);
            // $table->dropForeign(['updated_by']);
            $table->dropSoftDeletes();
        });

        Schema::dropIfExists('keluarga');
    }
};
