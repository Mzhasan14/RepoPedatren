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
        Schema::create('orang_tua', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_biodata');
            $table->string('pekerjaan');
            $table->integer('penghasialan')->nullable();
            $table->boolean('wafat');
            $table->softDeletes();
            $table->boolean('status')->nullable()->default(true);
            $table->timestamps();

            $table->foreign('id_biodata')->references('id')->on('biodata')->onDelete('cascade');

            // $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            // $table->foreign('updated_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orang_tua');
    }
};
