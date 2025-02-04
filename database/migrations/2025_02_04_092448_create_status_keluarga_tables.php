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
        Schema::create('status_keluarga', function (Blueprint $table) {
            $table->id();
            $table->string('nama_status',45);
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->boolean('status')->default(true); 
            $table->timestamps();

            // $table->foreign('created_by')->references('id')->on('user')->onDelete('cascade');
            // $table->foreign('updated_by')->references('id')->on('user')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('status_keluarga',function (Blueprint $table){
            // $table->dropForeign(['created_by']);
            // $table->dropForeign(['updated_by']);
            $table->dropSoftDeletes();       
        });
        Schema::dropIfExists('status_keluarga');
    }
};
