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
        Schema::create('biometric_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('biometric_profile_id')->nullable();
            $table->uuid('device_id')->nullable();
            $table->enum('method', ['fingerprint', 'card']);
            $table->timestamp('scanned_at');
            $table->boolean('success')->default(true);
            $table->string('message')->nullable();
            $table->timestamps();

            $table->foreign('biometric_profile_id')->references('id')->on('biometric_profiles')->onDelete('set null');
            $table->foreign('device_id')->references('id')->on('biometric_devices')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('biometric_logs');
    }
};
