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
            $table->unsignedBigInteger('id_wilayah');
            $table->string('nama_grup');
            $table->fullText('nama_grup');
            $table->enum('jenis_kelamin', ['l', 'p']);
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->softDeletes();
            $table->boolean('status')->nullable()->default(true);
            $table->timestamps();

            $table->foreign('id_wilayah')->references('id')->on('wilayah')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('deleted_by')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('wali_asuh', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_santri');
            $table->unsignedBigInteger('id_grup_wali_asuh')->nullable();
            $table->date('tanggal_mulai');
            $table->date('tanggal_berakhir')->nullable()->check('tanggal_berakhir IS NULL OR tanggal_berakhir >= tanggal_mulai');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->softDeletes();
            $table->boolean('status')->nullable()->default(true);
            $table->timestamps();

            $table->foreign('id_santri')->references('id')->on('santri')->onDelete('cascade');
            $table->foreign('id_grup_wali_asuh')->references('id')->on('grup_wali_asuh')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('anak_asuh', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_santri');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->softDeletes();
            $table->boolean('status')->nullable()->default(true);
            $table->timestamps();

            $table->foreign('id_santri')->references('id')->on('santri')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('deleted_by')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('kewaliasuhan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_wali_asuh')->nullable();
            $table->unsignedBigInteger('id_anak_asuh')->nullable();
            $table->date('tanggal_mulai');
            $table->date('tanggal_berakhir')->nullable()->check('tanggal_berakhir IS NULL OR tanggal_berakhir >= tanggal_mulai');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->softDeletes();
            $table->boolean('status')->nullable()->default(true);
            $table->timestamps();

            $table->foreign('id_wali_asuh')->references('id')->on('wali_asuh')->onDelete('cascade');
            $table->foreign('id_anak_asuh')->references('id')->on('anak_asuh')->onDelete('cascade');
            // Constraint untuk memastikan satu anak asuh hanya punya satu wali asuh aktif
            $table->unique(['id_anak_asuh', 'status'], 'unique_anak_aktif');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('deleted_by')->references('id')->on('users')->onDelete('cascade');
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
        Schema::dropIfExists('kewaliasuhan');
    }
};
