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
        Schema::create('biometric_devices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('device_name');
            $table->string('location')->nullable();
            $table->string('ip_address')->nullable();
            $table->enum('type', ['fingerprint', 'card', 'both']);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        Schema::create('biometric_profiles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('santri_id');
            $table->string('card_uid')->nullable();
            $table->timestamps();

            $table->foreign('santri_id')->references('id')->on('santri')->onDelete('cascade');
        });

        Schema::create('biometric_finger_positions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('biometric_profile_id');
            $table->string('finger_position'); // e.g., "right_thumb", "left_index"
            $table->timestamps();

            $table->foreign('biometric_profile_id')->references('id')->on('biometric_profiles')->onDelete('cascade');
        });

        Schema::create('biometric_fingerprint_templates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('finger_position_id'); // mengacu ke jari mana
            $table->string('template'); // hasil scan fingerprint
            $table->integer('scan_order')->nullable(); // urutan scan ke-1, ke-2, dst.
            $table->timestamps();

            $table->foreign('finger_position_id')->references('id')->on('biometric_finger_positions')->onDelete('cascade');
        });

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
        Schema::dropIfExists('biometric');
    }
};
