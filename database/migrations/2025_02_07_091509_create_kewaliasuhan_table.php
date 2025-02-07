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
        Schema::create('grup_wali_asuh', function (Blueprint $table) {
            $table->id();
            $table->string('nama_grup');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->boolean('status')->nullable()->default(true);
            $table->timestamps();
            // $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            // $table->foreign('updated_by')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('wali_asuh', function (Blueprint $table) {
            $table->id();
            $table->char('nis',11);
            $table->unsignedBigInteger('id_grup_wali_asuh');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->boolean('status')->nullable()->default(true);
            $table->timestamps();

            $table->foreign('nis')->references('nis')->on('peserta_didik')->onDelete('cascade');
            $table->foreign('id_grup_wali_asuh')->references('id')->on('grup_wali_asuh')->onDelete('cascade');
            // $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            // $table->foreign('updated_by')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('anak_asuh', function (Blueprint $table) {
            $table->id();
            $table->char('nis',11);
            $table->unsignedBigInteger('id_grup_wali_asuh');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->boolean('status')->nullable()->default(true);
            $table->timestamps();

            $table->foreign('nis')->references('nis')->on('peserta_didik')->onDelete('cascade');
            $table->foreign('id_grup_wali_asuh')->references('id')->on('grup_wali_asuh')->onDelete('cascade');
            // $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            // $table->foreign('updated_by')->references('id')->on('users')->onDelete('cascade');
        });
    }
    
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('anak_asuh');
        Schema::dropIfExists('wali_asuh');
        Schema::dropIfExists('grup_wali_asuh');
    }
};
      