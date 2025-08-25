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
        Schema::create('user_ortu', function (Blueprint $table) {
            $table->id();
            $table->string('no_kk')->unique();
            $table->string('no_hp')->unique()->nullable();
            $table->string('email')->unique()->nullable();
            $table->string('password');
            $table->boolean('status')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_ortu');
    }
};
